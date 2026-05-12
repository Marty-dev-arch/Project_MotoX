@extends('layouts.app')

@section('content')
    <section
        class="space-y-8"
        data-dashboard-metrics-url="{{ $dashboardMetricsUrl }}"
        data-dashboard-months="{{ $dashboardTrendMonths }}"
        data-dashboard-range="{{ $dashboardTrendRange ?? 'jan-jun' }}"
    >
        @if (session('status'))
            <div class="auth-alert auth-alert-{{ session('status_tone', 'success') }}">
                <p class="font-semibold">{{ session('status') }}</p>
            </div>
        @endif

        <div class="flex flex-wrap items-start justify-between gap-5">
            <div class="space-y-2">
                <h1 class="text-4xl font-black tracking-tight text-slate-900">{{ $heading }}</h1>
                <p class="text-base text-slate-500">{{ $subheading }}</p>
            </div>

            <div class="page-filter-toolbar">
                <label class="page-search-shell">
                    <x-icon name="search" class="h-4 w-4 text-slate-400" />
                    <input
                        type="text"
                        id="dashboard-search-input"
                        placeholder="Search part, sku, category..."
                    >
                </label>

                <div class="relative">
                    <button type="button" class="page-filter-button" data-date-filter-trigger="dashboard">
                        <x-icon name="calendar" class="h-4 w-4" />
                        <span>Filter by Date</span>
                        <x-icon name="chevron-down" class="h-4 w-4" />
                    </button>
                    <div class="page-filter-menu hidden" data-date-filter-menu="dashboard">
                        <button type="button" data-date-filter="all">All Time</button>
                        <button type="button" data-date-filter="today">Today</button>
                        <button type="button" data-date-filter="week">This Week</button>
                        <button type="button" data-date-filter="month">This Month</button>
                        <button type="button" data-date-filter="year">This Year</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid gap-4 md:grid-cols-2 2xl:grid-cols-4">
            @foreach ($stats as $index => $stat)
                <article class="panel-card p-5">
                    <div class="flex items-start justify-between gap-4">
                        <span class="icon-chip bg-slate-100 text-brand-600">
                            <x-icon :name="$stat['icon']" class="h-5 w-5" />
                        </span>
                    </div>
                    <div class="mt-6">
                        <p class="muted-label">{{ $stat['label'] }}</p>
                        <p class="mt-2 text-4xl font-black tracking-tight text-slate-900" data-kpi="{{ ['total_skus', 'low_stock', 'out_of_stock', 'inventory_value'][$index] }}">
                            {{ $stat['value'] }}
                        </p>
                        <p class="mt-2 text-sm text-slate-500">{{ $stat['caption'] }}</p>
                    </div>
                </article>
            @endforeach
        </div>

        <section class="panel-card p-5 sm:p-6">
            <div class="flex flex-wrap items-end justify-between gap-3">
                <div>
                    <h2 class="text-2xl font-bold text-slate-900">Revenue Snapshot</h2>
                    <p class="mt-1 text-sm text-slate-500">Actual completed job-order revenue by operating monitoring.</p>
                </div>
                <a href="{{ route('reports') }}" class="text-sm font-semibold text-brand-600 transition hover:text-brand-700">Open Reports</a>
            </div>
            <div class="mt-5 grid gap-4 md:grid-cols-2 xl:grid-cols-4" data-dashboard-revenue-list>
                @foreach ($revenueStats as $row)
                    <article class="dashboard-revenue-card rounded-2xl border border-slate-100 bg-slate-50/80 px-4 py-4" data-dashboard-revenue-card="{{ $row['period'] }}">
                        <p class="muted-label">{{ $row['label'] }}</p>
                        <p class="mt-2 text-2xl font-black tracking-tight text-slate-900" data-dashboard-revenue-value>{{ $row['value'] }}</p>
                        <p class="mt-1 text-xs text-slate-500">{{ $row['caption'] }}</p>
                    </article>
                @endforeach
            </div>
        </section>

        <div>
            <section class="panel-card budget-card p-5 sm:p-6">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <h2 class="text-2xl font-bold text-slate-900">Graphical Chart Stock Flow</h2>
                        <div class="budget-legend mt-2">
                            <span class="budget-legend-item"><i class="budget-legend-dot budget-legend-dot-in"></i>Stock In</span>
                            <span class="budget-legend-item"><i class="budget-legend-dot budget-legend-dot-out"></i>Stock Out</span>
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center gap-3">
                        <label class="report-range-select" aria-label="Choose stock flow month range">
                            <x-icon name="calendar" class="h-4 w-4" />
                            <select data-dashboard-range-select>
                                @foreach ($dashboardTrendRanges as $range)
                                    <option value="{{ $range['value'] }}" @selected($range['active'])>{{ $range['label'] }}</option>
                                @endforeach
                            </select>
                            <x-icon name="chevron-down" class="h-4 w-4" />
                        </label>
                    </div>
                </div>

                <div
                    class="mt-6"
                    data-chart="movement"
                    data-trend='@json($trend)'
                ></div>
            </section>
        </div>

<div class="grid gap-6 xl:grid-cols-[1fr_1fr]">
            <section class="panel-card p-5 sm:p-6 dashboard-list-panel">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <h2 class="text-2xl font-bold text-slate-900">Low Stock Parts</h2>
                        <p class="mt-1 text-sm text-slate-500">Items currently below minimum stock.</p>
                    </div>
                    <a href="{{ route('inventory') }}" class="text-sm font-semibold text-brand-600 transition hover:text-brand-700">Open Inventory</a>
                </div>

                <div class="mt-5 space-y-3" data-dashboard-results="low-stock">
                    @forelse ($lowStockParts as $part)
                        <article class="rounded-2xl border border-slate-100 bg-slate-50/80 px-4 py-3" data-dashboard-item data-item-date="{{ optional($part->updated_at)->toIso8601String() }}" data-part-name="{{ $part->name }}" data-part-sku="{{ $part->sku }}" data-part-category="{{ $part->category }}">
                            <div class="flex items-center justify-between gap-3">
                                <div>
                                    <p class="font-semibold text-slate-900">{{ $part->name }}</p>
                                    <p class="text-sm text-slate-500">{{ $part->sku }} &middot; {{ $part->category }}</p>
                                </div>
                                <x-badge tone="warning">{{ $part->current_stock }} / {{ $part->minimum_stock }}</x-badge>
                            </div>
                        </article>
                    @empty
                        <p class="text-sm text-slate-500">No low-stock items right now.</p>
                    @endforelse
                </div>
            </section>

            <section class="panel-card p-5 sm:p-6 dashboard-list-panel">
                <div>
                    <h2 class="text-2xl font-bold text-slate-900">Recent Stock Activity</h2>
                    <p class="mt-1 text-sm text-slate-500">Latest inventory movements.</p>
                </div>

<div class="mt-5 space-y-3" data-dashboard-results="movements">
                    @forelse ($recentMovements as $movement)
                        @php
                            $delta = $movement->delta();
                            $tone = $delta > 0 ? 'success' : ($delta < 0 ? 'danger' : 'accent');
                            $movementPartImageUrl = $movement->part?->image_path && Storage::disk('public')->exists($movement->part->image_path)
                                ? Storage::url($movement->part->image_path)
                                : null;
                        @endphp
                        <article class="rounded-2xl border border-slate-100 bg-white px-4 py-3" data-dashboard-item data-item-date="{{ optional($movement->moved_at)->toIso8601String() }}" data-movement-name="{{ $movement->part?->name ?? '' }}" data-movement-type="{{ $movement->type }}" data-movement-reason="{{ $movement->reason ?? '' }}">
                            <div class="flex items-center justify-between gap-3">
                                <div class="flex min-w-0 items-center gap-3">
                                    @if ($movementPartImageUrl)
                                        <img src="{{ $movementPartImageUrl }}" alt="{{ $movement->part?->name ?? 'Part' }}" class="stock-movement-thumb" loading="lazy" decoding="async">
                                    @else
                                        <span class="stock-movement-thumb stock-movement-thumb-empty">
                                            <x-icon name="image" class="h-5 w-5" />
                                        </span>
                                    @endif
                                    <div class="min-w-0">
                                        <p class="truncate font-semibold text-slate-900">{{ $movement->part?->name ?? 'Part Removed' }}</p>
                                        <p class="truncate text-sm text-slate-500">{{ ucfirst($movement->type) }} &middot; {{ $movement->reason ?: 'Stock update' }}</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <x-badge :tone="$tone">
                                        {{ $delta >= 0 ? '+' : '' }}{{ rtrim(rtrim(number_format((float) $delta, 3, '.', ''), '0'), '.') }}
                                    </x-badge>
                                    <p class="mt-1 text-xs text-slate-400">{{ \App\Support\InventoryMetrics::formatMovementTime($movement->moved_at) }}</p>
                                </div>
                            </div>
                        </article>
                    @empty
                        <p class="text-sm text-slate-500">No stock movement recorded yet.</p>
                    @endforelse
                </div>
            </section>
        </div>
    </section>
@endsection
