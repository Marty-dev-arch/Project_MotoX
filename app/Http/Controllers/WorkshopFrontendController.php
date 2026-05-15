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
            'pageTitle' => 'MotoX - your trusted workshop',
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
            'updatedAt' => 'May 15, 2026',
            'icon' => 'lock',
            'description' => 'MotoX stores workshop information so mechanics can manage customers, vehicles, repairs, stock, billing, and reports in one place.',
            'sections' => [
                [
                    'title' => 'Introduction',
                    'body' => 'MotoX is committed to protecting workshop and customer information. This Privacy Policy explains how information is collected, used, protected, and handled when you use the system.',
                ],
                [
                    'title' => 'Information We Collect',
                    'body' => 'MotoX may collect information about you and your workshop in several ways. The information used by the system includes:',
                    'items' => [
                        ['label' => 'Personal Data', 'text' => 'Customer names, email addresses, phone numbers, addresses, and profile photos entered by authorized workshop users.'],
                        ['label' => 'Workshop Records', 'text' => 'Vehicle details, job order notes, inventory activity, billing records, receipts, and service history.'],
                        ['label' => 'Usage Data', 'text' => 'Basic browser, session, filter, theme, and interaction information needed to keep the application secure and usable.'],
                    ],
                ],
                [
                    'title' => 'Use of Your Information',
                    'body' => 'Having accurate information helps MotoX provide a smooth and reliable workshop workflow. Specifically, information may be used to:',
                    'items' => [
                        'Create and manage workshop accounts.',
                        'Prepare job orders, service records, invoices, and receipts.',
                        'Track stock movement, low inventory, and billing status.',
                        'Generate dashboard, report, and history views for authorized users.',
                        'Improve application reliability, security, and daily usability.',
                    ],
                ],
                [
                    'title' => 'Disclosure of Your Information',
                    'body' => 'MotoX does not sell customer information. Information may be disclosed only in limited operational or legal situations:',
                    'items' => [
                        ['label' => 'By Law or to Protect Rights', 'text' => 'If disclosure is required to respond to legal process, protect users, or prevent misuse of the system.'],
                        ['label' => 'Service Providers', 'text' => 'If a trusted provider is needed for hosting, storage, authentication, security, or support services.'],
                    ],
                ],
                [
                    'title' => 'Security of Your Information',
                    'body' => 'MotoX uses application safeguards, access control, and secure session handling to help protect personal and workshop information. No system can guarantee perfect security, so shared devices should be logged out after use.',
                ],
                [
                    'title' => 'Contact Us',
                    'body' => 'If you have questions or comments about this Privacy Policy, contact the MotoX team through your workshop administrator or support channel.',
                    'contact' => [
                        'MotoX Inc.',
                        'Privacy Department',
                        'support@motox.local',
                    ],
                ],
            ],
        ]);
    }

    public function cookies(): View
    {
        return $this->publicInfoPage([
            'pageTitle' => 'MotoX Cookies',
            'eyebrow' => 'Browser Preferences',
            'title' => 'Cookies Policy',
            'updatedAt' => 'May 15, 2026',
            'icon' => 'cookie',
            'description' => 'MotoX uses essential browser storage to keep the application usable, consistent, and secure while you work.',
            'sections' => [
                [
                    'title' => 'What are cookies?',
                    'body' => 'Cookies and browser storage are small pieces of information kept by your browser. MotoX uses them to recognize your session, protect forms, and make your next visit easier.',
                ],
                [
                    'title' => 'Types of cookies we use',
                    'body' => 'MotoX keeps browser storage focused on app security and workflow preferences.',
                    'cards' => [
                        [
                            'icon' => 'lock',
                            'title' => 'Essential Cookies',
                            'body' => 'These cookies are necessary for login sessions, authenticated requests, and protection against unwanted form submissions.',
                        ],
                        [
                            'icon' => 'settings',
                            'title' => 'Preference Cookies',
                            'body' => 'These remember theme mode, language, table filters, chart ranges, and similar interface choices on the same device.',
                        ],
                        [
                            'icon' => 'reports',
                            'title' => 'Performance & Analytics',
                            'body' => 'These help summarize app usage patterns so pages, reports, and dashboard workflows can be improved.',
                        ],
                    ],
                ],
                [
                    'title' => 'Third-party cookies',
                    'body' => 'MotoX may use trusted services for authentication, hosting, or security. These providers may use their own cookies to make those services work correctly.',
                ],
                [
                    'title' => 'What are your choices regarding cookies',
                    'body' => 'You can clear cookies or browser storage from your browser settings. If you delete or refuse cookies, some MotoX features may reset, and you may need to sign in again.',
                    'links' => [
                        ['label' => 'Chrome Cookie Settings', 'url' => 'https://support.google.com/chrome/answer/95647'],
                        ['label' => 'Safari Cookie Settings', 'url' => 'https://support.apple.com/guide/safari/manage-cookies-and-website-data-sfri11471/mac'],
                        ['label' => 'Firefox Cookie Settings', 'url' => 'https://support.mozilla.org/kb/enhanced-tracking-protection-firefox-desktop'],
                        ['label' => 'Edge Cookie Settings', 'url' => 'https://support.microsoft.com/microsoft-edge/delete-cookies-in-microsoft-edge-63947406-40ac-c3b8-57b9-2a946a29ae09'],
                    ],
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
            'support' => false,
            'icon' => 'support',
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
                [
                    'title' => 'How to use Help Me',
                    'body' => 'Find the page, check the record, then take action. Use the sidebar to move between pages. Use search and date filters to narrow records before editing or downloading receipts.',
                ],
                [
                    'title' => 'Quick Help Workflow',
                    'body' => 'Follow these steps when something is unclear or blocked.',
                    'items' => [
                        ['label' => 'Step 1', 'text' => 'Pick the page you are working on: Dashboard, Inventory, Customers, Job Orders, Billing, Reports, Logs, or Settings.'],
                        ['label' => 'Step 2', 'text' => 'Use search and date filters before editing records so you are working on the correct customer, invoice, job order, or part.'],
                        ['label' => 'Step 3', 'text' => 'If something still looks wrong, check the current page, exact record name, date filter, status, and latest Logs.'],
                    ],
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
            'updatedAt' => null,
            'icon' => 'file',
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
