@extends('layouts.app')

@section('content')
    <section class="space-y-6" data-billing-metrics-url="{{ $billingMetricsUrl }}" data-billing-export-url="{{ $billingExportUrl }}" data-live-table="billing">
        <div class="flex flex-wrap items-start justify-between gap-5">
            <div>
                <h1 class="text-4xl font-black tracking-tight text-slate-900">{{ $heading }}</h1>
                <p class="mt-2 text-sm text-slate-500">{{ $subheading }}</p>
            </div>

            <div class="page-filter-toolbar">
                <label class="page-search-shell">
                    <x-icon name="search" class="h-4 w-4 text-slate-400" />
                    <input
                        type="text"
                        id="billing-search-input"
                        placeholder="Search invoice, customer, status..."
                    >
                </label>

                <div class="relative">
                    <button type="button" class="page-filter-button" data-date-filter-trigger="billing">
                        <x-icon name="calendar" class="h-4 w-4" />
                        <span>Filter by Date</span>
                        <x-icon name="chevron-down" class="h-4 w-4" />
                    </button>
                    <div class="page-filter-menu hidden" data-date-filter-menu="billing">
                        <button type="button" data-date-filter="all">General</button>
                        <button type="button" data-date-filter="daily">Daily</button>
                        <button type="button" data-date-filter="weekly">Weekly</button>
                        <button type="button" data-date-filter="monthly">Monthly</button>
                        <button type="button" data-date-filter="yearly">Yearly</button>
                    </div>
                </div>

            </div>
        </div>

        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <article class="panel-card p-5">
                <p class="muted-label">Total Billed</p>
                <p class="mt-2 text-4xl font-black tracking-tight text-slate-900" data-billing-kpi="total_billed">PHP {{ number_format($stats['total_billed'], 2) }}</p>
            </article>
            <article class="panel-card p-5">
                <p class="muted-label">Paid Amount</p>
                <p class="mt-2 text-4xl font-black tracking-tight text-emerald-600" data-billing-kpi="paid_amount">PHP {{ number_format($stats['paid_amount'], 2) }}</p>
            </article>
            <article class="panel-card p-5">
                <p class="muted-label">Pending Amount</p>
                <p class="mt-2 text-4xl font-black tracking-tight text-amber-600" data-billing-kpi="pending_amount">PHP {{ number_format($stats['pending_amount'], 2) }}</p>
            </article>
            <article class="panel-card p-5">
                <p class="muted-label">Total Invoices</p>
                <p class="mt-2 text-4xl font-black tracking-tight text-slate-900" data-billing-kpi="total_invoices">{{ number_format($stats['total_invoices']) }}</p>
            </article>
        </div>

        <section class="table-shell">
            <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
                <div>
                    <h2 class="text-2xl font-bold tracking-tight text-slate-900">Invoices from Job Orders</h2>
                    <p class="text-sm text-slate-500">Generated directly from real job order totals.</p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="soft-table w-full min-w-[980px]">
                    <thead>
                        <tr class="table-heading">
                            <th>Invoice #</th>
                            <th>Job Order</th>
                            <th>Customer</th>
                            <th>Vehicle</th>
                            <th>Status</th>
                            <th>Amount</th>
                            <th>Updated</th>
                            <th data-print-skip>Receipt</th>
                        </tr>
                    </thead>
                    <tbody data-billing-rows>
                        @forelse ($invoices as $invoice)
                            <tr
                                data-billing-row
                                data-item-date="{{ $invoice['updated_at']->toIso8601String() }}"
                                data-search="{{ strtolower($invoice['invoice_number'].' '.$invoice['order_number'].' '.$invoice['customer'].' '.$invoice['vehicle'].' '.$invoice['status']) }}"
                            >
                                <td class="font-semibold text-slate-900">{{ $invoice['invoice_number'] }}</td>
                                <td>{{ $invoice['order_number'] }}</td>
                                <td>{{ $invoice['customer'] }}</td>
                                <td>{{ $invoice['vehicle'] }}</td>
                                <td><x-badge :tone="$invoice['tone']">{{ $invoice['status'] }}</x-badge></td>
                                <td class="font-semibold text-slate-900">PHP {{ number_format($invoice['amount'], 2) }}</td>
                                <td>{{ $invoice['updated_at']->timezone('Asia/Manila')->format('M d, Y h:i A') }} PHT</td>
                                <td data-print-skip>
                                    <button
                                        type="button"
                                        class="icon-button h-9 w-9"
                                        title="Print receipt"
                                        aria-label="Print receipt for {{ $invoice['invoice_number'] }}"
                                        data-print-receipt
                                        data-receipt-invoice="{{ $invoice['invoice_number'] }}"
                                        data-receipt-order="{{ $invoice['order_number'] }}"
                                        data-receipt-customer="{{ $invoice['customer'] }}"
                                        data-receipt-phone="{{ $invoice['customer_phone'] ?? '' }}"
                                        data-receipt-email="{{ $invoice['customer_email'] ?? '' }}"
                                        data-receipt-vehicle="{{ $invoice['vehicle'] }}"
                                        data-receipt-status="{{ $invoice['status'] }}"
                                        data-receipt-amount="PHP {{ number_format($invoice['amount'], 2) }}"
                                        data-receipt-updated="{{ $invoice['updated_at']->timezone('Asia/Manila')->format('F j, Y, l h:i A') }} PHT"
                                        data-receipt-shop="{{ $invoice['shop_name'] ?? 'MotoX' }}"
                                    >
                                        <x-icon name="printer" class="h-4 w-4" />
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr data-empty-row>
                                <td colspan="8" class="py-10 text-center text-sm text-slate-500">No billable job orders yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </section>
@endsection
