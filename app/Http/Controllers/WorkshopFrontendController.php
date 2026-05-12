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

    public function policies(): View
    {
        return $this->publicInfoPage([
            'pageTitle' => 'MotoX Policies',
            'eyebrow' => 'Workshop Use Policy',
            'title' => 'Policies',
            'description' => 'MotoX policies explain how mechanics, shop owners, and staff should use the system for reliable workshop operations.',
            'sections' => [
                [
                    'title' => 'Account Responsibility',
                    'body' => 'Keep login access private. Every inventory movement, job order update, customer edit, and billing action should be performed by the assigned workshop user.',
                ],
                [
                    'title' => 'Operational Records',
                    'body' => 'Use MotoX records as a working source of truth for job orders, parts, billing, customer profiles, and reports. Review entries before using them for final invoices.',
                ],
                [
                    'title' => 'Acceptable Use',
                    'body' => 'Do not upload harmful files, misleading customer details, or unrelated records. Keep photos and notes connected to real workshop activity.',
                ],
                [
                    'title' => 'Inventory Accuracy',
                    'body' => 'Update stock when parts are bought, used, returned, damaged, or corrected. Dashboard alerts and reports depend on accurate movement records.',
                ],
                [
                    'title' => 'Billing Review',
                    'body' => 'Check customer names, vehicle details, job order status, and estimated cost before sharing receipt files or using totals for reports.',
                ],
                [
                    'title' => 'Staff Workflow',
                    'body' => 'Admins should keep user roles, shop details, and notification settings current so staff see the right pages and alerts.',
                ],
            ],
        ]);
    }

    public function privacy(): View
    {
        return $this->publicInfoPage([
            'pageTitle' => 'MotoX Privacy',
            'eyebrow' => 'Data Handling',
            'title' => 'Privacy Policy',
            'description' => 'MotoX stores workshop information so mechanics can manage customers, vehicles, repairs, stock, billing, and reports in one place.',
            'sections' => [
                [
                    'title' => 'Information Used',
                    'body' => 'The system may store customer names, contact details, vehicle details, profile photos, job order notes, inventory activity, invoices, and payment-related records.',
                ],
                [
                    'title' => 'Purpose',
                    'body' => 'This information is used to run workshop workflows, prepare receipts, track service history, monitor inventory, and generate operational reports.',
                ],
                [
                    'title' => 'Access',
                    'body' => 'Only authorized workshop users should access customer and shop records. Remove or update incorrect records when they are no longer needed for operations.',
                ],
                [
                    'title' => 'Photos and Attachments',
                    'body' => 'Profile photos and part images should be used only to identify customers, walk-ins, vehicles, or stock records inside the workshop workflow.',
                ],
                [
                    'title' => 'Operational Reports',
                    'body' => 'Reports summarize records already stored in the system. They are designed for workshop decisions, not for selling customer information.',
                ],
                [
                    'title' => 'Good Practice',
                    'body' => 'Use strong passwords, log out on shared devices, and keep browser access limited to trusted staff computers or phones.',
                ],
            ],
        ]);
    }

    public function cookies(): View
    {
        return $this->publicInfoPage([
            'pageTitle' => 'MotoX Cookies',
            'eyebrow' => 'Browser Preferences',
            'title' => 'Cookies',
            'description' => 'MotoX uses essential browser storage to keep the application usable, consistent, and secure while you work.',
            'sections' => [
                [
                    'title' => 'Session Storage',
                    'body' => 'Authentication and security state are required so the system knows which shop workspace should be opened.',
                ],
                [
                    'title' => 'Interface Preferences',
                    'body' => 'Theme mode, language, chart ranges, filters, and similar preferences may be kept locally so your workflow stays consistent on the same device.',
                ],
                [
                    'title' => 'Control',
                    'body' => 'You can clear browser storage from your browser settings, but doing so may reset saved preferences and require logging in again.',
                ],
                [
                    'title' => 'Security Tokens',
                    'body' => 'Security cookies help protect forms, login sessions, and authenticated requests while you use MotoX.',
                ],
                [
                    'title' => 'No Advertising Cookies',
                    'body' => 'MotoX does not need advertising cookies for workshop pages. Browser storage is focused on app security and user preferences.',
                ],
                [
                    'title' => 'After Clearing Cookies',
                    'body' => 'If you clear cookies, sign in again and reselect your preferred theme, language, filters, and chart ranges.',
                ],
            ],
        ]);
    }

    public function support(): View
    {
        return $this->publicInfoPage([
            'pageTitle' => 'MotoX Help Me',
            'eyebrow' => 'Help Center',
            'title' => 'Help Me',
            'description' => 'Use this guide when you need to know where to click, what a page is for, or how to finish a common workshop task in MotoX.',
            'support' => true,
            'sections' => [
                [
                    'title' => 'Dashboard',
                    'body' => 'Start here to check today\'s inventory health, low-stock parts, recent stock movement, and the stock-flow chart. Use the date filter when you only want today, this week, this month, or this year.',
                ],
                [
                    'title' => 'Inventory',
                    'body' => 'Add parts with SKU, category, box quantity, price per box, and price per pieces. Use Stock In when buying inventory and Stock Out when parts are used or removed.',
                ],
                [
                    'title' => 'Job Orders',
                    'body' => 'Create a job order for a customer or walk-in, choose the vehicle, add repair notes, set the estimated cost, and update the status as work moves from pending to completed.',
                ],
                [
                    'title' => 'Billing Receipts',
                    'body' => 'Open Billing, find the customer or invoice, then use Actions and click Receipt to download a receipt image. Paid job orders show as paid; open job orders stay pending.',
                ],
                [
                    'title' => 'Customers and Reports',
                    'body' => 'Use Customers for contact details and service history. Use Reports to review billing totals, customer contribution, and business activity over time.',
                ],
                [
                    'title' => 'Settings',
                    'body' => 'Update your shop profile, profile photo, labor rate, language, and notification preferences. Use the sidebar appearance toggle to switch theme any time.',
                ],
            ],
        ]);
    }

    private function publicInfoPage(array $content): View
    {
        return view('pages.public-info', array_merge([
            'pageTitle' => 'MotoX',
            'eyebrow' => 'MotoX',
            'title' => 'MotoX',
            'description' => '',
            'sections' => [],
            'support' => false,
        ], $content));
    }

    private function buildLandingMetricsPayload(): array
    {
        $nowPh = now('Asia/Manila');
        $monthStartUtc = $nowPh->copy()->startOfMonth()->setTimezone('UTC');
        $monthEndUtc = $nowPh->copy()->endOfMonth()->setTimezone('UTC');
        $todayStartUtc = $nowPh->copy()->startOfDay()->setTimezone('UTC');
        $todayEndUtc = $nowPh->copy()->endOfDay()->setTimezone('UTC');

        $parts = Part::query()->withCurrentStock()->get();
        $inventorySummary = InventoryMetrics::summarizeParts($parts);

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
