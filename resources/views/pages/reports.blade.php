@extends('layouts.app')

@section('content')
    <section class="space-y-6" data-reports-metrics-url="{{ $reportsMetricsUrl }}" data-reports-export-url="{{ $reportsExportUrl }}" data-live-table="reports" data-report-export-root>
        <div class="flex flex-wrap items-start justify-between gap-5">
            <div>
                <h1 class="text-4xl font-black tracking-tight text-slate-900">{{ $heading }}</h1>
                <p class="mt-2 text-sm text-slate-500">{{ $subheading }}</p>
            </div>

            <div class="page-filter-toolbar" data-report-export-exclude>
                <label class="page-search-shell">
                    <x-icon name="search" class="h-4 w-4 text-slate-400" />
                    <input
                        type="text"
                        id="reports-search-input"
                        placeholder="Search customer, jobs, billing..."
                    >
                </label>

                <div class="relative">
                    <button type="button" class="page-filter-button" data-date-filter-trigger="reports">
                        <x-icon name="calendar" class="h-4 w-4" />
                        <span>Filter by Date</span>
                        <x-icon name="chevron-down" class="h-4 w-4" />
                    </button>
                    <div class="page-filter-menu hidden" data-date-filter-menu="reports">
                        <button type="button" data-date-filter="all">General</button>
                        <button type="button" data-date-filter="daily">Daily</button>
                        <button type="button" data-date-filter="weekly">Weekly</button>
                        <button type="button" data-date-filter="monthly">Monthly</button>
                        <button type="button" data-date-filter="yearly">Yearly</button>
                    </div>
                </div>

                <div class="relative">
                    <button type="button" class="page-export-button" data-report-download-trigger>
                        <x-icon name="export" class="h-4 w-4" />
                        <span>Export</span>
                        <x-icon name="chevron-down" class="h-4 w-4" />
                    </button>
                    <div class="page-filter-menu hidden" data-report-download-menu>
                        <button type="button" data-export-csv="reports">CSV File</button>
                        <button type="button" data-download-report-png>Chart PNG</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid gap-4 md:grid-cols-2 2xl:grid-cols-4">
            <article class="panel-card p-5">
                <p class="muted-label">{{ $activePeriod === 'all' ? 'All Revenue' : ucfirst($activePeriod).' Revenue' }}</p>
                <p class="mt-2 text-4xl font-black tracking-tight text-slate-900" data-report-kpi="month_revenue">PHP {{ number_format($stats['month_revenue'], 2) }}</p>
            </article>
            <article class="panel-card p-5">
                <p class="muted-label">{{ $activePeriod === 'all' ? 'Live Growth' : 'Vs Previous Period' }}</p>
                <p class="mt-2 text-4xl font-black tracking-tight {{ $stats['growth_rate'] >= 0 ? 'text-emerald-600' : 'text-rose-600' }}" data-report-kpi="growth_rate">
                    {{ $stats['growth_rate'] >= 0 ? '+' : '' }}{{ number_format($stats['growth_rate'], 1) }}%
                </p>
            </article>
            <article class="panel-card p-5">
                <p class="muted-label">Closed Jobs</p>
                <p class="mt-2 text-4xl font-black tracking-tight text-slate-900" data-report-kpi="jobs_closed">{{ number_format($stats['jobs_closed']) }}</p>
            </article>
            <article class="panel-card p-5">
                <p class="muted-label">Inventory Value</p>
                <p class="mt-2 text-4xl font-black tracking-tight text-slate-900" data-report-kpi="inventory_value">PHP {{ number_format($stats['inventory_value'], 2) }}</p>
            </article>
        </div>

        <div class="grid items-start gap-6 xl:grid-cols-[1fr_0.9fr]">
            <section class="panel-card p-5 sm:p-6">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <h2 class="text-2xl font-bold text-slate-900">Graphical Chart Revenue Flow</h2>
                        <p class="mt-1 text-sm text-slate-500">movement monitor based on completed job orders.</p>
                    </div>

                    <label class="report-range-select" aria-label="Choose month range">
                        <x-icon name="calendar" class="h-4 w-4" />
                        <select data-report-range-select>
                            <option value="jan-jun">Jan-Jun</option>
                            <option value="jul-dec">Jul-Dec</option>
                        </select>
                        <x-icon name="chevron-down" class="h-4 w-4" />
                    </label>
                </div>

                <div
                    class="mt-6"
                    data-chart="report-revenue"
                    data-series='@json($monthlyTrend)'
                ></div>

                <div class="report-trend-summary mt-4">
                    <article class="report-trend-pill">
                        <p class="report-trend-pill-label">Latest Month</p>
                        <p class="report-trend-pill-value" data-report-summary="latest">{{ $monthlyTrendSummary['latest'] }}</p>
                    </article>
                    <article class="report-trend-pill">
                        <p class="report-trend-pill-label">12-Month Average</p>
                        <p class="report-trend-pill-value" data-report-summary="average">{{ $monthlyTrendSummary['average'] }}</p>
                    </article>
                    <article class="report-trend-pill">
                        <p class="report-trend-pill-label">Peak Month</p>
                        <p class="report-trend-pill-value" data-report-summary="peak">{{ $monthlyTrendSummary['peak'] }}</p>
                    </article>
                </div>
            </section>

            <section class="panel-card report-status-panel p-5 sm:p-6">
                <div>
                    <h2 class="text-2xl font-bold text-slate-900">Status Breakdown</h2>
                    <p class="mt-1 text-sm text-slate-500">Current job order distribution.</p>
                </div>

                <div class="report-status-list mt-5" data-report-status-breakdown>
                    @foreach ($statusBreakdown as $row)
                        @php
                            $statusKey = $row['key'] ?? strtolower(str_replace(' ', '_', $row['status']));
                            $tone = match ($statusKey) {
                                'completed' => 'success',
                                'in_progress' => 'accent',
                                'cancelled' => 'danger',
                                default => 'warning',
                            };
                        @endphp
                        <article
                            class="report-status-card"
                            data-report-status="{{ $statusKey }}"
                        >
                            <div class="report-status-card-head">
                                <div>
                                    <p class="font-semibold text-slate-900">{{ $row['status'] }}</p>
                                </div>
                                <x-badge :tone="$tone" data-report-status-count>{{ $row['count'] }}</x-badge>
                            </div>

                            <div class="report-status-orders" data-report-status-orders>
                                @forelse (($row['orders'] ?? []) as $order)
                                    <div class="report-status-order">
                                        @if (! empty($order['profile_photo_url']))
                                            <img src="{{ $order['profile_photo_url'] }}" alt="{{ $order['customer'] }} profile" class="report-status-avatar" loading="lazy" decoding="async">
                                        @else
                                            <span class="report-status-avatar report-status-avatar-fallback">{{ $order['initials'] ?? 'WI' }}</span>
                                        @endif
                                        <div class="min-w-0">
                                            <p class="truncate text-sm font-bold text-slate-900">{{ $order['customer'] }}</p>
                                            <p class="truncate text-xs text-slate-500">{{ $order['order_number'] }} - {{ $order['vehicle'] ?: 'No vehicle' }}</p>
                                        </div>
                                        <span class="report-status-date">{{ $order['date_display'] ?? '-' }}</span>
                                    </div>
                                @empty
                                    <p class="report-status-empty">No job orders in this status.</p>
                                @endforelse
                            </div>
                        </article>
                    @endforeach
                </div>
            </section>
        </div>

        <section class="table-shell">
            <div class="border-b border-slate-100 px-5 py-4">
                <h2 class="text-2xl font-bold tracking-tight text-slate-900">Top Customers by Billing</h2>
                <p class="text-sm text-slate-500">movement ranking from recorded job orders.</p>
            </div>

            <div class="overflow-x-auto">
                <table class="soft-table w-full min-w-[760px]">
                    <thead>
                        <tr class="table-heading">
                            <th>Customer</th>
                            <th>Total Jobs</th>
                            <th>Total Billed</th>
                            <th>Latest Date</th>
                        </tr>
                    </thead>
                    <tbody data-reports-rows>
                        @forelse ($topCustomers as $row)
                            <tr
                                data-reports-row
                                data-item-date="{{ optional($row['latest_job_at'] ?? null)->toIso8601String() }}"
                                data-search="{{ strtolower($row['name'].' '.$row['jobs'].' '.$row['billed']) }}"
                            >
                                <td class="font-semibold text-slate-900">
                                    <div class="flex items-center gap-3">
                                        @if (! empty($row['profile_photo_url']))
                                            <img src="{{ $row['profile_photo_url'] }}" alt="{{ $row['name'] }} profile" class="h-10 w-10 rounded-full object-cover">
                                        @else
                                            <span class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-slate-900 text-xs font-black text-white">
                                                {{ strtoupper(collect(explode(' ', $row['name']))->filter()->map(fn (string $part): string => mb_substr($part, 0, 1))->take(2)->implode('') ?: 'CU') }}
                                            </span>
                                        @endif
                                        <span>{{ $row['name'] }}</span>
                                    </div>
                                </td>
                                <td>{{ $row['jobs'] }}</td>
                                <td class="font-semibold text-slate-900">{{ $row['billed'] }}</td>
                                <td>{{ $row['latest_display'] ?? optional($row['latest_job_at'] ?? null)->timezone('Asia/Manila')->format('M d, Y') ?? now('Asia/Manila')->format('M d, Y') }}</td>
                            </tr>
                        @empty
                            <tr data-empty-row>
                                <td colspan="4" class="py-10 text-center text-sm text-slate-500">No customer billing data available yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </section>
@endsection
