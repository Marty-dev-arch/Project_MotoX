<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\BuildsPageData;
use App\Models\Customer;
use App\Models\JobOrder;
use App\Models\Shop;
use App\Support\DatePeriods;
use App\Support\InventoryMetrics;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\View\View;

class ReportsController extends Controller
{
    use BuildsPageData;

    public function index(Request $request): View
    {
        $shop = $request->user()->workspaceShop();
        abort_if($shop === null, 403, 'Shop profile not found.');

        $period = $this->resolvePeriod($request);
        $reportData = $this->buildReportSnapshot($shop, $period);

        return view('pages.reports', $this->buildPageData('reports', [
            'heading' => 'Reports',
            'subheading' => 'Live operational metrics generated from your actual customer, inventory, and job-order data.',
            'showHeaderSearch' => false,
            'stats' => $reportData['stats'],
            'activePeriod' => $period,
            'monthlyTrend' => $reportData['monthlyTrend'],
            'monthlyTrendSummary' => $reportData['monthlyTrendSummary'],
            'monthlyBars' => $reportData['monthlyBars'],
            'statusBreakdown' => $reportData['statusBreakdown'],
            'topCustomers' => $reportData['topCustomers'],
            'reportsMetricsUrl' => route('reports.metrics'),
            'reportsExportUrl' => route('reports.export'),
        ]));
    }

    public function metrics(Request $request): JsonResponse
    {
        $shop = $request->user()->workspaceShop();
        abort_if($shop === null, 403, 'Shop profile not found.');

        $period = $this->resolvePeriod($request);
        $reportData = $this->buildReportSnapshot($shop, $period);

        return response()->json([
            'stats' => $reportData['stats'],
            'period' => $period,
            'period_label' => DatePeriods::label($period),
            'monthly_trend' => $reportData['monthlyTrend'],
            'monthly_trend_summary' => $reportData['monthlyTrendSummary'],
            'status_breakdown' => $reportData['statusBreakdown'],
            'top_customers' => $reportData['topCustomers']->map(fn (array $row): array => $this->topCustomerPayload($row))->values(),
            'updated_display' => now('Asia/Manila')->format('F j, Y, l h:i:s A').' PHT',
            'updated_at' => now('Asia/Manila')->toIso8601String(),
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        $shop = $request->user()->workspaceShop();
        abort_if($shop === null, 403, 'Shop profile not found.');

        $period = $this->resolvePeriod($request);
        $reportData = $this->buildReportSnapshot($shop, $period);
        $filename = 'motox-reports-'.DatePeriods::filenameToken($period).'-'.now('Asia/Manila')->format('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($reportData, $period): void {
            $output = fopen('php://output', 'w');

            fputcsv($output, ['MotoX Reports Export']);
            fputcsv($output, ['Period', DatePeriods::label($period)]);
            fputcsv($output, ['Generated At', now('Asia/Manila')->format('Y-m-d H:i:s').' PHT']);
            fputcsv($output, []);
            fputcsv($output, ['Overall Statistics']);
            fputcsv($output, ['Revenue', number_format((float) $reportData['stats']['month_revenue'], 2, '.', '')]);
            fputcsv($output, ['Growth Rate %', number_format((float) $reportData['stats']['growth_rate'], 2, '.', '')]);
            fputcsv($output, ['Closed Jobs', (int) $reportData['stats']['jobs_closed']]);
            fputcsv($output, ['Inventory Value', number_format((float) $reportData['stats']['inventory_value'], 2, '.', '')]);
            fputcsv($output, []);
            fputcsv($output, ['Monthly Revenue Trend']);
            fputcsv($output, ['Month', 'Revenue']);
            foreach ($reportData['monthlyTrend'] as $row) {
                fputcsv($output, [$row['label'], number_format((float) $row['value'], 2, '.', '')]);
            }
            fputcsv($output, []);
            fputcsv($output, ['Job Status Breakdown']);
            fputcsv($output, ['Status', 'Count']);
            foreach ($reportData['statusBreakdown'] as $row) {
                fputcsv($output, [$row['status'], (int) $row['count']]);
            }
            fputcsv($output, []);
            fputcsv($output, ['Customer Billing Records']);
            fputcsv($output, ['Customer', 'Total Jobs', 'Total Billed', 'Latest Job Date']);
            foreach ($reportData['topCustomers'] as $row) {
                fputcsv($output, [
                    $row['name'],
                    (int) $row['jobs'],
                    $row['billed_raw'] ?? 0,
                    $row['latest_display'] ?? '-',
                ]);
            }
            fputcsv($output, []);
            fputcsv($output, ['Closed Job Records']);
            fputcsv($output, ['Job Order', 'Customer', 'Vehicle', 'Amount', 'Completed At']);
            foreach ($reportData['closedJobs'] as $row) {
                fputcsv($output, [
                    $row['order_number'],
                    $row['customer'],
                    $row['vehicle'],
                    number_format((float) $row['amount'], 2, '.', ''),
                    $row['completed_at']?->timezone('Asia/Manila')->format('Y-m-d H:i:s').' PHT',
                ]);
            }

            fclose($output);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function buildReportSnapshot(Shop $shop, string $period): array
    {
        $bounds = DatePeriods::bounds($period);
        [$periodFrom, $periodTo] = $bounds ?? [null, null];

        if ($periodFrom && $periodTo) {
            $periodSpanDays = max(1, $periodFrom->diffInDays($periodTo) + 1);
            $previousFrom = $periodFrom->copy()->subDays($periodSpanDays);
            $previousTo = $periodTo->copy()->subDays($periodSpanDays);
        } else {
            $previousFrom = null;
            $previousTo = null;
        }

        $orders = JobOrder::query()
            ->forShop($shop)
            ->with('customer')
            ->get();

        $completedOrders = $orders->where('status', JobOrder::STATUS_COMPLETED);
        $parts = InventoryMetrics::partsWithStockQuery($shop)->get();
        $inventorySummary = InventoryMetrics::summarizeParts($parts);

        $now = now('Asia/Manila');

        $currentPeriodOrders = $period === 'all'
            ? $completedOrders
            : $completedOrders->filter(fn (JobOrder $order): bool => $order->completed_at !== null && $order->completed_at->between($periodFrom, $periodTo));

        $currentPeriodRevenue = (float) $currentPeriodOrders
            ->sum(fn (JobOrder $order): float => (float) $order->estimated_cost);

        $currentMonthStart = $now->copy()->startOfMonth();
        $currentMonthEnd = $now->copy()->endOfMonth();
        $previousMonthStart = $now->copy()->subMonthNoOverflow()->startOfMonth();
        $previousMonthEnd = $now->copy()->subMonthNoOverflow()->endOfMonth();

        $previousPeriodRevenue = ($previousFrom && $previousTo)
            ? (float) $completedOrders
                ->filter(fn (JobOrder $order): bool => $order->completed_at !== null && $order->completed_at->between($previousFrom, $previousTo))
                ->sum(fn (JobOrder $order): float => (float) $order->estimated_cost)
            : (float) $completedOrders
                ->filter(fn (JobOrder $order): bool => $order->completed_at !== null && $order->completed_at->between($previousMonthStart, $previousMonthEnd))
                ->sum(fn (JobOrder $order): float => (float) $order->estimated_cost);

        $growthBaseRevenue = $period === 'all'
            ? (float) $completedOrders
                ->filter(fn (JobOrder $order): bool => $order->completed_at !== null && $order->completed_at->between($currentMonthStart, $currentMonthEnd))
                ->sum(fn (JobOrder $order): float => (float) $order->estimated_cost)
            : $currentPeriodRevenue;

        $growthRate = $previousPeriodRevenue > 0
            ? (($growthBaseRevenue - $previousPeriodRevenue) / $previousPeriodRevenue) * 100
            : ($growthBaseRevenue > 0 ? 100 : 0);

        $monthly = collect(range(11, 0))
            ->map(function (int $offset) use ($completedOrders, $now): array {
                $start = $now->copy()->subMonths($offset)->startOfMonth();
                $end = $start->isSameMonth($now)
                    ? $now->copy()
                    : $start->copy()->endOfMonth();
                $total = $completedOrders
                    ->filter(fn (JobOrder $order): bool => $order->completed_at !== null && $order->completed_at->between($start, $end))
                    ->sum(fn (JobOrder $order): float => (float) $order->estimated_cost);

                return [
                    'label' => $start->format('M'),
                    'date_label' => $end->format('F j, Y, l'),
                    'date_token' => $end->toDateString(),
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
            'date_label' => $row['date_label'],
            'date_token' => $row['date_token'],
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

        $topCustomersQuery = Customer::query()
            ->forShop($shop)
            ->withCount(['jobOrders' => function ($query) use ($periodFrom, $periodTo): void {
                $query->where('status', JobOrder::STATUS_COMPLETED)
                    ->whereNotNull('completed_at');

                if ($periodFrom && $periodTo) {
                    $query->whereBetween('completed_at', [$periodFrom, $periodTo]);
                }
            }])
            ->withSum(['jobOrders' => function ($query) use ($periodFrom, $periodTo): void {
                $query->where('status', JobOrder::STATUS_COMPLETED)
                    ->whereNotNull('completed_at');

                if ($periodFrom && $periodTo) {
                    $query->whereBetween('completed_at', [$periodFrom, $periodTo]);
                }
            }], 'estimated_cost')
            ->withMax(['jobOrders' => function ($query) use ($periodFrom, $periodTo): void {
                $query->where('status', JobOrder::STATUS_COMPLETED)
                    ->whereNotNull('completed_at');

                if ($periodFrom && $periodTo) {
                    $query->whereBetween('completed_at', [$periodFrom, $periodTo]);
                }
            }], 'completed_at');

        $topCustomersQuery->whereHas('jobOrders', function ($query) use ($periodFrom, $periodTo): void {
            $query->where('status', JobOrder::STATUS_COMPLETED)
                ->whereNotNull('completed_at');

            if ($periodFrom && $periodTo) {
                $query->whereBetween('completed_at', [$periodFrom, $periodTo]);
            }
        });

        $topCustomers = $topCustomersQuery
            ->orderByDesc('job_orders_sum_estimated_cost')
            ->get()
            ->map(fn (Customer $customer): array => [
                'customer_id' => $customer->id,
                'name' => $customer->name,
                'jobs' => $customer->job_orders_count,
                'billed_raw' => (float) ($customer->job_orders_sum_estimated_cost ?? 0),
                'billed' => $this->money((float) ($customer->job_orders_sum_estimated_cost ?? 0)),
                'latest_job_at' => $customer->job_orders_max_completed_at
                    ? \Illuminate\Support\Carbon::parse($customer->job_orders_max_completed_at)
                    : null,
            ]);

        $latestDisplayDates = $currentPeriodOrders
            ->filter(fn (JobOrder $order): bool => $order->customer_id !== null)
            ->groupBy('customer_id')
            ->map(function ($orders): string {
                $latest = $orders->sortByDesc(fn (JobOrder $order): int => $order->completed_at?->timestamp ?? 0)->first();
                $displayDate = $latest?->scheduled_for ?: $latest?->completed_at;

                return $displayDate
                    ? $displayDate->timezone('Asia/Manila')->format('M d, Y')
                    : now('Asia/Manila')->format('M d, Y');
            });

        $topCustomers = $topCustomers->map(function (array $row) use ($latestDisplayDates): array {
            $customerId = $row['customer_id'] ?? null;

            $row['latest_display'] = $customerId && $latestDisplayDates->has($customerId)
                ? $latestDisplayDates->get($customerId)
                : ($row['latest_job_at']?->timezone('Asia/Manila')->format('M d, Y') ?? now('Asia/Manila')->format('M d, Y'));

            return $row;
        });

        $closedJobs = $currentPeriodOrders
            ->sortByDesc('completed_at')
            ->values()
            ->map(fn (JobOrder $order): array => [
                'order_number' => $order->order_number,
                'customer' => $order->customer?->name ?? 'Walk-in Customer',
                'vehicle' => $order->vehicle,
                'amount' => (float) $order->estimated_cost,
                'completed_at' => $order->completed_at,
            ]);

        return [
            'stats' => [
                'month_revenue' => $currentPeriodRevenue,
                'growth_rate' => $growthRate,
                'jobs_closed' => $currentPeriodOrders->count(),
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
            'closedJobs' => $closedJobs,
        ];
    }

    private function resolvePeriod(Request $request): string
    {
        return DatePeriods::normalize($request->query('period', 'all'));
    }

    private function money(float $amount): string
    {
        return 'PHP '.number_format($amount, 2);
    }

    private function topCustomerPayload(array $row): array
    {
        $latest = $row['latest_job_at'] ? \Illuminate\Support\Carbon::parse($row['latest_job_at']) : null;

        return [
            'name' => $row['name'],
            'jobs' => (int) $row['jobs'],
            'billed' => $row['billed'],
            'latest_job_at' => $latest?->toIso8601String(),
            'latest_display' => $row['latest_display'] ?? ($latest?->timezone('Asia/Manila')->format('M d, Y') ?? now('Asia/Manila')->format('M d, Y')),
        ];
    }
}
