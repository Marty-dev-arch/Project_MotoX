<?php

namespace App\Http\Controllers;

use App\Models\StockMovement;
use App\Models\JobOrder;
use App\Support\DatePeriods;
use App\Support\InventoryMetrics;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $shop = $request->user()->workspaceShop();
        abort_if($shop === null, 403, 'Shop profile not found.');

        $parts = InventoryMetrics::partsWithStockQuery($shop)
            ->orderBy('name')
            ->get();

        $summary = InventoryMetrics::summarizeParts($parts);
        $selectedTrendRange = $this->normalizeTrendRange((string) $request->query('range', $request->query('months', $this->defaultTrendRange())));
        $trend = InventoryMetrics::movementTrendForDashboardRange($shop, $selectedTrendRange);
        $lowStockByCategory = InventoryMetrics::lowStockByCategory($parts);
        $revenueStats = $this->revenueStats($shop->id);

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
            'subheading' => 'Track real-time inventory visibility, spare parts activity, and all revenue.',
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
            'dashboardTrendRanges' => $this->dashboardTrendRanges($selectedTrendRange),
            'dashboardTrendRange' => $selectedTrendRange,
            'dashboardTrendMonths' => is_numeric($selectedTrendRange) ? (int) $selectedTrendRange : 3,
            'lowStockByCategory' => $lowStockByCategory,
            'revenueStats' => $revenueStats,
            'lowStockParts' => $parts
                ->where('is_active', true)
                ->filter(fn ($part) => $part->current_stock <= 0 || ($part->current_stock > 0 && $part->current_stock < $part->minimum_stock))
                ->take(6)
                ->values(),
            'recentMovements' => $recentMovements,
            'dashboardMetricsUrl' => route('dashboard.metrics.inventory'),
        ]));
    }

    public function inventoryMetrics(Request $request): JsonResponse
    {
        $shop = $request->user()->workspaceShop();
        abort_if($shop === null, 403, 'Shop profile not found.');

        $range = $this->normalizeTrendRange((string) $request->query('range', $request->query('months', $this->defaultTrendRange())));

        $parts = InventoryMetrics::partsWithStockQuery($shop)->get();
        $summary = InventoryMetrics::summarizeParts($parts);

        return response()->json([
            'kpis' => [
                'total_skus' => number_format($summary['totalSkus']),
                'low_stock' => number_format($summary['lowStock']),
                'out_of_stock' => number_format($summary['outOfStock']),
                'inventory_value' => InventoryMetrics::formatCurrency($summary['inventoryValue']),
            ],
            'trend' => InventoryMetrics::movementTrendForDashboardRange($shop, $range),
            'low_stock_by_category' => InventoryMetrics::lowStockByCategory($parts),
            'revenue_stats' => $this->revenueStats($shop->id),
            'trend_range' => $range,
            'trend_range_months' => is_numeric($range) ? (int) $range : null,
            'updated_at' => now('Asia/Manila')->toIso8601String(),
        ]);
    }

    private function dashboardTrendRanges(string $selectedRange): array
    {
        return collect([
            ['value' => 'jan-jun', 'label' => 'Jan-Jun'],
            ['value' => 'jul-dec', 'label' => 'Jul-Dec'],
        ])
            ->map(fn (array $range): array => [
                'value' => $range['value'],
                'months' => is_numeric($range['value']) ? (int) $range['value'] : null,
                'label' => $range['label'],
                'active' => $range['value'] === $selectedRange,
            ])
            ->all();
    }

    private function normalizeTrendRange(string $range): string
    {
        $normalized = strtolower(trim($range));

        return in_array($normalized, ['jan-jun', 'jul-dec'], true)
            ? $normalized
            : $this->defaultTrendRange();
    }

    private function defaultTrendRange(): string
    {
        return (int) now('Asia/Manila')->format('n') > 6 ? 'jul-dec' : 'jan-jun';
    }

    private function revenueStats(int $shopId): array
    {
        return collect([
            DatePeriods::PERIOD_DAILY => 'Today',
            DatePeriods::PERIOD_WEEKLY => 'This Week',
            DatePeriods::PERIOD_MONTHLY => 'This Month',
            DatePeriods::PERIOD_YEARLY => 'This Year',
        ])
            ->map(function (string $caption, string $period) use ($shopId): array {
                [$from, $to] = DatePeriods::bounds($period);
                $revenue = JobOrder::query()
                    ->forShop($shopId)
                    ->where('status', JobOrder::STATUS_COMPLETED)
                    ->whereNotNull('completed_at')
                    ->whereBetween('completed_at', [$from, $to])
                    ->sum('estimated_cost');

                return [
                    'period' => $period,
                    'label' => DatePeriods::label($period).' Revenue',
                    'value' => InventoryMetrics::formatCurrency((float) $revenue),
                    'caption' => $caption,
                ];
            })
            ->values()
            ->all();
    }

    private function baseData(array $pageData): array
    {
        $user = auth()->user();
        $shop = $user?->workspaceShop();

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
