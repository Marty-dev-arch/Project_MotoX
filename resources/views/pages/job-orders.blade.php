@extends('layouts.app')

@section('content')
    <section class="space-y-6" data-joborders-metrics-url="{{ $jobOrdersMetricsUrl }}">
        @if (session('status'))
            <div class="auth-alert auth-alert-{{ session('status_tone', 'success') }}">
                <p class="font-semibold">{{ session('status') }}</p>
            </div>
        @endif

        @if ($errors->any())
            <div class="auth-alert auth-alert-danger">
                <p class="font-semibold">{{ $errors->first() }}</p>
            </div>
        @endif

        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <h1 class="text-4xl font-black tracking-tight text-slate-900">{{ $heading }}</h1>
                <p class="mt-2 text-sm text-slate-500">{{ $subheading }}</p>
            </div>
        </div>

        <div class="grid gap-4 md:grid-cols-2 2xl:grid-cols-5">
            <article class="panel-card p-5 col-span-1">
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
            <article class="panel-card p-5 col-span-2 md:col-span-2 2xl:col-span-5 w-full">
                <p class="muted-label">Estimated Value</p>
                <p class="mt-2 text-4xl font-black tracking-tight text-slate-900" data-joborder-kpi="estimated_value">PHP {{ number_format($totalEstimated, 2) }}</p>
            </article>
        </div>

        <div class="flex flex-wrap justify-end gap-3">
            <div class="relative w-full md:w-[360px]">
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
                                    'in_progress' => 'neutral',
                                    'cancelled' => 'danger',
                                    default => 'warning',
                                };
                                $isSelected = $selectedOrder?->id === $order->id;
@endphp
                            <tr class="job-order-row" data-order-number="{{ $order->order_number }}" data-customer="{{ $order->customer?->name ?? 'Walk-in Customer' }}" data-vehicle="{{ $order->vehicle }}" data-status="{{ $order->status }}" data-created-at="{{ $order->created_at?->toIso8601String() }}">
                                <td class="font-semibold text-slate-900">{{ $order->order_number }}</td>
                                <td>
                                    @php
                                        $customerName = $order->customer?->name ?? 'Walk-in Customer';
                                        $customerInitials = collect(explode(' ', $customerName))
                                            ->filter()
                                            ->map(fn (string $part): string => mb_substr($part, 0, 1))
                                            ->take(2)
                                            ->implode('');
                                        $profilePhotoPath = $order->customer_id
                                            ? $order->customer?->profile_photo_path
                                            : $order->walk_in_profile_photo_path;
                                    @endphp
                                    <div class="flex items-center gap-3">
                                        @if ($profilePhotoPath)
                                            <img src="{{ \Illuminate\Support\Facades\Storage::url($profilePhotoPath) }}" alt="{{ $customerName }} profile" class="h-10 w-10 rounded-full object-cover">
                                        @else
                                            <span class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-slate-900 text-xs font-black text-white">{{ strtoupper($customerInitials ?: 'WI') }}</span>
                                        @endif
                                        <span>{{ $customerName }}</span>
                                    </div>
                                </td>
                                <td>{{ $order->vehicle }}</td>
                                <td><x-badge :tone="$tone">{{ str_replace('_', ' ', ucfirst($order->status)) }}</x-badge></td>
                                <td>PHP {{ number_format((float) $order->estimated_cost, 2) }}</td>
                                <td>{{ $order->scheduled_for?->format('M d, Y') ?? '-' }}</td>
                                <td>
                                    <div class="flex justify-end gap-2">
                                        <a href="{{ route('job-orders', ['edit' => $order->id, 'order' => $order->id]) }}" class="icon-button" aria-label="Edit job order">
                                            <x-icon name="pencil" class="h-4 w-4" />
                                        </a>
                                        <button
                                            type="button"
                                            class="icon-button"
                                            aria-label="Delete job order"
                                            data-open-modal="delete-job-order-{{ $order->id }}-modal"
                                        >
                                            <x-icon name="trash" class="h-4 w-4" />
                                        </button>
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

@foreach ($orders as $order)
    <div class="app-modal hidden" data-modal="delete-job-order-{{ $order->id }}-modal">
        <div class="app-modal-card max-w-lg">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <h3 class="text-2xl font-bold text-slate-900">Are you sure?</h3>
                    <p class="mt-2 text-sm text-slate-500">You are about to delete this Job Order.</p>
                </div>
                <button type="button" class="icon-button" data-close-modal="delete-job-order-{{ $order->id }}-modal" aria-label="Cancel delete job order">
                    <x-icon name="x" class="h-4 w-4" />
                </button>
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <button type="button" class="ghost-button" data-close-modal="delete-job-order-{{ $order->id }}-modal">Cancel</button>
                <form method="POST" action="{{ route('job-orders.destroy', $order) }}">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="danger-button">Yes, Delete</button>
                </form>
            </div>
        </div>
    </div>
@endforeach

<div class="app-modal {{ $isCreating ? '' : 'hidden' }}" data-modal="create-joborder-modal">
        <div class="app-modal-card">
            <div class="flex items-center justify-between gap-3">
                <h3 class="text-2xl font-bold text-slate-900">Create Job Order</h3>
                <button type="button" class="icon-button" data-close-modal="create-joborder-modal">
                    <x-icon name="x" class="h-4 w-4" />
                </button>
            </div>

            <form method="POST" action="{{ route('job-orders.store') }}" class="mt-6 space-y-4" enctype="multipart/form-data">
                @csrf

                <label class="form-field">
                    <span class="muted-label">Customer</span>
                    <select name="customer_id" class="input-shell" data-customer-photo-select="create-joborder-profile">
                        <option value="">Walk-in Customer</option>
                        @foreach ($customers as $customer)
                            <option value="{{ $customer->id }}" data-photo-url="{{ $customer->profile_photo_path ? \Illuminate\Support\Facades\Storage::url($customer->profile_photo_path) : '' }}">{{ $customer->name }}</option>
                        @endforeach
                    </select>
                </label>

                <label class="part-upload-card customer-upload-card" data-walk-in-upload="create-joborder-profile">
                    <span class="part-upload-preview customer-upload-preview">
                        <img src="" alt="Job order customer profile preview" class="hidden" data-image-preview="create-joborder-profile">
                        <span data-image-preview-placeholder="create-joborder-profile">
                            <x-icon name="camera" class="h-8 w-8" />
                        </span>
                    </span>
                    <span class="part-upload-content">
                        <span class="part-upload-title">Choose Customer Profile Photo</span>
                        <span class="part-upload-note">PNG, JPG, WEBP up to 2MB.Select an existing customer to use their saved profile photo, or upload one for walk-in.</span>
                    </span>
                    <input type="file" name="walk_in_profile_photo" accept="image/*" class="sr-only" data-image-preview-input="create-joborder-profile">
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

    @if ($editingOrder)
        <div class="app-modal" data-modal="edit-joborder-modal">
            <div class="app-modal-card">
                <div class="flex items-center justify-between gap-3">
                    <h3 class="text-2xl font-bold text-slate-900">Edit Job Order</h3>
                    <a href="{{ route('job-orders', ['order' => $editingOrder->id]) }}" class="icon-button" aria-label="Close edit job order">
                        <x-icon name="x" class="h-4 w-4" />
                    </a>
                </div>

                <form method="POST" action="{{ route('job-orders.update', $editingOrder) }}" class="mt-6 space-y-4" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <label class="form-field">
                        <span class="muted-label">Customer</span>
                        <select name="customer_id" class="input-shell" data-customer-photo-select="edit-joborder-profile">
                            <option value="">Walk-in Customer</option>
                            @foreach ($customers as $customer)
                                <option value="{{ $customer->id }}" data-photo-url="{{ $customer->profile_photo_path ? \Illuminate\Support\Facades\Storage::url($customer->profile_photo_path) : '' }}" @selected((string) old('customer_id', $editingOrder->customer_id) === (string) $customer->id)>{{ $customer->name }}</option>
                            @endforeach
                        </select>
                    </label>

                    <label class="part-upload-card customer-upload-card" data-walk-in-upload="edit-joborder-profile">
                        <span class="part-upload-preview customer-upload-preview">
                            @php
                                $editPhotoPath = $editingOrder->customer_id
                                    ? $editingOrder->customer?->profile_photo_path
                                    : $editingOrder->walk_in_profile_photo_path;
                            @endphp
                            @if ($editPhotoPath)
                                <img src="{{ \Illuminate\Support\Facades\Storage::url($editPhotoPath) }}" alt="Job order customer profile preview" data-image-preview="edit-joborder-profile">
                                <span class="hidden" data-image-preview-placeholder="edit-joborder-profile">
                                    <x-icon name="camera" class="h-8 w-8" />
                                </span>
                            @else
                                <img src="" alt="Job order customer profile preview" class="hidden" data-image-preview="edit-joborder-profile">
                                <span data-image-preview-placeholder="edit-joborder-profile">
                                    <x-icon name="camera" class="h-8 w-8" />
                                </span>
                            @endif
                        </span>
                        <span class="part-upload-content">
                            <span class="part-upload-title">Walk-in customer profile photo</span>
                            <span class="part-upload-note">Selected customer photos appear automatically. Upload only for walk-in customers.</span>
                        </span>
                        <input type="file" name="walk_in_profile_photo" accept="image/*" class="sr-only" data-image-preview-input="edit-joborder-profile">
                    </label>

                    <div class="grid gap-4 md:grid-cols-2">
                        <label class="form-field">
                            <span class="muted-label">Vehicle</span>
                            <input type="text" name="vehicle" value="{{ old('vehicle', $editingOrder->vehicle) }}" class="input-shell" required>
                        </label>
                        <label class="form-field">
                            <span class="muted-label">Status</span>
                            <select name="status" class="input-shell" required>
                                @foreach ($statusOptions as $status)
                                    <option value="{{ $status }}" @selected(old('status', $editingOrder->status) === $status)>{{ str_replace('_', ' ', ucfirst($status)) }}</option>
                                @endforeach
                            </select>
                        </label>
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        <label class="form-field">
                            <span class="muted-label">Concern</span>
                            <input type="text" name="concern" value="{{ old('concern', $editingOrder->concern) }}" class="input-shell" required>
                        </label>
                        <label class="form-field">
                            <span class="muted-label">Estimated Cost (PHP)</span>
                            <input type="number" step="0.01" min="0" name="estimated_cost" value="{{ old('estimated_cost', number_format((float) $editingOrder->estimated_cost, 2, '.', '')) }}" class="input-shell" required>
                        </label>
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        <label class="form-field">
                            <span class="muted-label">Scheduled For</span>
                            <input type="date" name="scheduled_for" value="{{ old('scheduled_for', $editingOrder->scheduled_for?->format('Y-m-d')) }}" class="input-shell">
                        </label>
                        <label class="form-field">
                            <span class="muted-label">Notes</span>
                            <textarea name="notes" rows="1" class="input-shell">{{ old('notes', $editingOrder->notes) }}</textarea>
                        </label>
                    </div>

                    <div class="flex justify-end gap-3 pt-2">
                        <a href="{{ route('job-orders', ['order' => $editingOrder->id]) }}" class="ghost-button">Cancel</a>
                        <button type="submit" class="primary-button">Save Job Order</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    </section>
@endsection
