<?php

namespace App\Http\Controllers;

use App\Models\Part;
use App\Models\StockMovement;
use App\Support\InventoryMetrics;
use App\Support\WorkshopDemo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class InventoryController extends Controller
{
    public function index(Request $request): View
    {
        $shop = $request->user()->shop;
        abort_if($shop === null, 403, 'Shop profile not found.');

        $parts = InventoryMetrics::partsWithStockQuery($shop)
            ->orderBy('name')
            ->get();
        $summary = InventoryMetrics::summarizeParts($parts);
        $lowStockParts = $parts
            ->where('is_active', true)
            ->filter(fn (Part $part): bool => $part->current_stock < $part->minimum_stock)
            ->values();

        $categories = $parts
            ->groupBy('category')
            ->map(fn ($items, string $name): array => [
                'name' => $name,
                'count' => $items->count(),
                'low' => $items->filter(fn (Part $part): bool => $part->current_stock < $part->minimum_stock)->count(),
            ])
            ->sortBy('name')
            ->values();

        $recentMovements = StockMovement::query()
            ->join('parts', 'parts.id', '=', 'stock_movements.part_id')
            ->where('parts.shop_id', $shop->id)
            ->with('part')
            ->select('stock_movements.*')
            ->latest('stock_movements.moved_at')
            ->take(12)
            ->get();

        return view('pages.inventory', $this->baseData('inventory', [
            'heading' => 'Inventory',
            'meta' => sprintf(
                'Updated %s | %d categories | %d total SKUs',
                now()->format('M d, Y h:i A'),
                $categories->count(),
                $parts->count()
            ),
            'stats' => [
                ['label' => 'Total SKUs', 'value' => number_format($summary['totalSkus']), 'caption' => 'Tracked parts', 'icon' => 'inventory'],
                ['label' => 'Low Stock', 'value' => number_format($summary['lowStock']), 'caption' => 'Below minimum', 'icon' => 'alert', 'tone' => 'warning'],
                ['label' => 'Out of Stock', 'value' => number_format($summary['outOfStock']), 'caption' => 'Needs reorder', 'icon' => 'alert', 'tone' => 'danger'],
                ['label' => 'Inventory Value', 'value' => InventoryMetrics::formatCurrency($summary['inventoryValue']), 'caption' => 'On-hand value', 'icon' => 'billing'],
            ],
            'categories' => $categories,
            'alerts' => $lowStockParts->take(8),
            'parts' => $parts,
            'movements' => $recentMovements,
            'partCategories' => [
                    'Engine Parts', 'Electrical Parts', 'Transmission & Drive Parts', 
                    'Brake & Suspension Parts', 'Body & Frame Parts'
                ],
        ]));
    }

    public function storePart(Request $request): RedirectResponse
    {
        $shop = $request->user()->shop;
        abort_if($shop === null, 403, 'Shop profile not found.');

        $validated = $request->validate([
            'sku' => ['required', 'string', 'max:50', Rule::unique('parts', 'sku')->where(fn ($query) => $query->where('shop_id', $shop->id))],
            'name' => ['required', 'string', 'max:140'],
            'category' => ['required', 'string', 'max:100'],
            'image' => ['nullable', 'image', 'max:2048'],
            'minimum_stock' => ['required', 'integer', 'min:0', 'max:100000'],
            'unit_price' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->storePublicly('parts', 'public');
        }

        Part::query()->create([
            'shop_id' => $shop->id,
            'sku' => strtoupper(trim($validated['sku'])),
            'name' => trim($validated['name']),
            'category' => trim($validated['category']),
            'image_path' => $imagePath,
            'minimum_stock' => $validated['minimum_stock'],
            'unit_price' => $validated['unit_price'],
            'is_active' => (bool) ($validated['is_active'] ?? true),
        ]);

        return redirect()->route('inventory')->with('status', 'Part added successfully.');
    }

    public function updatePart(Request $request, Part $part): RedirectResponse
    {
        $shop = $request->user()->shop;
        abort_if($shop === null, 403, 'Shop profile not found.');
        $part = $this->shopPart($part, $shop->id);

        $validated = $request->validate([
            'sku' => [
                'required',
                'string',
                'max:50',
                Rule::unique('parts', 'sku')
                    ->where(fn ($query) => $query->where('shop_id', $shop->id))
                    ->ignore($part->id),
            ],
            'name' => ['required', 'string', 'max:140'],
            'category' => ['required', 'string', 'max:100'],
            'image' => ['nullable', 'image', 'max:2048'],
            'minimum_stock' => ['required', 'integer', 'min:0', 'max:100000'],
            'unit_price' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $imagePath = $part->image_path;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->storePublicly('parts', 'public');
        }

        $part->update([
            'sku' => strtoupper(trim($validated['sku'])),
            'name' => trim($validated['name']),
            'category' => trim($validated['category']),
            'image_path' => $imagePath,
            'minimum_stock' => $validated['minimum_stock'],
            'unit_price' => $validated['unit_price'],
            'is_active' => (bool) ($validated['is_active'] ?? false),
        ]);

        return redirect()->route('inventory')->with('status', 'Part updated successfully.');
    }

    public function destroyPart(Request $request, Part $part): RedirectResponse
    {
        $shop = $request->user()->shop;
        abort_if($shop === null, 403, 'Shop profile not found.');
        $part = $this->shopPart($part, $shop->id);

        if ($part->movements()->exists()) {
            return redirect()
                ->route('inventory')
                ->withErrors([
                    'inventory' => 'Part cannot be deleted because it has stock movement history.',
                ]);
        }

        $part->delete();

        return redirect()->route('inventory')->with('status', 'Part deleted.');
    }

    public function storeMovement(Request $request, Part $part): RedirectResponse
    {
        $shop = $request->user()->shop;
        abort_if($shop === null, 403, 'Shop profile not found.');
        $part = $this->shopPart($part, $shop->id);
        $currentStock = $this->currentStockForPart($shop->id, $part->id);

        $validator = Validator::make($request->all(), [
            'type' => ['required', Rule::in([StockMovement::TYPE_IN, StockMovement::TYPE_OUT, StockMovement::TYPE_ADJUST])],
            'quantity' => ['required', 'integer', 'min:-100000', 'max:100000'],
            'reason' => ['nullable', 'string', 'max:255'],
            'reference' => ['nullable', 'string', 'max:255'],
        ]);

        $validator->after(function ($validator) use ($currentStock): void {
            $type = (string) request('type');
            $rawQty = (int) request('quantity');

            if (in_array($type, [StockMovement::TYPE_IN, StockMovement::TYPE_OUT], true) && $rawQty <= 0) {
                $validator->errors()->add('quantity', 'Quantity must be greater than zero for stock in/out.');
                return;
            }

            if ($type === StockMovement::TYPE_ADJUST && $rawQty === 0) {
                $validator->errors()->add('quantity', 'Adjustment quantity cannot be zero.');
                return;
            }

            $delta = $this->movementDelta($type, $rawQty);
            $nextStock = $currentStock + $delta;

            if ($nextStock < 0) {
                $validator->errors()->add('quantity', 'Movement would make stock negative. Reduce the quantity.');
            }
        });

        $validated = $validator->validate();

        StockMovement::query()->create([
            'part_id' => $part->id,
            'user_id' => $request->user()->id,
            'type' => $validated['type'],
            'quantity' => $this->normalizeMovementQuantity($validated['type'], (int) $validated['quantity']),
            'reason' => $validated['reason'] ?? null,
            'reference' => $validated['reference'] ?? null,
            'moved_at' => now(),
        ]);

        return redirect()->route('inventory')->with('status', 'Stock movement recorded.');
    }

    private function movementDelta(string $type, int $quantity): int
    {
        return match ($type) {
            StockMovement::TYPE_IN => abs($quantity),
            StockMovement::TYPE_OUT => abs($quantity) * -1,
            default => $quantity,
        };
    }

    private function normalizeMovementQuantity(string $type, int $quantity): int
    {
        return match ($type) {
            StockMovement::TYPE_IN, StockMovement::TYPE_OUT => abs($quantity),
            default => $quantity,
        };
    }

    private function shopPart(Part $part, int $shopId): Part
    {
        abort_if($part->shop_id !== $shopId, 404);

        return $part;
    }

    private function currentStockForPart(int $shopId, int $partId): int
    {
        $stockRow = InventoryMetrics::partsWithStockQuery($shopId)
            ->where('parts.id', $partId)
            ->first();

        return (int) ($stockRow?->current_stock ?? 0);
    }

    /**
     * @param array<string, mixed> $pageData
     * @return array<string, mixed>
     */
    private function baseData(string $page, array $pageData): array
    {
        $user = auth()->user();
        $shop = $user?->shop;

        return array_merge([
            'pageTitle' => $pageData['heading'] ?? 'MotoX',
            'navigation' => WorkshopDemo::navigation(),
            'supportLinks' => WorkshopDemo::supportLinks(),
            'currentPage' => $page,
            'currentUser' => [
                'name' => $user?->name ?? 'MotoX',
                'role' => $shop?->name ?? 'Workshop',
                'initials' => collect(explode(' ', $user?->name ?? 'MX'))
                    ->filter()
                    ->map(fn (string $part): string => mb_substr($part, 0, 1))
                    ->take(2)
                    ->implode(''),
                'online' => true,
            ],
            'showTopbar' => true,
        ], $pageData);
    }
}
