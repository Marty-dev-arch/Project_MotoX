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
                        <span data-i18n="Filter by Date">Filter by Date</span>
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
                <table class="soft-table w-full min-w-[1040px]">
                    <thead>
                        <tr class="table-heading">
                            <th>Invoice #</th>
                            <th>Job Order #</th>
                            <th>Customer</th>
                            <th>Vehicle</th>
                            <th>Status</th>
                            <th>Amount</th>
                            <th>Updated</th>
                            <th data-print-skip>Actions</th>
                        </tr>
                    </thead>
                    <tbody data-billing-rows>
                        @forelse ($invoices as $invoice)
                            <tr
                                data-billing-row
                                data-item-date="{{ $invoice['updated_at']->toIso8601String() }}"
                                data-search="{{ strtolower($invoice['invoice_number'].' '.$invoice['order_number'].' '.$invoice['customer'].' '.$invoice['vehicle'].' '.$invoice['status']) }}"
                                data-receipt-key="{{ $invoice['invoice_number'] }}"
                                data-receipt-invoice="{{ $invoice['invoice_number'] }}"
                                data-receipt-order="{{ $invoice['order_number'] }}"
                                data-receipt-customer="{{ $invoice['customer'] }}"
                                data-receipt-phone="{{ $invoice['customer_phone'] ?? '' }}"
                                data-receipt-email="{{ $invoice['customer_email'] ?? '' }}"
                                data-receipt-photo="{{ $invoice['customer_photo_url'] ?? '' }}"
                                data-receipt-vehicle="{{ $invoice['vehicle'] }}"
                                data-receipt-status="{{ $invoice['status'] }}"
                                data-receipt-amount="PHP {{ number_format($invoice['amount'], 2) }}"
                                data-receipt-amount-value="{{ number_format($invoice['amount'], 2, '.', '') }}"
                                data-receipt-updated="{{ $invoice['updated_at']->timezone('Asia/Manila')->format('F j, Y, l h:i A') }} PHT"
                                data-receipt-shop="{{ $invoice['shop_name'] ?? 'MotoX' }}"
                            >
                                <td class="font-semibold text-slate-900">{{ $invoice['invoice_number'] }}</td>
                                <td>{{ $invoice['order_number'] }}</td>
                                <td>
                                    <div class="flex items-center gap-3">
                                        @if (! empty($invoice['customer_photo_url']))
                                            <img src="{{ $invoice['customer_photo_url'] }}" alt="{{ $invoice['customer'] }} profile" class="h-10 w-10 rounded-full object-cover">
                                        @else
                                            <span class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-slate-900 text-xs font-black text-white">
                                                {{ strtoupper(collect(explode(' ', $invoice['customer']))->filter()->map(fn (string $part): string => mb_substr($part, 0, 1))->take(2)->implode('') ?: 'CU') }}
                                            </span>
                                        @endif
                                        <span>{{ $invoice['customer'] }}</span>
                                    </div>
                                </td>
                                <td>{{ $invoice['vehicle'] }}</td>
                                <td><x-badge :tone="$invoice['tone']">{{ $invoice['status'] }}</x-badge></td>
                                <td class="font-semibold text-slate-900">PHP {{ number_format($invoice['amount'], 2) }}</td>
                                <td>{{ $invoice['updated_at']->timezone('Asia/Manila')->format('M d, Y h:i A') }} PHT</td>
                                <td data-print-skip>
                                    <div class="billing-action-buttons">
                                        <button type="button" class="receipt-action-button receipt-action-button-primary" data-download-receipt aria-label="Download receipt for {{ $invoice['customer'] }}">
                                            <x-icon name="download" class="h-4 w-4" />
                                            <span>Receipt</span>
                                        </button>
                                    </div>
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
