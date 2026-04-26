<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\BuildsPageData;
use App\Models\Customer;
use App\Models\JobOrder;
use App\Models\Shop;
use App\Support\InventoryMetrics;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportsController extends Controller
{
    use BuildsPageData;

    public function index(Request $request): View
    {
        $shop = $request->user()->shop;
        abort_if($shop === null, 403, 'Shop profile not found.');

        $reportData = $this->buildReportSnapshot($shop);

        return view('pages.reports', $this->buildPageData('reports', [
            'heading' => 'Reports',
            'subheading' => 'Live operational metrics generated from your actual customer, inventory, and job-order data.',
            'searchPlaceholder' => 'Search report section...',
            'stats' => $reportData['stats'],
            'monthlyTrend' => $reportData['monthlyTrend'],
            'monthlyTrendSummary' => $reportData['monthlyTrendSummary'],
            'monthlyBars' => $reportData['monthlyBars'],
            'statusBreakdown' => $reportData['statusBreakdown'],
            'topCustomers' => $reportData['topCustomers'],
            'reportsMetricsUrl' => route('reports.metrics'),
        ]));
    }

    public function metrics(Request $request): JsonResponse
    {
        $shop = $request->user()->shop;
        abort_if($shop === null, 403, 'Shop profile not found.');

        $reportData = $this->buildReportSnapshot($shop);

        return response()->json([
            'stats' => $reportData['stats'],
            'monthly_trend' => $reportData['monthlyTrend'],
            'monthly_trend_summary' => $reportData['monthlyTrendSummary'],
            'status_breakdown' => $reportData['statusBreakdown'],
            'updated_at' => now('Asia/Manila')->toIso8601String(),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function buildReportSnapshot(Shop $shop): array
    {
        $orders = JobOrder::query()
            ->forShop($shop)
            ->with('customer')
            ->get();

        $completedOrders = $orders->where('status', JobOrder::STATUS_COMPLETED);
        $parts = InventoryMetrics::partsWithStockQuery($shop)->get();
        $inventorySummary = InventoryMetrics::summarizeParts($parts);

        $now = now();
        $monthStart = $now->copy()->startOfMonth();
        $lastMonthStart = $monthStart->copy()->subMonth();
        $lastMonthEnd = $monthStart->copy()->subSecond();

        $currentMonthRevenue = (float) $completedOrders
            ->filter(fn (JobOrder $order): bool => $order->completed_at !== null && $order->completed_at >= $monthStart)
            ->sum(fn (JobOrder $order): float => (float) $order->estimated_cost);

        $lastMonthRevenue = (float) $completedOrders
            ->filter(fn (JobOrder $order): bool => $order->completed_at !== null && $order->completed_at->between($lastMonthStart, $lastMonthEnd))
            ->sum(fn (JobOrder $order): float => (float) $order->estimated_cost);

        $growthRate = $lastMonthRevenue > 0
            ? (($currentMonthRevenue - $lastMonthRevenue) / $lastMonthRevenue) * 100
            : ($currentMonthRevenue > 0 ? 100 : 0);

        $monthly = collect(range(11, 0))
            ->map(function (int $offset) use ($completedOrders, $now): array {
                $start = $now->copy()->subMonths($offset)->startOfMonth();
                $end = $start->copy()->endOfMonth();
                $total = $completedOrders
                    ->filter(fn (JobOrder $order): bool => $order->completed_at !== null && $order->completed_at->between($start, $end))
                    ->sum(fn (JobOrder $order): float => (float) $order->estimated_cost);

                return [
                    'label' => $start->format('M'),
                    'value' => $total,
                ];
            })
            ->values();

        $maxMonthly = max(1, (float) $monthly->max('value'));
        $monthlyBars = $monthly->map(fn (array $row): array => [
            'label' => $row['label'],
            'display' => $this->money((float) $row['value']),
            'height' => max(10, (int) round(($row['value'] / $maxMonthly) * 100)),
        ]);

        $monthlyTrend = $monthly->map(fn (array $row): array => [
            'label' => $row['label'],
            'value' => (float) $row['value'],
            'display' => $this->money((float) $row['value']),
        ])->values();

        $peakMonth = $monthlyTrend->sortByDesc('value')->first();
        $averageMonthly = (float) $monthlyTrend->avg('value');
        $recentMonth = (float) ($monthlyTrend->last()['value'] ?? 0);

        $statusBreakdown = collect(JobOrder::statuses())->map(fn (string $status): array => [
            'status' => str_replace('_', ' ', ucfirst($status)),
            'count' => $orders->where('status', $status)->count(),
        ])->values();

        $topCustomers = Customer::query()
            ->forShop($shop)
            ->withCount('jobOrders')
            ->withSum('jobOrders', 'estimated_cost')
            ->orderByDesc('job_orders_sum_estimated_cost')
            ->take(8)
            ->get()
            ->map(fn (Customer $customer): array => [
                'name' => $customer->name,
                'jobs' => $customer->job_orders_count,
                'billed' => $this->money((float) ($customer->job_orders_sum_estimated_cost ?? 0)),
            ]);

        return [
            'stats' => [
                'month_revenue' => $currentMonthRevenue,
                'growth_rate' => $growthRate,
                'jobs_closed' => $completedOrders->count(),
                'inventory_value' => (float) $inventorySummary['inventoryValue'],
            ],
            'monthlyTrend' => $monthlyTrend,
            'monthlyTrendSummary' => [
                'latest' => $this->money($recentMonth),
                'average' => $this->money($averageMonthly),
                'peak' => sprintf(
                    '%s (%s)',
                    $peakMonth['label'] ?? '-',
                    $this->money((float) ($peakMonth['value'] ?? 0))
                ),
            ],
            'monthlyBars' => $monthlyBars,
            'statusBreakdown' => $statusBreakdown,
            'topCustomers' => $topCustomers,
        ];
    }

    private function money(float $amount): string
    {
        return 'PHP '.number_format($amount, 2);
    }
}
