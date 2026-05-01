<?php

namespace App\Http\Controllers;

use App\Models\StockMovement;
use App\Support\InventoryMetrics;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $shop = $request->user()->shop;
        abort_if($shop === null, 403, 'Shop profile not found.');

        $parts = InventoryMetrics::partsWithStockQuery($shop)
            ->orderBy('name')
            ->get();

        $summary = InventoryMetrics::summarizeParts($parts);
        $trend = InventoryMetrics::movementTrend($shop);
        $lowStockByCategory = InventoryMetrics::lowStockByCategory($parts);

        $recentMovements = StockMovement::query()
            ->join('parts', 'parts.id', '=', 'stock_movements.part_id')
            ->where('parts.shop_id', $shop->id)
            ->with('part')
            ->select('stock_movements.*')
            ->latest('stock_movements.moved_at')
            ->take(6)
            ->get();

return view('pages.dashboard', $this->baseData([
            'heading' => 'Dashboard',
            'subheading' => 'Real-time inventory visibility and spare parts activity.',
            'stats' => [
                [
                    'label' => 'Total SKUs',
                    'value' => number_format($summary['totalSkus']),
                    'caption' => 'Tracked spare parts',
                    'icon' => 'inventory',
                ],
                [
                    'label' => 'Low Stock',
                    'value' => number_format($summary['lowStock']),
                    'caption' => 'Below minimum stock',
                    'icon' => 'alert',
                    'tone' => 'warning',
                ],
                [
                    'label' => 'Out of Stock',
                    'value' => number_format($summary['outOfStock']),
                    'caption' => 'Immediate reorder required',
                    'icon' => 'alert',
                    'tone' => 'danger',
                ],
                [
                    'label' => 'Inventory Value',
                    'value' => InventoryMetrics::formatCurrency($summary['inventoryValue']),
                    'caption' => 'Current on-hand value',
                    'icon' => 'billing',
                ],
            ],
            'trend' => $trend,
            'lowStockByCategory' => $lowStockByCategory,
            'lowStockParts' => $parts
                ->where('is_active', true)
                ->filter(fn ($part) => $part->current_stock < $part->minimum_stock)
                ->take(6)
                ->values(),
            'recentMovements' => $recentMovements,
            'dashboardMetricsUrl' => route('dashboard.metrics.inventory'),
        ]));
    }

    public function inventoryMetrics(Request $request): JsonResponse
    {
        $shop = $request->user()->shop;
        abort_if($shop === null, 403, 'Shop profile not found.');

        $allowedRanges = [2, 7, 30, 90, 180, 365];
        $requestedDays = (int) $request->integer('days', 7);
        $days = in_array($requestedDays, $allowedRanges, true) ? $requestedDays : 7;

        $parts = InventoryMetrics::partsWithStockQuery($shop)->get();
        $summary = InventoryMetrics::summarizeParts($parts);

        return response()->json([
            'kpis' => [
                'total_skus' => number_format($summary['totalSkus']),
                'low_stock' => number_format($summary['lowStock']),
                'out_of_stock' => number_format($summary['outOfStock']),
                'inventory_value' => InventoryMetrics::formatCurrency($summary['inventoryValue']),
            ],
            'trend' => InventoryMetrics::movementTrend($shop, $days),
            'low_stock_by_category' => InventoryMetrics::lowStockByCategory($parts),
            'trend_range_days' => $days,
            'updated_at' => now('Asia/Manila')->toIso8601String(),
        ]);
    }

    /**
     * @param array<string, mixed> $pageData
     * @return array<string, mixed>
     */
    private function baseData(array $pageData): array
    {
        $user = auth()->user();
        $shop = $user?->shop;

return array_merge([
            'pageTitle' => $pageData['heading'] ?? 'MotoX',
            'navigation' => $this->navigationItems(),
            'supportLinks' => $this->supportItems(),
            'currentPage' => 'dashboard',
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

    /**
     * @return array<int, array{label:string,route:string,icon:string}>
     */
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

    /**
     * @return array<int, array{label:string,icon:string,href:string}>
     */
    private function supportItems(): array
    {
        return [
            ['label' => 'Support', 'icon' => 'support', 'href' => '#'],
        ];
    }
}
