@extends('layouts.app')

@section('content')
    <section class="space-y-6" data-customers-metrics-url="{{ $customersMetricsUrl }}">
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

        <div class="grid gap-4 md:grid-cols-3">
            <article class="panel-card p-5">
                <p class="muted-label">Total Customers</p>
                <p class="mt-2 text-4xl font-black tracking-tight text-slate-900" data-customer-kpi="total">{{ number_format($stats['total']) }}</p>
            </article>
            <article class="panel-card p-5">
                <p class="muted-label">Active Jobs</p>
                <p class="mt-2 text-4xl font-black tracking-tight text-slate-900" data-customer-kpi="active_jobs">{{ number_format($stats['active_jobs']) }}</p>
            </article>
            <article class="panel-card p-5">
                <p class="muted-label">New This Month</p>
                <p class="mt-2 text-4xl font-black tracking-tight text-slate-900" data-customer-kpi="new_this_month">{{ number_format($stats['new_this_month']) }}</p>
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
                    id="customer-search-input"
                    placeholder="Search customer, email, phone..."
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
                        <button type="button" class="w-full px-4 py-2 text-left text-sm text-slate-700 hover:bg-slate-100" data-progress-filter="active">With Active Jobs</button>
                        <button type="button" class="w-full px-4 py-2 text-left text-sm text-slate-700 hover:bg-slate-100" data-progress-filter="no-active">No Active Jobs</button>
                    </div>
                </div>
            </div>
        </div>

        @php
            $isEditing = $editingCustomer !== null;
        @endphp

        <!-- Add Customer (full width) -->
        <section class="panel-card p-5 sm:p-6">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <h2 class="text-2xl font-bold text-slate-900">{{ $isEditing ? 'Edit Customer' : 'Add Customer' }}</h2>
                    <p class="mt-1 text-sm text-slate-500">Use real customer records for job orders and billing.</p>
                </div>

                @if ($isEditing)
                    <a href="{{ route('customers') }}" class="ghost-button">Cancel</a>
                @endif
            </div>

            <form
                method="POST"
                action="{{ $isEditing ? route('customers.update', $editingCustomer) : route('customers.store') }}"
                class="mt-6 space-y-4"
            >
                @csrf
                @if ($isEditing)
                    @method('PUT')
                @endif

                <div class="grid gap-4 md:grid-cols-2">
                    <label class="form-field">
                        <span class="muted-label">Full Name</span>
                        <input
                            type="text"
                            name="name"
                            value="{{ old('name', $editingCustomer?->name) }}"
                            class="input-shell"
                            required
                        >
                    </label>

                    <label class="form-field">
                        <span class="muted-label">Email</span>
                        <input
                            type="email"
                            name="email"
                            value="{{ old('email', $editingCustomer?->email) }}"
                            class="input-shell"
                        >
                    </label>
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                    <label class="form-field">
                        <span class="muted-label">Phone</span>
                        <input
                            type="text"
                            name="phone"
                            value="{{ old('phone', $editingCustomer?->phone) }}"
                            class="input-shell"
                        >
                    </label>

                    <label class="form-field">
                        <span class="muted-label">Address</span>
                        <input
                            type="text"
                            name="address"
                            value="{{ old('address', $editingCustomer?->address) }}"
                            class="input-shell"
                        >
                    </label>
                </div>

                <label class="form-field">
                    <span class="muted-label">Notes</span>
                    <textarea name="notes" rows="4" class="input-shell">{{ old('notes', $editingCustomer?->notes) }}</textarea>
                </label>

                <div class="flex justify-end">
                    <button type="submit" class="primary-button">
                        {{ $isEditing ? 'Save Changes' : 'Create Customer' }}
                    </button>
                </div>
            </form>
        </section>

        <!-- Customer Directory (full width) -->
        <section class="table-shell">
            <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
                <div>
                    <h3 class="text-2xl font-bold tracking-tight text-slate-900">Customer Directory</h3>
                    <p class="text-sm text-slate-500" id="visible-customers-count">{{ $customers->count() }} profiles</p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="soft-table w-full min-w-[860px]">
                    <thead>
                        <tr class="table-heading">
                            <th>Name</th>
                            <th>Contact</th>
                            <th>Job Orders</th>
                            <th>Updated</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($customers as $customer)
                            @php
                                $isSelected = $selectedCustomer?->id === $customer->id;
                            @endphp
                            <tr 
                                class="customer-row"
                                data-name="{{ $customer->name }}"
                                data-email="{{ $customer->email ?? '' }}"
                                data-phone="{{ $customer->phone ?? '' }}"
                                data-created-at="{{ $customer->created_at->toIso8601String() }}"
                                data-active-jobs="{{ $customer->active_job_orders_count }}"
                            >
                                <td>
                                    <div>
                                        <p class="font-semibold text-slate-900">{{ $customer->name }}</p>
                                        <p class="text-xs text-slate-500">{{ $customer->email ?: 'No email' }}</p>
                                    </div>
                                </td>
                                <td>{{ $customer->phone ?: 'No phone' }}</td>
                                <td>
                                    <x-badge :tone="$customer->active_job_orders_count > 0 ? 'warning' : 'neutral'">
                                        {{ $customer->job_orders_count }} total
                                    </x-badge>
                                </td>
                                <td>{{ $customer->updated_at->format('M d, Y') }}</td>
                                <td>
                                    <div class="flex justify-end gap-2">
                                        <a
                                            href="{{ route('customers', ['customer' => $customer->id]) }}"
                                            class="icon-button"
                                            aria-label="View customer"
                                        >
                                            <x-icon name="id-card" class="h-4 w-4" />
                                        </a>
                                        <a
                                            href="{{ route('customers', ['edit' => $customer->id, 'customer' => $customer->id]) }}"
                                            class="icon-button"
                                            aria-label="Edit customer"
                                        >
                                            <x-icon name="pencil" class="h-4 w-4" />
                                        </a>
                                        <form method="POST" action="{{ route('customers.destroy', $customer) }}" onsubmit="return confirm('Delete this customer?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="icon-button" aria-label="Delete customer">
                                                <x-icon name="trash" class="h-4 w-4" />
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-10 text-center text-sm text-slate-500">No customers yet. Add your first customer profile.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
                        </table>
                    </div>
                </div>
            </section>
</section>
@endsection
