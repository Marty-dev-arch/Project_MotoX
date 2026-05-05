<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\BuildsPageData;
use App\Models\JobOrder;
use App\Models\Shop;
use App\Support\DatePeriods;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\View\View;

class BillingController extends Controller
{
    use BuildsPageData;

    public function index(Request $request): View
    {
        $shop = $request->user()->workspaceShop();
        abort_if($shop === null, 403, 'Shop profile not found.');

        $period = $this->resolvePeriod($request);
        $snapshot = $this->buildBillingSnapshot($shop, $period);

return view('pages.billing', $this->buildPageData('billing', [
            'heading' => 'Billing',
            'subheading' => 'Invoice-ready totals generated from real job order records.',
            'showHeaderSearch' => false,
            'invoices' => $snapshot['invoices'],
            'stats' => $snapshot['stats'],
            'activePeriod' => $period,
            'billingMetricsUrl' => route('billing.metrics'),
            'billingExportUrl' => route('billing.export'),
        ]));
    }

    public function metrics(Request $request): JsonResponse
    {
        $shop = $request->user()->workspaceShop();
        abort_if($shop === null, 403, 'Shop profile not found.');

        $period = $this->resolvePeriod($request);
        $snapshot = $this->buildBillingSnapshot($shop, $period);

        return response()->json([
            'stats' => $snapshot['stats'],
            'invoices' => $snapshot['invoices']->map(fn (array $invoice): array => $this->invoicePayload($invoice))->values(),
            'period' => $period,
            'updated_at' => now('Asia/Manila')->toIso8601String(),
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        $shop = $request->user()->workspaceShop();
        abort_if($shop === null, 403, 'Shop profile not found.');

        $period = $this->resolvePeriod($request);
        $snapshot = $this->buildBillingSnapshot($shop, $period);
        $filename = 'motox-billing-'.DatePeriods::filenameToken($period).'-'.now('Asia/Manila')->format('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($snapshot, $period): void {
            $output = fopen('php://output', 'w');

            fputcsv($output, ['MotoX Billing Export']);
            fputcsv($output, ['Period', DatePeriods::label($period)]);
            fputcsv($output, ['Generated At', now('Asia/Manila')->format('Y-m-d H:i:s').' PHT']);
            fputcsv($output, []);
            fputcsv($output, ['Totals']);
            fputcsv($output, ['Total Billed', number_format((float) $snapshot['stats']['total_billed'], 2, '.', '')]);
            fputcsv($output, ['Paid Amount', number_format((float) $snapshot['stats']['paid_amount'], 2, '.', '')]);
            fputcsv($output, ['Pending Amount', number_format((float) $snapshot['stats']['pending_amount'], 2, '.', '')]);
            fputcsv($output, ['Total Invoices', (int) $snapshot['stats']['total_invoices']]);
            fputcsv($output, []);
            fputcsv($output, ['Invoice Number', 'Job Order', 'Customer', 'Vehicle', 'Status', 'Amount', 'Updated At']);

            foreach ($snapshot['invoices'] as $invoice) {
                fputcsv($output, [
                    $invoice['invoice_number'],
                    $invoice['order_number'],
                    $invoice['customer'],
                    $invoice['vehicle'],
                    $invoice['status'],
                    number_format((float) $invoice['amount'], 2, '.', ''),
                    $invoice['updated_at']?->timezone('Asia/Manila')->format('Y-m-d H:i:s').' PHT',
                ]);
            }

            fclose($output);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function buildBillingSnapshot(Shop $shop, string $period): array
    {
        $orders = JobOrder::query()
            ->forShop($shop)
            ->with('customer')
            ->where('estimated_cost', '>', 0)
            ->when(DatePeriods::bounds($period), function (Builder $query, array $bounds): void {
                $query->whereBetween('updated_at', $bounds);
            })
            ->orderByDesc('updated_at')
            ->get();

        $invoices = $orders->map(function (JobOrder $order) use ($shop): array {
            $status = match ($order->status) {
                JobOrder::STATUS_COMPLETED => 'Paid',
                JobOrder::STATUS_CANCELLED => 'Voided',
                default => 'Pending',
            };

            $tone = match ($status) {
                'Paid' => 'success',
                'Voided' => 'danger',
                default => 'warning',
            };

            return [
                'invoice_number' => 'INV-'.str_pad((string) $order->id, 5, '0', STR_PAD_LEFT),
                'order_number' => $order->order_number,
                'customer' => $order->customer?->name ?? 'Walk-in Customer',
                'customer_phone' => $order->customer?->phone ?? '',
                'customer_email' => $order->customer?->email ?? '',
                'vehicle' => $order->vehicle,
                'status' => $status,
                'tone' => $tone,
                'amount' => (float) $order->estimated_cost,
                'updated_at' => $order->updated_at,
                'shop_name' => $shop->name,
            ];
        });

        $totalBilled = (float) $invoices->sum('amount');
        $paidAmount = (float) $invoices->where('status', 'Paid')->sum('amount');
        $pendingAmount = (float) $invoices->where('status', 'Pending')->sum('amount');

        return [
            'invoices' => $invoices,
            'stats' => [
                'total_billed' => $totalBilled,
                'paid_amount' => $paidAmount,
                'pending_amount' => $pendingAmount,
                'total_invoices' => $invoices->count(),
            ],
        ];
    }

    private function resolvePeriod(Request $request): string
    {
        return DatePeriods::normalize($request->query('period', 'all'));
    }

    private function invoicePayload(array $invoice): array
    {
        return [
            'invoice_number' => $invoice['invoice_number'],
            'order_number' => $invoice['order_number'],
            'customer' => $invoice['customer'],
            'customer_phone' => $invoice['customer_phone'] ?? '',
            'customer_email' => $invoice['customer_email'] ?? '',
            'vehicle' => $invoice['vehicle'],
            'status' => $invoice['status'],
            'tone' => $invoice['tone'],
            'amount' => (float) $invoice['amount'],
            'amount_display' => 'PHP '.number_format((float) $invoice['amount'], 2),
            'updated_at' => $invoice['updated_at']?->toIso8601String(),
            'updated_display' => $invoice['updated_at']?->timezone('Asia/Manila')->format('M d, Y h:i A').' PHT',
            'receipt_updated_display' => $invoice['updated_at']?->timezone('Asia/Manila')->format('F j, Y, l h:i A').' PHT',
            'shop_name' => $invoice['shop_name'] ?? 'MotoX',
        ];
    }
}
