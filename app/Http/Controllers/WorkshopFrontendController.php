<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\JobOrder;
use App\Models\Part;
use App\Models\Shop;
use App\Models\StockMovement;
use App\Support\InventoryMetrics;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;

class WorkshopFrontendController extends Controller
{
    public function landing(): View
    {
        $landingMetrics = $this->buildLandingMetricsPayload();

        $moduleHighlights = [
            [
                'title' => 'Dashboard',
                'description' => 'Operational overview with live inventory KPIs, movement flow, and category risk alerts.',
                'route' => 'dashboard',
                'icon' => 'dashboard',
            ],
            [
                'title' => 'Inventory',
                'description' => 'Part catalog, stock movement journal, low-stock monitoring, and valuation in one workspace.',
                'route' => 'inventory',
                'icon' => 'inventory',
            ],
            [
                'title' => 'Job Orders',
                'description' => 'Track each vehicle from intake, repair staging, and completion with status visibility.',
                'route' => 'job-orders',
                'icon' => 'job-orders',
            ],
            [
                'title' => 'Billing',
                'description' => 'Invoice-ready totals generated directly from job order and parts usage records.',
                'route' => 'billing',
                'icon' => 'billing',
            ],
            [
                'title' => 'Reports',
                'description' => 'Revenue, growth, customer contribution, and operational trend insights from live records.',
                'route' => 'reports',
                'icon' => 'reports',
            ],
            [
                'title' => 'Customers',
                'description' => 'Centralized customer profile and service history to keep communication consistent.',
                'route' => 'customers',
                'icon' => 'customers',
            ],
        ];

        $primaryRoute = auth()->check() ? 'dashboard' : 'register';
        $secondaryRoute = auth()->check() ? 'inventory' : 'login';

        return view('pages.landing', [
            'pageTitle' => 'MotoX | Workshop Operating System',
            'projectSnapshot' => $landingMetrics['projectSnapshot'],
            'workspacePulse' => $landingMetrics['workspacePulse'],
            'moduleHighlights' => $moduleHighlights,
            'timeWindows' => $landingMetrics['timeWindows'],
            'landingUpdatedAt' => $landingMetrics['landingUpdatedAt'],
            'landingMetricsUrl' => route('landing.metrics'),
            'primaryCtaRoute' => route($primaryRoute),
            'primaryCtaLabel' => auth()->check() ? 'Open Dashboard' : 'Sign up',
            'secondaryCtaRoute' => route($secondaryRoute),
            'secondaryCtaLabel' => auth()->check() ? 'Open Inventory' : 'Log In',
        ]);
    }

    public function landingMetrics(Request $request): JsonResponse
    {
        return response()->json(array_merge(
            $this->buildLandingMetricsPayload(),
            ['updated_at' => now('Asia/Manila')->toIso8601String()]
        ));
    }

    /**
     * @return array{
     *     projectSnapshot:array<int,array{key:string,label:string,value:string,note:string}>,
     *     workspacePulse:array<int,array{key:string,title:string,value:string,tone:string}>,
     *     timeWindows:array<int,array{key:string,label:string,value:string,note:string}>,
     *     landingUpdatedAt:string
     * }
     */
    private function buildLandingMetricsPayload(): array
    {
        $nowPh = now('Asia/Manila');
        $monthStartUtc = $nowPh->copy()->startOfMonth()->setTimezone('UTC');
        $monthEndUtc = $nowPh->copy()->endOfMonth()->setTimezone('UTC');
        $todayStartUtc = $nowPh->copy()->startOfDay()->setTimezone('UTC');
        $todayEndUtc = $nowPh->copy()->endOfDay()->setTimezone('UTC');

        $parts = Part::query()->withCurrentStock()->get();
        $inventorySummary = InventoryMetrics::summarizeParts($parts);

        $completedMonthRevenue = (float) JobOrder::query()
            ->where('status', JobOrder::STATUS_COMPLETED)
            ->whereBetween('completed_at', [$monthStartUtc, $monthEndUtc])
            ->sum('estimated_cost');

        $projectSnapshot = [
            [
                'key' => 'active_workshops',
                'label' => 'Active Workshops',
                'value' => number_format(Shop::query()->count()),
                'note' => 'Shops onboarded in MotoX',
            ],
            [
                'key' => 'open_job_orders',
                'label' => 'Open Job Orders',
                'value' => number_format(
                    JobOrder::query()
                        ->whereIn('status', [JobOrder::STATUS_PENDING, JobOrder::STATUS_IN_PROGRESS])
                        ->count()
                ),
                'note' => 'Pending and in-progress repairs',
            ],
            [
                'key' => 'tracked_skus',
                'label' => 'Tracked SKUs',
                'value' => number_format($inventorySummary['totalSkus']),
                'note' => 'Inventory records with movement logs',
            ],
            [
                'key' => 'monthly_revenue',
                'label' => 'Monthly Revenue',
                'value' => 'PHP '.number_format($completedMonthRevenue, 2),
                'note' => 'Closed job orders this month',
            ],
        ];

        $workspacePulse = [
            [
                'key' => 'customers_in_crm',
                'title' => 'Customers in CRM',
                'value' => number_format(Customer::query()->count()),
                'tone' => 'success',
            ],
            [
                'key' => 'low_stock_alerts',
                'title' => 'Low-stock Alerts',
                'value' => number_format($inventorySummary['lowStock']),
                'tone' => 'warning',
            ],
            [
                'key' => 'invoices_issued',
                'title' => 'Invoices Issued',
                'value' => number_format(Invoice::query()->count()),
                'tone' => 'accent',
            ],
        ];

        $timeWindows = [
            [
                'key' => 'movements_today',
                'label' => 'Stock Movements Today',
                'value' => number_format(
                    StockMovement::query()
                        ->whereBetween('moved_at', [$todayStartUtc, $todayEndUtc])
                        ->count()
                ),
                'note' => 'Recorded in Philippine business day',
            ],
            [
                'key' => 'completed_jobs_this_month',
                'label' => 'Completed Jobs This Month',
                'value' => number_format(
                    JobOrder::query()
                        ->where('status', JobOrder::STATUS_COMPLETED)
                        ->whereBetween('completed_at', [$monthStartUtc, $monthEndUtc])
                        ->count()
                ),
                'note' => 'Closed from verified job order records',
            ],
            [
                'key' => 'onhand_inventory_value',
                'label' => 'On-hand Inventory Value',
                'value' => InventoryMetrics::formatCurrency($inventorySummary['inventoryValue']),
                'note' => 'Computed from current stock and unit prices',
            ],
        ];

        return [
            'projectSnapshot' => $projectSnapshot,
            'workspacePulse' => $workspacePulse,
            'timeWindows' => $timeWindows,
            'landingUpdatedAt' => $nowPh->format('M d, Y h:i A').' PHT',
        ];
    }
}
