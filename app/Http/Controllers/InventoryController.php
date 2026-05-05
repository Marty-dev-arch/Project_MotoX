<?php

namespace App\Http\Controllers;

use App\Models\Part;
use App\Models\StockMovement;
use App\Support\InventoryMetrics;
use App\Support\InventoryUnits;
use App\Support\SystemNotifier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class InventoryController extends Controller
{
    public function index(Request $request): View
    {
        $shop = $request->user()->workspaceShop();
        abort_if($shop === null, 403, 'Shop profile not found.');

        $parts = InventoryMetrics::partsWithStockQuery($shop)
            ->orderBy('name')
            ->get();
        $summary = InventoryMetrics::summarizeParts($parts);
        $lowStockParts = $parts
            ->where('is_active', true)
            ->filter(fn (Part $part): bool => $part->current_stock <= 0 || ($part->current_stock > 0 && $part->current_stock < $part->minimum_stock))
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
                now('Asia/Manila')->format('M d, Y h:i A').' PHT',
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
        $shop = $request->user()->workspaceShop();
        abort_if($shop === null, 403, 'Shop profile not found.');

        $validated = $request->validate([
            'sku' => ['required', 'string', 'max:50', Rule::unique('parts', 'sku')->where(fn ($query) => $query->where('shop_id', $shop->id))],
            'name' => ['required', 'string', 'max:140'],
            'category' => ['required', 'string', 'max:100'],
            'stock_mode' => ['required', Rule::in(['piece', 'box_piece', 'liquid'])],
            'unit_label' => ['required', 'string', 'max:20'],
            'pieces_per_box' => ['nullable', 'required_if:stock_mode,box_piece', 'numeric', 'min:0.001', 'max:1000000'],
            'allow_fractional_quantity' => ['nullable', 'boolean'],
            'image' => ['nullable', 'image', 'max:2048'],
            'minimum_stock' => ['required', 'numeric', 'min:0', 'max:100000'],
            'unit_price' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->storePublicly('parts', 'public');
        }

        $part = Part::query()->create([
            'shop_id' => $shop->id,
            'sku' => strtoupper(trim($validated['sku'])),
            'name' => trim($validated['name']),
            'category' => trim($validated['category']),
            'stock_mode' => $validated['stock_mode'],
            'unit_label' => trim((string) $validated['unit_label']),
            'pieces_per_box' => $validated['stock_mode'] === 'box_piece' ? (float) ($validated['pieces_per_box'] ?? 0) : null,
            'allow_fractional_quantity' => $validated['stock_mode'] === 'liquid'
                || (bool) ($validated['allow_fractional_quantity'] ?? false),
            'image_path' => $imagePath,
            'minimum_stock' => $validated['minimum_stock'],
            'unit_price' => $validated['unit_price'],
            'is_active' => (bool) ($validated['is_active'] ?? true),
        ]);

        SystemNotifier::notifyShop(
            $shop,
            'part.created',
            'Part Added',
            sprintf('%s was added to inventory.', $part->name),
            'success',
            ['part_id' => $part->id],
        );
        SystemNotifier::notifyStockLevel($part->fresh(), 0.0);

        return redirect()->route('inventory')->with('status', 'Part added successfully.');
    }

    public function updatePart(Request $request, Part $part): RedirectResponse
    {
        $shop = $request->user()->workspaceShop();
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
            'stock_mode' => ['required', Rule::in(['piece', 'box_piece', 'liquid'])],
            'unit_label' => ['required', 'string', 'max:20'],
            'pieces_per_box' => ['nullable', 'required_if:stock_mode,box_piece', 'numeric', 'min:0.001', 'max:1000000'],
            'allow_fractional_quantity' => ['nullable', 'boolean'],
            'image' => ['nullable', 'image', 'max:2048'],
            'minimum_stock' => ['required', 'numeric', 'min:0', 'max:100000'],
            'unit_price' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $imagePath = $part->image_path;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->storePublicly('parts', 'public');

            if ($part->image_path) {
                Storage::disk('public')->delete($part->image_path);
            }
        }

        $part->update([
            'sku' => strtoupper(trim($validated['sku'])),
            'name' => trim($validated['name']),
            'category' => trim($validated['category']),
            'stock_mode' => $validated['stock_mode'],
            'unit_label' => trim((string) $validated['unit_label']),
            'pieces_per_box' => $validated['stock_mode'] === 'box_piece' ? (float) ($validated['pieces_per_box'] ?? 0) : null,
            'allow_fractional_quantity' => $validated['stock_mode'] === 'liquid'
                || (bool) ($validated['allow_fractional_quantity'] ?? false),
            'image_path' => $imagePath,
            'minimum_stock' => $validated['minimum_stock'],
            'unit_price' => $validated['unit_price'],
            'is_active' => (bool) ($validated['is_active'] ?? true),
        ]);

        SystemNotifier::notifyShop(
            $shop,
            'part.updated',
            'Part Updated',
            sprintf('%s details were updated.', $part->name),
            'success',
            ['part_id' => $part->id],
        );

        $freshPart = InventoryMetrics::partsWithStockQuery($shop->id)
            ->where('parts.id', $part->id)
            ->first();

        if ($freshPart) {
            SystemNotifier::notifyStockLevel($part->fresh(), (float) $freshPart->current_stock);
        }

        return redirect()->route('inventory')->with('status', 'Part updated successfully.');
    }

    public function destroyPart(Request $request, Part $part): RedirectResponse
    {
        $shop = $request->user()->workspaceShop();
        abort_if($shop === null, 403, 'Shop profile not found.');
        $part = $this->shopPart($part, $shop->id);

        if ($part->movements()->exists()) {
            return redirect()
                ->route('inventory')
                ->withErrors([
                    'inventory' => 'Part cannot be deleted because it has stock movement history.',
                ]);
        }

        if ($part->image_path) {
            Storage::disk('public')->delete($part->image_path);
        }

        $part->delete();

        return redirect()->route('inventory')->with('status', 'Part deleted.');
    }

    public function storeMovement(Request $request, Part $part): RedirectResponse
    {
        $shop = $request->user()->workspaceShop();
        abort_if($shop === null, 403, 'Shop profile not found.');
        $part = $this->shopPart($part, $shop->id);
        $currentStock = $this->currentStockForPart($shop->id, $part->id);

        $validator = Validator::make($request->all(), [
            'type' => ['required', Rule::in([StockMovement::TYPE_IN, StockMovement::TYPE_OUT, StockMovement::TYPE_ADJUST])],
            'quantity' => ['required', 'numeric', 'min:-100000', 'max:100000'],
            'quantity_unit' => ['nullable', Rule::in(['piece', 'box', 'liter', 'milliliter'])],
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        $validator->after(function ($validator) use ($currentStock, $part): void {
            $type = (string) request('type');
            $inputQuantity = (float) request('quantity');
            $quantityUnit = (string) request('quantity_unit', '');
            $rawQty = InventoryUnits::toBaseQuantity($part, $inputQuantity, $quantityUnit);

            try {
                InventoryUnits::assertValidSellQuantity(
                    $part,
                    $type === StockMovement::TYPE_ADJUST ? abs($rawQty) : $rawQty
                );
            } catch (\Illuminate\Validation\ValidationException $exception) {
                foreach ($exception->errors() as $fieldErrors) {
                    foreach ($fieldErrors as $error) {
                        $validator->errors()->add('quantity', $error);
                    }
                }
                return;
            }

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
        $baseQuantity = InventoryUnits::toBaseQuantity(
            $part,
            (float) $validated['quantity'],
            $validated['quantity_unit'] ?? null
        );

        StockMovement::query()->create([
            'part_id' => $part->id,
            'user_id' => $request->user()->id,
            'type' => $validated['type'],
            'quantity' => $this->normalizeMovementQuantity($validated['type'], $baseQuantity),
            'reason' => $validated['reason'] ?? null,
            'reference' => null,
            'moved_at' => now(),
        ]);

        $freshPart = InventoryMetrics::partsWithStockQuery($shop->id)
            ->where('parts.id', $part->id)
            ->first();

        if ($freshPart) {
            SystemNotifier::notifyStockLevel($part->fresh(), (float) $freshPart->current_stock);
        }

        $movementValue = InventoryUnits::priceForBaseQuantity($part, $baseQuantity);

        SystemNotifier::notifyShop(
            $shop,
            'stock.movement',
            'Stock Movement Recorded',
            sprintf('%s stock %s: %.3f %s worth PHP %s.', $part->name, $validated['type'], $baseQuantity, $part->defaultUnitLabel(), number_format($movementValue, 2)),
            $validated['type'] === StockMovement::TYPE_OUT ? 'warning' : 'success',
            ['part_id' => $part->id, 'quantity' => $baseQuantity, 'type' => $validated['type'], 'movement_value' => $movementValue],
        );

        return redirect()->route('inventory')->with('status', 'Stock movement recorded.');
    }

    private function movementDelta(string $type, float $quantity): float
    {
        return match ($type) {
            StockMovement::TYPE_IN => abs($quantity),
            StockMovement::TYPE_OUT => abs($quantity) * -1,
            default => $quantity,
        };
    }

    private function normalizeMovementQuantity(string $type, float $quantity): float
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

    private function currentStockForPart(int $shopId, int $partId): float
    {
        $stockRow = InventoryMetrics::partsWithStockQuery($shopId)
            ->where('parts.id', $partId)
            ->first();

        return (float) ($stockRow?->current_stock ?? 0);
    }

    
    private function baseData(string $page, array $pageData): array
    {
        $user = auth()->user();
        $shop = $user?->workspaceShop();

        return array_merge([
            'pageTitle' => $pageData['heading'] ?? 'MotoX',
            'navigation' => $this->navigationItems(),
            'supportLinks' => $this->supportItems(),
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
            'showHeaderSearch' => false,
        ], $pageData);
    }

    private function navigationItems(): array
    {
        return [
            ['label' => 'Dashboard', 'route' => 'dashboard', 'icon' => 'dashboard'],
            ['label' => 'Customers', 'route' => 'customers', 'icon' => 'customers'],
            ['label' => 'Job Orders', 'route' => 'job-orders', 'icon' => 'job-orders'],
            ['label' => 'Inventory', 'route' => 'inventory', 'icon' => 'inventory'],
            ['label' => 'Billing', 'route' => 'billing', 'icon' => 'billing'],
            ['label' => 'Reports', 'route' => 'reports', 'icon' => 'reports'],
            ['label' => 'Settings', 'route' => 'settings', 'icon' => 'settings'],
        ];
    }

    private function supportItems(): array
    {
        return [
            ['label' => 'Support', 'icon' => 'support', 'href' => '#'],
        ];
    }
}
