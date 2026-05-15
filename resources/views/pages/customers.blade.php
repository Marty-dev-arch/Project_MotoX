@extends('layouts.app')

@section('content')
    <section class="space-y-6" data-customers-metrics-url="{{ $customersMetricsUrl }}">
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

        <div class="flex flex-wrap justify-end gap-3">
            <div class="relative w-full md:w-[360px]">
                <span class="absolute inset-y-0 left-4 flex items-center text-slate-400">
                    <x-icon name="search" class="h-4 w-4" />
                </span>
                <input
                    type="text"
                    id="customer-search-input"
                    placeholder="Search customer, email, phone..."
                    class="input-shell w-full pl-11"
                />
            </div>

            <div class="relative">
                <button type="button" id="filter-date-btn" class="ghost-button flex items-center gap-2">
                    <x-icon name="calendar" class="h-4 w-4" />
                    <span>Filter by Date</span>
                    <x-icon name="chevron-down" class="h-4 w-4" />
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
                    <x-icon name="reports" class="h-4 w-4" />
                    <span>Filter by Progress</span>
                    <x-icon name="chevron-down" class="h-4 w-4" />
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



        <section class="table-shell">
            <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
                <div>
                    <h3 class="text-2xl font-bold tracking-tight text-slate-900">Customer Directory</h3>
                    <p class="text-sm text-slate-500" id="visible-customers-count">{{ $customers->count() }} profiles</p>
                </div>
                <button type="button" class="primary-button" data-open-modal="create-customer-modal">
                    <x-icon name="plus" class="h-4 w-4" />
                    <span>Create Customer</span>
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="soft-table w-full min-w-[1040px]">
                    <thead>
                        <tr class="table-heading">
                            <th>Name</th>
                            <th>Contact</th>
                            <th>Notes</th>
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
                                data-notes="{{ $customer->notes ?? '' }}"
                                data-created-at="{{ $customer->created_at->toIso8601String() }}"
                                data-active-jobs="{{ $customer->active_job_orders_count }}"
                            >
                                <td>
                                    <div class="flex items-center gap-3">
                                        @php
                                            $initials = collect(explode(' ', (string) $customer->name))
                                                ->filter()
                                                ->map(fn (string $part): string => mb_substr($part, 0, 1))
                                                ->take(2)
                                                ->implode('');
                                        @endphp
                                        @if ($customer->profile_photo_path)
                                            <img
                                                src="{{ \Illuminate\Support\Facades\Storage::url($customer->profile_photo_path) }}"
                                                alt="{{ $customer->name }} profile photo"
                                                class="h-11 w-11 rounded-full object-cover"
                                            >
                                        @else
                                            <span class="inline-flex h-11 w-11 items-center justify-center rounded-full bg-slate-900 text-sm font-black text-white">
                                                {{ strtoupper($initials ?: 'CU') }}
                                            </span>
                                        @endif
                                        <div>
                                            <p class="font-semibold text-slate-900">{{ $customer->name }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="space-y-1 text-sm">
                                        <p class="font-semibold text-slate-900">{{ $customer->phone ?: 'No phone' }}</p>
                                        <p class="text-xs text-slate-500">{{ $customer->email ?: 'No email' }}</p>
                                        <p class="text-xs text-slate-500">{{ $customer->address ?: 'No address' }}</p>
                                    </div>
                                </td>
                                <td class="max-w-[280px]">
                                    @if (filled($customer->notes))
                                        <p class="line-clamp-2 text-sm text-slate-600" title="{{ $customer->notes }}">{{ $customer->notes }}</p>
                                    @else
                                        <span class="text-sm text-slate-400">No notes</span>
                                    @endif
                                </td>
                                <td>
                                    <x-badge :tone="$customer->active_job_orders_count > 0 ? 'warning' : 'neutral'">
                                        {{ $customer->job_orders_count }} total
                                    </x-badge>
                                </td>
                                <td>{{ $customer->updated_at->format('M d, Y') }}</td>
                                <td>
                                    <div class="flex justify-end gap-2">
                                        <a
                                            href="{{ route('customers', ['history' => $customer->id, 'customer' => $customer->id]) }}"
                                            class="icon-button"
                                            aria-label="View customer history"
                                        >
                                            <x-icon name="clipboard" class="h-4 w-4" />
                                        </a>
                                        <a
                                            href="{{ route('customers', ['edit' => $customer->id, 'customer' => $customer->id]) }}"
                                            class="icon-button"
                                            aria-label="Edit customer"
                                        >
                                            <x-icon name="pencil" class="h-4 w-4" />
                                        </a>
                                        <button
                                            type="button"
                                            class="icon-button"
                                            aria-label="Delete customer"
                                            data-open-modal="delete-customer-{{ $customer->id }}-modal"
                                        >
                                            <x-icon name="trash" class="h-4 w-4" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-10 text-center text-sm text-slate-500">No customers yet. Add your first customer profile.</td>
                            </tr>
@endforelse
                    </tbody>
                </table>
            </div>
        </section>

    @foreach ($customers as $customer)
        <div class="app-modal hidden" data-modal="delete-customer-{{ $customer->id }}-modal">
            <div class="app-modal-card max-w-lg">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <h3 class="text-2xl font-bold text-slate-900">Are you sure?</h3>
                        <p class="mt-2 text-sm text-slate-500">You are about to delete this Customer.</p>
                    </div>
                    <button type="button" class="icon-button" data-close-modal="delete-customer-{{ $customer->id }}-modal" aria-label="Cancel delete customer">
                        <x-icon name="x" class="h-4 w-4" />
                    </button>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" class="ghost-button" data-close-modal="delete-customer-{{ $customer->id }}-modal">Cancel</button>
                    <form method="POST" action="{{ route('customers.destroy', $customer) }}">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="danger-button">
                            <x-icon name="trash" class="h-4 w-4" />
                            <span>Yes, Delete</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    @endforeach

    <div class="app-modal hidden" data-modal="create-customer-modal">
        <div class="app-modal-card">
            <div class="flex items-center justify-between gap-3">
                <h3 class="text-2xl font-bold text-slate-900">Create Customer</h3>
                <button type="button" class="icon-button" data-close-modal="create-customer-modal">
                    <x-icon name="x" class="h-4 w-4" />
                </button>
            </div>

            <form method="POST" action="{{ route('customers.store') }}" class="mt-6 space-y-4" enctype="multipart/form-data" data-auth-phone-form>
                @csrf
                <input type="hidden" name="phone" value="{{ old('phone') }}" data-auth-phone-full>
                <input type="hidden" name="phone_country" value="{{ old('phone_country', 'ph') }}" data-auth-phone-country>
                <input type="hidden" name="phone_dial_code" value="{{ old('phone_dial_code', '+63') }}" data-auth-phone-dial-code>

                <label class="part-upload-card customer-upload-card">
                    <span class="part-upload-preview customer-upload-preview">
                        <img src="" alt="Selected customer profile preview" class="hidden" data-image-preview="create-customer-profile">
                        <span data-image-preview-placeholder="create-customer-profile">
                            <x-icon name="camera" class="h-8 w-8" />
                        </span>
                    </span>
                    <span class="part-upload-content">
                        <span class="part-upload-title">Choose Customer Profile Photo</span>
                        <span class="part-upload-note">PNG, JPG, WEBP up to 2MB.</span>
                    </span>
                    <input type="file" name="profile_photo" accept="image/*" class="sr-only" data-image-preview-input="create-customer-profile">
                </label>

                <div class="grid gap-4 md:grid-cols-2">
                    <label class="form-field">
                        <span class="muted-label">Full Name</span>
                        <input type="text" name="name" class="input-shell" required>
                    </label>
                    <label class="form-field">
                        <span class="muted-label">Email</span>
                        <input type="email" name="email" class="input-shell" required>
                    </label>
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                    <label class="form-field">
                        <span class="muted-label">Phone</span>
                        <input type="tel" class="input-shell" inputmode="numeric" autocomplete="tel-national" data-auth-phone-input>
                        <span class="text-xs font-semibold text-rose-600 hidden" data-auth-phone-error></span>
                    </label>
                    <label class="form-field">
                        <span class="muted-label">Address</span>
                        <input type="text" name="address" class="input-shell" required>
                    </label>
                </div>

                <label class="form-field">
                    <span class="muted-label">Notes</span>
                    <textarea name="notes" rows="4" class="input-shell"></textarea>
                </label>

                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" class="ghost-button" data-close-modal="create-customer-modal">Cancel</button>
                    <button type="submit" class="primary-button">
                        <x-icon name="plus" class="h-4 w-4" />
                        <span>Create Customer</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    @if ($editingCustomer)
        <div class="app-modal" data-modal="edit-customer-modal">
            <div class="app-modal-card">
                <div class="flex items-center justify-between gap-3">
                    <h3 class="text-2xl font-bold text-slate-900">Edit Customer</h3>
                    <a href="{{ route('customers', ['customer' => $editingCustomer->id]) }}" class="icon-button" aria-label="Close edit customer">
                        <x-icon name="x" class="h-4 w-4" />
                    </a>
                </div>

                <form method="POST" action="{{ route('customers.update', $editingCustomer) }}" class="mt-6 space-y-4" enctype="multipart/form-data" data-auth-phone-form>
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="phone" value="{{ old('phone', $editingCustomer->phone) }}" data-auth-phone-full>
                    <input type="hidden" name="phone_country" value="{{ old('phone_country', 'ph') }}" data-auth-phone-country>
                    <input type="hidden" name="phone_dial_code" value="{{ old('phone_dial_code', '+63') }}" data-auth-phone-dial-code>

                    <label class="part-upload-card customer-upload-card">
                        <span class="part-upload-preview customer-upload-preview">
                            @if ($editingCustomer->profile_photo_path)
                                <img
                                    src="{{ \Illuminate\Support\Facades\Storage::url($editingCustomer->profile_photo_path) }}"
                                    alt="{{ $editingCustomer->name }} profile photo preview"
                                    data-image-preview="edit-customer-profile"
                                >
                                <span class="hidden" data-image-preview-placeholder="edit-customer-profile">
                                    <x-icon name="camera" class="h-8 w-8" />
                                </span>
                            @else
                                <img src="" alt="Selected customer profile preview" class="hidden" data-image-preview="edit-customer-profile">
                                <span data-image-preview-placeholder="edit-customer-profile">
                                    <x-icon name="camera" class="h-8 w-8" />
                                </span>
                            @endif
                        </span>
                        <span class="part-upload-content">
                            <span class="part-upload-title">Choose customer profile photo</span>
                            <span class="part-upload-note">Circular preview. PNG, JPG, WEBP up to 2MB.</span>
                        </span>
                        <input type="file" name="profile_photo" accept="image/*" class="sr-only" data-image-preview-input="edit-customer-profile">
                    </label>

                    <div class="grid gap-4 md:grid-cols-2">
                        <label class="form-field">
                            <span class="muted-label">Full Name</span>
                            <input type="text" name="name" value="{{ old('name', $editingCustomer->name) }}" class="input-shell" required>
                        </label>
                        <label class="form-field">
                            <span class="muted-label">Email</span>
                            <input type="email" name="email" value="{{ old('email', $editingCustomer->email) }}" class="input-shell" required>
                        </label>
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        <label class="form-field">
                            <span class="muted-label">Phone</span>
                            <input type="tel" class="input-shell" inputmode="numeric" autocomplete="tel-national" data-auth-phone-input>
                            <span class="text-xs font-semibold text-rose-600 hidden" data-auth-phone-error></span>
                        </label>
                        <label class="form-field">
                            <span class="muted-label">Address</span>
                            <input type="text" name="address" value="{{ old('address', $editingCustomer->address) }}" class="input-shell" required>
                        </label>
                    </div>

                    <label class="form-field">
                        <span class="muted-label">Notes</span>
                        <textarea name="notes" rows="4" class="input-shell">{{ old('notes', $editingCustomer->notes) }}</textarea>
                    </label>

                    <div class="flex justify-end gap-3 pt-2">
                        <a href="{{ route('customers', ['customer' => $editingCustomer->id]) }}" class="ghost-button">Cancel</a>
                        <button type="submit" class="primary-button">
                            <x-icon name="check-circle" class="h-4 w-4" />
                            <span>Save Customer</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    @if ($historyCustomer)
        <div class="app-modal" data-modal="customer-history-modal">
            <div class="app-modal-card max-w-5xl">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div class="flex items-center gap-4">
                        @php
                            $historyInitials = collect(explode(' ', (string) $historyCustomer->name))
                                ->filter()
                                ->map(fn (string $part): string => mb_substr($part, 0, 1))
                                ->take(2)
                                ->implode('');
                        @endphp
                        @if ($historyCustomer->profile_photo_path)
                            <img
                                src="{{ \Illuminate\Support\Facades\Storage::url($historyCustomer->profile_photo_path) }}"
                                alt="{{ $historyCustomer->name }} profile photo"
                                class="h-16 w-16 rounded-full object-cover"
                            >
                        @else
                            <span class="inline-flex h-16 w-16 items-center justify-center rounded-full bg-slate-900 text-lg font-black text-white">
                                {{ strtoupper($historyInitials ?: 'CU') }}
                            </span>
                        @endif
                        <div>
                            <h3 class="text-2xl font-bold tracking-tight text-slate-900">{{ $historyCustomer->name }} History</h3>
                            <p class="text-sm text-slate-500">{{ $historyCustomer->phone ?: 'No phone' }} &middot; {{ $historyCustomer->email ?: 'No email' }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <x-badge :tone="$historyCustomer->active_job_orders_count > 0 ? 'warning' : 'success'">
                            {{ $historyCustomer->active_job_orders_count }} active jobs
                        </x-badge>
                        <a href="{{ route('customers', ['customer' => $historyCustomer->id]) }}" class="icon-button" aria-label="Close customer history">
                            <x-icon name="x" class="h-4 w-4" />
                        </a>
                    </div>
                </div>

                <div class="mt-6 max-h-[65vh] space-y-3 overflow-y-auto pr-1">
                    @forelse ($historyJobs as $job)
                        @php
                            $tone = match ($job->status) {
                                'completed' => 'success',
                                'in_progress' => 'neutral',
                                'cancelled' => 'danger',
                                default => 'warning',
                            };
                        @endphp
                        <article class="rounded-2xl border border-slate-100 bg-white px-4 py-3">
                            <div class="flex flex-wrap items-start justify-between gap-3">
                                <div>
                                    <p class="font-semibold text-slate-900">{{ $job->order_number }} &middot; {{ $job->vehicle }}</p>
                                    <p class="mt-1 text-sm text-slate-500">{{ $job->concern }}</p>
                                    @if ($job->notes)
                                        <p class="mt-1 text-xs text-slate-400">{{ $job->notes }}</p>
                                    @endif
                                </div>
                                <div class="text-right">
                                    <x-badge :tone="$tone">{{ str_replace('_', ' ', ucfirst($job->status)) }}</x-badge>
                                    <p class="mt-2 text-xs font-semibold text-slate-400">
                                        {{ ($job->completed_at ?: $job->scheduled_for ?: $job->created_at)?->timezone('Asia/Manila')->format('M d, Y') }}
                                    </p>
                                </div>
                            </div>
                            <div class="mt-3 flex flex-wrap gap-3 text-xs font-semibold uppercase tracking-[0.14em] text-slate-400">
                                <span>Service: {{ $job->concern }}</span>
                                <span>Amount: PHP {{ number_format((float) $job->estimated_cost, 2) }}</span>
                            </div>
                        </article>
                    @empty
                        <p class="rounded-2xl border border-slate-100 bg-slate-50 px-4 py-4 text-sm text-slate-500">
                            No job order history for this customer yet.
                        </p>
                    @endforelse
                </div>
            </div>
        </div>
    @endif
@endsection
