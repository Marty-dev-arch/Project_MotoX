@extends('layouts.app')

@section('content')
    <section class="space-y-6" data-joborders-metrics-url="{{ $jobOrdersMetricsUrl }}">
        @if (session('status'))
            <div class="auth-alert">
                <p class="font-semibold">{{ session('status') }}</p>
            </div>
        @endif

        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <h1 class="text-4xl font-black tracking-tight text-slate-900">{{ $heading }}</h1>
                <p class="mt-2 text-sm text-slate-500">{{ $subheading }}</p>
            </div>
        </div>

        <div class="grid gap-4 md:grid-cols-2 2xl:grid-cols-5">
            <article class="panel-card p-5">
                <p class="muted-label">Total Orders</p>
                <p class="mt-2 text-4xl font-black tracking-tight text-slate-900" data-joborder-kpi="total_orders">{{ number_format($orders->count()) }}</p>
            </article>
            <article class="panel-card p-5">
                <p class="muted-label">Pending</p>
                <p class="mt-2 text-4xl font-black tracking-tight text-slate-900" data-joborder-kpi="pending">{{ number_format($statusCounts['pending']) }}</p>
            </article>
            <article class="panel-card p-5">
                <p class="muted-label">In Progress</p>
                <p class="mt-2 text-4xl font-black tracking-tight text-slate-900" data-joborder-kpi="in_progress">{{ number_format($statusCounts['in_progress']) }}</p>
            </article>
            <article class="panel-card p-5">
                <p class="muted-label">Completed</p>
                <p class="mt-2 text-4xl font-black tracking-tight text-slate-900" data-joborder-kpi="completed">{{ number_format($statusCounts['completed']) }}</p>
            </article>
            <article class="panel-card p-5">
                <p class="muted-label">Estimated Value</p>
                <p class="mt-2 text-4xl font-black tracking-tight text-slate-900" data-joborder-kpi="estimated_value">PHP {{ number_format($totalEstimated, 2) }}</p>
            </article>
        </div>

         <!-- Search & Filter -->
        <div class="flex flex-wrap gap-3">
            <div class="relative flex-1 min-w-[200px]">
                <span class="absolute inset-y-0 left-4 flex items-center text-slate-400">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
                    </svg>
</span>
                <input
                    type="text"
                    id="joborder-search-input"
                    placeholder="Search order, customer, vehicle..."
                    class="input-shell w-full pl-11"
                />
            </div>
           <!-- Filter by Date Dropdown -->
            <div class="relative">
                <button type="button" id="filter-date-btn" class="ghost-button flex items-center gap-2">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                        <line x1="16" y1="2" x2="16" y2="6"/>
                        <line x1="8" y1="2" x2="8" y2="6"/>
                        <line x1="3" y1="10" x2="21" y2="10"/>
                    </svg>
                    <span>Filter by Date</span>
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="m6 9 6 6 6-6"/>
                    </svg>
                </button>
                <div id="date-dropdown" class="hidden absolute top-full right-0 mt-1 w-48 bg-white rounded-lg shadow-lg border border-slate-200 z-50">
                    <div class="py-2">
                        <button type="button" class="w-full px-4 py-2 text-left text-sm text-slate-700 hover:bg-slate-100" data-date-filter="all">All Time</button>
                        <button type="button" class="w-full px-4 py-2 text-left text-sm text-slate-700 hover:bg-slate-100" data-date-filter="today">Today</button>
                        <button type="button" class="w-full px-4 py-2 text-left text-sm text-slate-700 hover:bg-slate-100" data-date-filter="week">This Week</button>
                        <button type="button" class="w-full px-4 py-2 text-left text-sm text-slate-700 hover:bg-slate-100" data-date-filter="month">This Month</button>
                        <button type="button" class="w-full px-4 py-2 text-left text-sm text-slate-700 hover:bg-slate-100" data-date-filter="year">This Year</button>
                    </div>
                </div>
            </div>

            <!-- Filter by Progress Dropdown -->
            <div class="relative">
                <button type="button" id="filter-progress-btn" class="ghost-button flex items-center gap-2">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M12 20V10"/>
                        <path d="M18 20V4"/>
                        <path d="M6 20v-4"/>
                    </svg>
                    <span>Filter by Progress</span>
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="m6 9 6 6 6-6"/>
                    </svg>
                </button>
<div id="progress-dropdown" class="hidden absolute top-full right-0 mt-1 w-48 bg-white rounded-lg shadow-lg border border-slate-200 z-50">
                    <div class="py-2">
                        <button type="button" class="w-full px-4 py-2 text-left text-sm text-slate-700 hover:bg-slate-100" data-progress-filter="all">All</button>
                        <button type="button" class="w-full px-4 py-2 text-left text-sm text-slate-700 hover:bg-slate-100" data-progress-filter="pending">Pending</button>
                        <button type="button" class="w-full px-4 py-2 text-left text-sm text-slate-700 hover:bg-slate-100" data-progress-filter="in_progress">In Progress</button>
                        <button type="button" class="w-full px-4 py-2 text-left text-sm text-slate-700 hover:bg-slate-100" data-progress-filter="completed">Completed</button>
                    </div>
                </div>
            </div>
        </div>

{{-- Job Order List (full width) --}}
        <section class="table-shell">
            <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
                <div>
                    <h3 class="text-2xl font-bold tracking-tight text-slate-900">Job Order List</h3>
                    <p class="text-sm text-slate-500">Latest orders with real-time status tracking.</p>
                </div>
<button type="button" class="primary-button" data-open-modal="create-joborder-modal">
                    <x-icon name="plus" class="h-4 w-4" />
                    <span>Create Job Order</span>
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="soft-table w-full min-w-[980px]">
                    <thead>
                        <tr class="table-heading">
                            <th>Order #</th>
                            <th>Customer</th>
                            <th>Vehicle</th>
                            <th>Status</th>
                            <th>Estimated Cost</th>
                            <th>Scheduled</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($orders as $order)
                            @php
                                $tone = match ($order->status) {
                                    'completed' => 'success',
                                    'in_progress' => 'accent',
                                    'cancelled' => 'danger',
                                    default => 'warning',
                                };
                                $isSelected = $selectedOrder?->id === $order->id;
@endphp
                            <tr class="job-order-row {{ $isSelected ? 'bg-brand-50/40' : '' }}" data-order-number="{{ $order->order_number }}" data-customer="{{ $order->customer?->name ?? 'Walk-in Customer' }}" data-vehicle="{{ $order->vehicle }}" data-status="{{ $order->status }}" data-created-at="{{ $order->created_at?->toIso8601String() }}">
                                <td class="font-semibold text-slate-900">{{ $order->order_number }}</td>
                                <td>{{ $order->customer?->name ?? 'Walk-in Customer' }}</td>
                                <td>{{ $order->vehicle }}</td>
                                <td><x-badge :tone="$tone">{{ str_replace('_', ' ', ucfirst($order->status)) }}</x-badge></td>
                                <td>PHP {{ number_format((float) $order->estimated_cost, 2) }}</td>
                                <td>{{ $order->scheduled_for?->format('M d, Y') ?? '-' }}</td>
                                <td>
                                    <div class="flex justify-end gap-2">
                                        <a href="{{ route('job-orders', ['order' => $order->id]) }}" class="icon-button" aria-label="View job order">
                                            <x-icon name="file" class="h-4 w-4" />
                                        </a>
                                        <a href="{{ route('job-orders', ['edit' => $order->id, 'order' => $order->id]) }}" class="icon-button" aria-label="Edit job order">
                                            <x-icon name="pencil" class="h-4 w-4" />
                                        </a>
                                        <form method="POST" action="{{ route('job-orders.destroy', $order) }}" onsubmit="return confirm('Delete this job order?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="icon-button" aria-label="Delete job order">
                                                <x-icon name="trash" class="h-4 w-4" />
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-10 text-center text-sm text-slate-500">No job orders yet. Create one to start tracking work.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
</section>

    <!-- Create Job Order Modal -->
<div class="app-modal hidden" data-modal="create-joborder-modal">
        <div class="app-modal-card">
            <div class="flex items-center justify-between gap-3">
                <h3 class="text-2xl font-bold text-slate-900">Create Job Order</h3>
                <button type="button" class="icon-button" data-close-modal="create-joborder-modal">
                    <x-icon name="x" class="h-4 w-4" />
                </button>
            </div>

            <form method="POST" action="{{ route('job-orders.store') }}" class="mt-6 space-y-4">
                @csrf

                <label class="form-field">
                    <span class="muted-label">Customer</span>
                    <select name="customer_id" class="input-shell">
                        <option value="">Walk-in Customer</option>
                        @foreach ($customers as $customer)
                            <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                        @endforeach
                    </select>
                </label>

                <div class="grid gap-4 md:grid-cols-2">
                    <label class="form-field">
                        <span class="muted-label">Vehicle</span>
                        <input type="text" name="vehicle" class="input-shell" required>
                    </label>
                    <label class="form-field">
                        <span class="muted-label">Status</span>
                        <select name="status" class="input-shell" required>
                            @foreach ($statusOptions as $status)
                                <option value="{{ $status }}">{{ str_replace('_', ' ', ucfirst($status)) }}</option>
                            @endforeach
                        </select>
                    </label>
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                    <label class="form-field">
                        <span class="muted-label">Concern</span>
                        <input type="text" name="concern" class="input-shell" required>
                    </label>
                    <label class="form-field">
                        <span class="muted-label">Estimated Cost (PHP)</span>
                        <input type="number" step="0.01" min="0" name="estimated_cost" class="input-shell" required>
                    </label>
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                    <label class="form-field">
                        <span class="muted-label">Scheduled For</span>
                        <input type="date" name="scheduled_for" class="input-shell">
                    </label>
                    <label class="form-field">
                        <span class="muted-label">Notes</span>
                        <textarea name="notes" rows="1" class="input-shell"></textarea>
                    </label>
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" class="ghost-button" data-close-modal="create-joborder-modal">Cancel</button>
                    <button type="submit" class="primary-button">Create Job Order</button>
                </div>
            </form>
        </div>
    </div>

    </section>
@endsection
