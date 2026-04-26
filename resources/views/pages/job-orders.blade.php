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

        <div class="grid gap-6 xl:grid-cols-[0.95fr_1.05fr]">
            @php
                $isEditing = $editingOrder !== null;
            @endphp

            <section class="panel-card p-5 sm:p-6">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <h2 class="text-2xl font-bold text-slate-900">{{ $isEditing ? 'Edit Job Order' : 'Create Job Order' }}</h2>
                        <p class="mt-1 text-sm text-slate-500">Use this form for real workshop job tracking.</p>
                    </div>

                    @if ($isEditing)
                        <a href="{{ route('job-orders') }}" class="ghost-button">Cancel</a>
                    @endif
                </div>

                <form
                    method="POST"
                    action="{{ $isEditing ? route('job-orders.update', $editingOrder) : route('job-orders.store') }}"
                    class="mt-6 space-y-4"
                >
                    @csrf
                    @if ($isEditing)
                        @method('PUT')
                    @endif

                    <label class="form-field">
                        <span class="muted-label">Customer</span>
                        <select name="customer_id" class="input-shell">
                            <option value="">Walk-in Customer</option>
                            @foreach ($customers as $customer)
                                <option
                                    value="{{ $customer->id }}"
                                    @selected((string) old('customer_id', $editingOrder?->customer_id) === (string) $customer->id)
                                >
                                    {{ $customer->name }}
                                </option>
                            @endforeach
                        </select>
                    </label>

                    <div class="grid gap-4 md:grid-cols-2">
                        <label class="form-field">
                            <span class="muted-label">Vehicle</span>
                            <input
                                type="text"
                                name="vehicle"
                                value="{{ old('vehicle', $editingOrder?->vehicle) }}"
                                class="input-shell"
                                required
                            >
                        </label>
                        <label class="form-field">
                            <span class="muted-label">Status</span>
                            <select name="status" class="input-shell" required>
                                @foreach ($statusOptions as $status)
                                    <option value="{{ $status }}" @selected(old('status', $editingOrder?->status ?? 'pending') === $status)>
                                        {{ str_replace('_', ' ', ucfirst($status)) }}
                                    </option>
                                @endforeach
                            </select>
                        </label>
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        <label class="form-field">
                            <span class="muted-label">Concern</span>
                            <input
                                type="text"
                                name="concern"
                                value="{{ old('concern', $editingOrder?->concern) }}"
                                class="input-shell"
                                required
                            >
                        </label>
                        <label class="form-field">
                            <span class="muted-label">Estimated Cost (PHP)</span>
                            <input
                                type="number"
                                step="0.01"
                                min="0"
                                name="estimated_cost"
                                value="{{ old('estimated_cost', $editingOrder?->estimated_cost ?? '0.00') }}"
                                class="input-shell"
                                required
                            >
                        </label>
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        <label class="form-field">
                            <span class="muted-label">Scheduled For</span>
                            <input
                                type="date"
                                name="scheduled_for"
                                value="{{ old('scheduled_for', optional($editingOrder?->scheduled_for)->format('Y-m-d')) }}"
                                class="input-shell"
                            >
                        </label>
                        <label class="form-field">
                            <span class="muted-label">Notes</span>
                            <textarea name="notes" rows="1" class="input-shell">{{ old('notes', $editingOrder?->notes) }}</textarea>
                        </label>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" class="primary-button">
                            {{ $isEditing ? 'Save Changes' : 'Create Job Order' }}
                        </button>
                    </div>
                </form>
            </section>

            <section class="table-shell">
                <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
                    <div>
                        <h3 class="text-2xl font-bold tracking-tight text-slate-900">Job Order List</h3>
                        <p class="text-sm text-slate-500">Latest orders with real-time status tracking.</p>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="soft-table min-w-[980px]">
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
                                <tr @class(['bg-brand-50/40' => $isSelected])>
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
        </div>

        @if ($selectedOrder)
            @php
                $selectedTone = match ($selectedOrder->status) {
                    'completed' => 'success',
                    'in_progress' => 'accent',
                    'cancelled' => 'danger',
                    default => 'warning',
                };
            @endphp
            <section class="panel-card p-5 sm:p-6">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <div class="flex items-center gap-3">
                            <h3 class="text-3xl font-black tracking-tight text-slate-900">{{ $selectedOrder->order_number }}</h3>
                            <x-badge :tone="$selectedTone">{{ str_replace('_', ' ', ucfirst($selectedOrder->status)) }}</x-badge>
                        </div>
                        <p class="mt-2 text-sm text-slate-500">{{ $selectedOrder->concern }}</p>
                    </div>
                    <x-badge tone="accent">PHP {{ number_format((float) $selectedOrder->estimated_cost, 2) }}</x-badge>
                </div>

                <div class="mt-6 grid gap-4 md:grid-cols-4">
                    <article class="detail-card">
                        <p class="muted-label">Customer</p>
                        <p class="mt-2 text-xl font-bold text-slate-900">{{ $selectedOrder->customer?->name ?? 'Walk-in Customer' }}</p>
                    </article>
                    <article class="detail-card">
                        <p class="muted-label">Vehicle</p>
                        <p class="mt-2 text-xl font-bold text-slate-900">{{ $selectedOrder->vehicle }}</p>
                    </article>
                    <article class="detail-card">
                        <p class="muted-label">Scheduled</p>
                        <p class="mt-2 text-xl font-bold text-slate-900">{{ $selectedOrder->scheduled_for?->format('M d, Y') ?? '-' }}</p>
                    </article>
                    <article class="detail-card">
                        <p class="muted-label">Completed</p>
                        <p class="mt-2 text-xl font-bold text-slate-900">{{ $selectedOrder->completed_at?->format('M d, Y h:i A') ?? '-' }}</p>
                    </article>
                </div>

                @if ($selectedOrder->notes)
                    <div class="mt-6 rounded-2xl border border-slate-200 bg-slate-50/70 p-4">
                        <p class="muted-label">Notes</p>
                        <p class="mt-2 text-sm text-slate-600">{{ $selectedOrder->notes }}</p>
                    </div>
                @endif
            </section>
        @endif
    </section>
@endsection
