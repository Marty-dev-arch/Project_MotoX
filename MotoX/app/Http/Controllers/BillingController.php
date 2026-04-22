<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\BuildsPageData;
use App\Models\JobOrder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BillingController extends Controller
{
    use BuildsPageData;

    public function index(Request $request): View
    {
        $shop = $request->user()->shop;
        abort_if($shop === null, 403, 'Shop profile not found.');

        $orders = JobOrder::query()
            ->forShop($shop)
            ->with('customer')
            ->where('estimated_cost', '>', 0)
            ->orderByDesc('updated_at')
            ->get();

        $invoices = $orders->map(function (JobOrder $order): array {
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
                'vehicle' => $order->vehicle,
                'status' => $status,
                'tone' => $tone,
                'amount' => (float) $order->estimated_cost,
                'updated_at' => $order->updated_at,
            ];
        });

        $totalBilled = $invoices->sum('amount');
        $paidAmount = $invoices->where('status', 'Paid')->sum('amount');
        $pendingAmount = $invoices->where('status', 'Pending')->sum('amount');

        return view('pages.billing', $this->buildPageData('billing', [
            'heading' => 'Billing',
            'subheading' => 'Invoice-ready totals generated from real job order records.',
            'searchPlaceholder' => 'Search invoice, order, customer...',
            'invoices' => $invoices,
            'stats' => [
                'total_billed' => $totalBilled,
                'paid_amount' => $paidAmount,
                'pending_amount' => $pendingAmount,
                'total_invoices' => $invoices->count(),
            ],
        ]));
    }
}

