<?php

namespace App\Http\Controllers;

use App\Models\Part;
use App\Models\StockMovement;
use App\Support\InventoryMetrics;
use App\Support\InventoryUnits;
use App\Support\SystemNotifier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
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
            'partCategories' => $this->categoryChoices(),
            'unitLabelChoices' => $this->unitLabelChoices(),
        ]));
    }

    public function storePart(Request $request): RedirectResponse
    {
        $shop = $request->user()->workspaceShop();
        abort_if($shop === null, 403, 'Shop profile not found.');

        $request->merge([
            'stock_mode' => 'box_piece',
            'unit_label' => $this->normalizeUnitLabel((string) $request->input('unit_label', 'box')),
            'pieces_per_box' => $request->input('container_quantity', $request->input('pieces_per_box')),
            'unit_price_basis' => 'per_box',
        ]);

        $validated = $request->validate([
            'sku' => ['required', 'string', 'max:50', Rule::unique('parts', 'sku')->where(fn ($query) => $query->where('shop_id', $shop->id))],
            'name' => ['required', 'string', 'max:140'],
            'category' => ['required', 'string', 'max:100', Rule::in($this->categoryChoices())],
            'stock_mode' => ['required', Rule::in($this->stockModeChoices())],
            'unit_label' => ['required', 'string', 'max:20'],
            'pieces_per_box' => ['nullable', 'required_if:stock_mode,box_piece', 'integer', 'min:1', 'max:1000000'],
            'image' => ['nullable', 'image', 'max:2048'],
            'minimum_stock' => ['required', 'numeric', 'min:0', 'max:100000'],
            'unit_price_per_box' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'unit_price_per_piece' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'unit_price' => ['nullable', 'numeric', 'min:0', 'max:99999999.99'],
            'unit_price_basis' => ['required', Rule::in(['per_box', 'per_piece'])],
            'initial_stock' => ['required', 'integer', 'min:0', 'max:1000000'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->storePublicly('parts', 'public');
        }

        $part = Part::query()->create($this->filterPartColumns([
            'shop_id' => $shop->id,
            'sku' => strtoupper(trim($validated['sku'])),
            'name' => trim($validated['name']),
            'category' => trim($validated['category']),
            'stock_mode' => $validated['stock_mode'],
            'unit_label' => $validated['unit_label'],
            'pieces_per_box' => (float) ($validated['pieces_per_box'] ?? 1),
            'allow_fractional_quantity' => false,
            'image_path' => $imagePath,
            'minimum_stock' => $validated['minimum_stock'],
            'unit_price' => $validated['unit_price_basis'] === 'per_piece'
                ? $validated['unit_price_per_piece']
                : $validated['unit_price_per_box'],
            'unit_price_per_box' => $validated['unit_price_per_box'],
            'unit_price_per_piece' => $validated['unit_price_per_piece'],
            'unit_price_basis' => $validated['unit_price_basis'],
            'notes' => isset($validated['notes']) && $validated['notes'] !== null ? trim($validated['notes']) : null,
            'is_active' => (bool) ($validated['is_active'] ?? true),
        ]));

        $initialBoxes = (int) $validated['initial_stock'];
        $initialPieces = InventoryUnits::toBaseQuantity($part, (float) $initialBoxes, 'box');

        StockMovement::query()->create([
            'part_id' => $part->id,
            'user_id' => $request->user()->id,
            'type' => StockMovement::TYPE_OPENING,
            'quantity' => $initialPieces,
            'reason' => sprintf('Opening Stock: %s box%s', number_format($initialBoxes), $initialBoxes === 1 ? '' : 'es'),
            'reference' => 'opening-stock',
            'moved_at' => now(),
        ]);

        SystemNotifier::notifyStockLevel($part->fresh(), $initialPieces);

        return redirect()->route('inventory')
            ->with('status', 'Part added successfully.')
            ->with('status_tone', 'success');
    }

    public function updatePart(Request $request, Part $part): RedirectResponse
    {
        $shop = $request->user()->workspaceShop();
        abort_if($shop === null, 403, 'Shop profile not found.');
        $part = $this->shopPart($part, $shop->id);

        $request->merge([
            'stock_mode' => 'box_piece',
            'unit_label' => $this->normalizeUnitLabel((string) $request->input('unit_label', 'box')),
            'pieces_per_box' => $request->input('container_quantity', $request->input('pieces_per_box')),
            'unit_price_basis' => 'per_box',
        ]);

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
            'category' => ['required', 'string', 'max:100', Rule::in($this->categoryChoices())],
            'stock_mode' => ['required', Rule::in($this->stockModeChoices())],
            'unit_label' => ['required', 'string', 'max:20'],
            'pieces_per_box' => ['nullable', 'required_if:stock_mode,box_piece', 'integer', 'min:1', 'max:1000000'],
            'image' => ['nullable', 'image', 'max:2048'],
            'minimum_stock' => ['required', 'numeric', 'min:0', 'max:100000'],
            'unit_price_per_box' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'unit_price_per_piece' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'unit_price' => ['nullable', 'numeric', 'min:0', 'max:99999999.99'],
            'unit_price_basis' => ['required', Rule::in(['per_box', 'per_piece'])],
            'notes' => ['nullable', 'string', 'max:2000'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $imagePath = $part->image_path;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->storePublicly('parts', 'public');

            if ($part->image_path) {
                Storage::disk('public')->delete($part->image_path);
            }
        }

        $part->update($this->filterPartColumns([
            'sku' => strtoupper(trim($validated['sku'])),
            'name' => trim($validated['name']),
            'category' => trim($validated['category']),
            'stock_mode' => $validated['stock_mode'],
            'unit_label' => $validated['unit_label'],
            'pieces_per_box' => (float) ($validated['pieces_per_box'] ?? 1),
            'allow_fractional_quantity' => false,
            'image_path' => $imagePath,
            'minimum_stock' => $validated['minimum_stock'],
            'unit_price' => $validated['unit_price_basis'] === 'per_piece'
                ? $validated['unit_price_per_piece']
                : $validated['unit_price_per_box'],
            'unit_price_per_box' => $validated['unit_price_per_box'],
            'unit_price_per_piece' => $validated['unit_price_per_piece'],
            'unit_price_basis' => $validated['unit_price_basis'],
            'notes' => isset($validated['notes']) && $validated['notes'] !== null ? trim($validated['notes']) : null,
            'is_active' => (bool) ($validated['is_active'] ?? true),
        ]));

        $freshPart = InventoryMetrics::partsWithStockQuery($shop->id)
            ->where('parts.id', $part->id)
            ->first();

        if ($freshPart) {
            SystemNotifier::notifyStockLevel($part->fresh(), (float) $freshPart->current_stock);
        }

        return redirect()->route('inventory')
            ->with('status', 'Part updated successfully.')
            ->with('status_tone', 'success');
    }

    public function destroyPart(Request $request, Part $part): RedirectResponse
    {
        $shop = $request->user()->workspaceShop();
        abort_if($shop === null, 403, 'Shop profile not found.');
        $part = $this->shopPart($part, $shop->id);

        $part->delete();

        return redirect()->route('inventory')
            ->with('status', 'Part deleted successfully.')
            ->with('status_tone', 'danger');
    }

    public function storeMovement(Request $request, Part $part): RedirectResponse
    {
        $shop = $request->user()->workspaceShop();
        abort_if($shop === null, 403, 'Shop profile not found.');
        $part = $this->shopPart($part, $shop->id);
        $currentStock = $this->currentStockForPart($shop->id, $part->id);

        $validator = Validator::make($request->all(), [
            'type' => ['required', Rule::in([StockMovement::TYPE_IN, StockMovement::TYPE_OUT])],
            'quantity' => ['required', 'integer', 'min:1', 'max:100000'],
            'quantity_unit' => ['nullable', Rule::in(['piece', 'box'])],
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        $validator->after(function ($validator) use ($currentStock, $part): void {
            $type = (string) request('type');
            $inputQuantity = (float) request('quantity');
            $quantityUnit = (string) request('quantity_unit', '');

            if (! $this->movementUnitAllowed($part, $type, $quantityUnit)) {
                $validator->errors()->add('quantity_unit', 'The selected quantity unit is not allowed for this stock movement.');
                return;
            }

            $rawQty = InventoryUnits::toBaseQuantity($part, $inputQuantity, $quantityUnit);

            try {
                InventoryUnits::assertValidSellQuantity($part, $rawQty);
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

        return redirect()->route('inventory')
            ->with('status', 'Stock movement recorded.')
            ->with('status_tone', $validated['type'] === StockMovement::TYPE_OUT ? 'danger' : 'success');
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

    private function unitLabelChoices(): array
    {
        return ['box', 'case', 'pack', 'set'];
    }

    private function categoryChoices(): array
    {
        return [
            'Body & Fairings',
            'Lighting & Accessories',
            'Engine Parts',
            'Electrical Parts',
            'Brake System',
        ];
    }

    private function stockModeChoices(): array
    {
        return ['box_piece'];
    }

    private function movementUnitAllowed(Part $part, string $type, string $unit): bool
    {
        $unit = strtolower(trim($unit));

        return $type === StockMovement::TYPE_IN
            ? $unit === 'box'
            : in_array($unit, ['box', 'piece'], true);
    }

    private function normalizeUnitLabel(string $unitLabel): string
    {
        $normalized = strtolower(trim($unitLabel));

        return $normalized !== '' ? $normalized : 'box';
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

    private function filterPartColumns(array $attributes): array
    {
        $columns = array_flip(Schema::getColumnListing('parts'));

        return array_intersect_key($attributes, $columns);
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
            ['label' => 'Logs', 'route' => 'logs', 'icon' => 'file'],
            ['label' => 'Settings', 'route' => 'settings', 'icon' => 'settings'],
        ];
    }

    private function supportItems(): array
    {
        return [
            ['label' => 'Support', 'icon' => 'support', 'href' => route('support')],
        ];
    }
}
