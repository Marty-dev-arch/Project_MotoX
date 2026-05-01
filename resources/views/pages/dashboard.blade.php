@extends('layouts.app')

@section('content')
    <section class="space-y-8" data-dashboard-metrics-url="{{ $dashboardMetricsUrl }}">
        @if (session('status'))
            <div class="auth-alert">
                <p class="font-semibold">{{ session('status') }}</p>
            </div>
        @endif

<div class="space-y-2">
            <h1 class="text-4xl font-black tracking-tight text-slate-900">{{ $heading }}</h1>
            <p class="text-base text-slate-500">{{ $subheading }}</p>
        </div>

        <div class="flex items-center gap-4">
            <label class="search-shell flex-1 max-w-md" data-dashboard-search>
                <x-icon name="search" class="h-5 w-5 text-slate-400" />
                <input
                    type="text"
                    id="dashboard-search-input"
                    placeholder="Search part, sku, category..."
                    class="w-full border-0 bg-transparent p-0 text-sm text-slate-700 placeholder:text-slate-400 focus:outline-none focus:ring-0"
                >
            </label>
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

        <div class="grid gap-6 xl:grid-cols-[1.4fr_1fr]">
            <section class="panel-card budget-card p-5 sm:p-6">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <h2 class="text-2xl font-bold text-slate-900">Consolidated Stock Flow</h2>
                        <div class="budget-legend mt-2">
                            <span class="budget-legend-item"><i class="budget-legend-dot budget-legend-dot-in"></i>Stock In</span>
                            <span class="budget-legend-item"><i class="budget-legend-dot budget-legend-dot-out"></i>Stock Out</span>
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center gap-3">
                        <div class="budget-range-pills">
                            <button type="button" class="budget-range-pill" data-dashboard-range="2">2D</button>
                            <button type="button" class="budget-range-pill budget-range-pill-active" data-dashboard-range="7">7D</button>
                            <button type="button" class="budget-range-pill" data-dashboard-range="30">30D</button>
                            <button type="button" class="budget-range-pill" data-dashboard-range="90">90D</button>
                            <button type="button" class="budget-range-pill" data-dashboard-range="365">365D</button>
                        </div>
                        <span class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400" data-updated-at>Updated now</span>
                    </div>
                </div>

                <div
                    class="mt-6"
                    data-chart="movement"
                    data-trend='@json($trend)'
                ></div>
            </section>

            <section class="panel-card p-5 sm:p-6">
                <div>
                    <h2 class="text-2xl font-bold text-slate-900">Low Stock by Category</h2>
                    <p class="mt-1 text-sm text-slate-500">Categories requiring immediate replenishment.</p>
                </div>

                <div class="mt-6 space-y-4" data-chart="low-stock">
                    @forelse ($lowStockByCategory as $row)
                        @php
                            $width = min(100, max(12, $row['count'] * 15));
                        @endphp
                        <article>
                            <div class="mb-2 flex items-center justify-between">
                                <p class="text-sm font-semibold text-slate-800">{{ $row['category'] }}</p>
                                <p class="text-sm font-bold text-brand-700">{{ $row['count'] }}</p>
                            </div>
                            <div class="h-2.5 w-full rounded-full bg-slate-100">
                                <div class="h-full rounded-full bg-brand-500" style="width: {{ $width }}%"></div>
                            </div>
                        </article>
                    @empty
                        <p class="text-sm text-slate-500">All categories are currently above minimum stock.</p>
                    @endforelse
                </div>
            </section>
        </div>

<div class="grid gap-6 xl:grid-cols-[1fr_1fr]">
            <section class="panel-card p-5 sm:p-6">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <h2 class="text-2xl font-bold text-slate-900">Low Stock Parts</h2>
                        <p class="mt-1 text-sm text-slate-500">Items currently below configured minimum stock.</p>
                    </div>
                    <a href="{{ route('inventory') }}" class="text-sm font-semibold text-brand-600 transition hover:text-brand-700">Open Inventory</a>
                </div>

                <div class="mt-5 space-y-3" data-dashboard-results="low-stock">
                    @forelse ($lowStockParts as $part)
                        <article class="rounded-2xl border border-slate-100 bg-slate-50/80 px-4 py-3" data-dashboard-item data-part-name="{{ $part->name }}" data-part-sku="{{ $part->sku }}" data-part-category="{{ $part->category }}">
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

            <section class="panel-card p-5 sm:p-6">
                <div>
                    <h2 class="text-2xl font-bold text-slate-900">Recent Stock Activity</h2>
                    <p class="mt-1 text-sm text-slate-500">Latest inventory movements from your team.</p>
                </div>

<div class="mt-5 space-y-3" data-dashboard-results="movements">
                    @forelse ($recentMovements as $movement)
                        @php
                            $delta = $movement->delta();
                            $tone = $delta > 0 ? 'success' : ($delta < 0 ? 'danger' : 'accent');
                        @endphp
                        <article class="rounded-2xl border border-slate-100 bg-white px-4 py-3" data-dashboard-item data-movement-name="{{ $movement->part?->name ?? '' }}" data-movement-type="{{ $movement->type }}" data-movement-reason="{{ $movement->reason ?? '' }}">
                            <div class="flex items-center justify-between gap-3">
                                <div>
                                    <p class="font-semibold text-slate-900">{{ $movement->part?->name ?? 'Part Removed' }}</p>
                                    <p class="text-sm text-slate-500">{{ ucfirst($movement->type) }} &middot; {{ $movement->reason ?: 'Stock update' }}</p>
                                </div>
                                <div class="text-right">
                                    <x-badge :tone="$tone">{{ $delta >= 0 ? '+' : '' }}{{ $delta }}</x-badge>
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
