@extends('layouts.app')

@section('content')
    <section class="space-y-6" data-billing-metrics-url="{{ $billingMetricsUrl }}">
        <div>
            <h1 class="text-4xl font-black tracking-tight text-slate-900">{{ $heading }}</h1>
            <p class="mt-2 text-sm text-slate-500">{{ $subheading }}</p>
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
                <table class="soft-table min-w-[980px]">
                    <thead>
                        <tr class="table-heading">
                            <th>Invoice #</th>
                            <th>Job Order</th>
                            <th>Customer</th>
                            <th>Vehicle</th>
                            <th>Status</th>
                            <th>Amount</th>
                            <th>Updated</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($invoices as $invoice)
                            <tr>
                                <td class="font-semibold text-slate-900">{{ $invoice['invoice_number'] }}</td>
                                <td>{{ $invoice['order_number'] }}</td>
                                <td>{{ $invoice['customer'] }}</td>
                                <td>{{ $invoice['vehicle'] }}</td>
                                <td><x-badge :tone="$invoice['tone']">{{ $invoice['status'] }}</x-badge></td>
                                <td class="font-semibold text-slate-900">PHP {{ number_format($invoice['amount'], 2) }}</td>
                                <td>{{ $invoice['updated_at']->timezone('Asia/Manila')->format('M d, Y h:i A') }} PHT</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-10 text-center text-sm text-slate-500">No billable job orders yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </section>
@endsection
