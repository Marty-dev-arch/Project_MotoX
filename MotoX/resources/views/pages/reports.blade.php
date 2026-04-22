@extends('layouts.app')

@section('content')
    <section class="space-y-6">
        <div>
            <h1 class="text-4xl font-black tracking-tight text-slate-900">{{ $heading }}</h1>
            <p class="mt-2 text-sm text-slate-500">{{ $subheading }}</p>
        </div>

        <div class="grid gap-4 md:grid-cols-2 2xl:grid-cols-4">
            <article class="panel-card p-5">
                <p class="muted-label">Month Revenue</p>
                <p class="mt-2 text-4xl font-black tracking-tight text-slate-900">PHP {{ number_format($stats['month_revenue'], 2) }}</p>
            </article>
            <article class="panel-card p-5">
                <p class="muted-label">Vs Last Month</p>
                <p class="mt-2 text-4xl font-black tracking-tight {{ $stats['growth_rate'] >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                    {{ $stats['growth_rate'] >= 0 ? '+' : '' }}{{ number_format($stats['growth_rate'], 1) }}%
                </p>
            </article>
            <article class="panel-card p-5">
                <p class="muted-label">Closed Jobs</p>
                <p class="mt-2 text-4xl font-black tracking-tight text-slate-900">{{ number_format($stats['jobs_closed']) }}</p>
            </article>
            <article class="panel-card p-5">
                <p class="muted-label">Inventory Value</p>
                <p class="mt-2 text-4xl font-black tracking-tight text-slate-900">PHP {{ number_format($stats['inventory_value'], 2) }}</p>
            </article>
        </div>

        <div class="grid gap-6 xl:grid-cols-[1fr_0.9fr]">
            <section class="panel-card p-5 sm:p-6">
                <div>
                    <h2 class="text-2xl font-bold text-slate-900">Monthly Revenue Trend</h2>
                    <p class="mt-1 text-sm text-slate-500">Last 6 months based on completed job orders.</p>
                </div>

                <div class="mt-6 grid grid-cols-2 gap-3 sm:grid-cols-3 xl:grid-cols-6">
                    @foreach ($monthlyBars as $index => $bar)
                        <article class="rounded-2xl border border-slate-100 bg-slate-50/80 p-4">
                            <div class="flex h-44 items-end">
                                <div
                                    @class([
                                        'w-full rounded-t-2xl transition-all duration-300',
                                        'bg-gradient-to-t from-brand-600 to-brand-400' => $index === $monthlyBars->count() - 1,
                                        'bg-slate-200' => $index !== $monthlyBars->count() - 1,
                                    ])
                                    style="height: {{ $bar['height'] }}%;"
                                ></div>
                            </div>
                            <div class="mt-3 text-center">
                                <p class="text-sm font-semibold text-slate-700">{{ $bar['label'] }}</p>
                                <p class="mt-1 text-xs text-slate-500">{{ $bar['display'] }}</p>
                            </div>
                        </article>
                    @endforeach
                </div>
            </section>

            <section class="panel-card p-5 sm:p-6">
                <div>
                    <h2 class="text-2xl font-bold text-slate-900">Status Breakdown</h2>
                    <p class="mt-1 text-sm text-slate-500">Current job order distribution.</p>
                </div>

                <div class="mt-6 space-y-3">
                    @foreach ($statusBreakdown as $row)
                        @php
                            $tone = match (strtolower(str_replace(' ', '_', $row['status']))) {
                                'completed' => 'success',
                                'in_progress' => 'accent',
                                'cancelled' => 'danger',
                                default => 'warning',
                            };
                        @endphp
                        <article class="flex items-center justify-between rounded-2xl border border-slate-100 bg-white px-4 py-3">
                            <p class="font-semibold text-slate-900">{{ $row['status'] }}</p>
                            <x-badge :tone="$tone">{{ $row['count'] }}</x-badge>
                        </article>
                    @endforeach
                </div>
            </section>
        </div>

        <section class="table-shell">
            <div class="border-b border-slate-100 px-5 py-4">
                <h2 class="text-2xl font-bold tracking-tight text-slate-900">Top Customers by Billing</h2>
                <p class="text-sm text-slate-500">Live ranking from recorded job orders.</p>
            </div>

            <div class="overflow-x-auto">
                <table class="soft-table min-w-[760px]">
                    <thead>
                        <tr class="table-heading">
                            <th>Customer</th>
                            <th>Total Jobs</th>
                            <th>Total Billed</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($topCustomers as $row)
                            <tr>
                                <td class="font-semibold text-slate-900">{{ $row['name'] }}</td>
                                <td>{{ $row['jobs'] }}</td>
                                <td class="font-semibold text-slate-900">{{ $row['billed'] }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="py-10 text-center text-sm text-slate-500">No customer billing data available yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </section>
@endsection

