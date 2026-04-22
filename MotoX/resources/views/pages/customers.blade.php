@extends('layouts.app')

@section('content')
    <section class="space-y-6">
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
                <p class="mt-2 text-4xl font-black tracking-tight text-slate-900">{{ number_format($stats['total']) }}</p>
            </article>
            <article class="panel-card p-5">
                <p class="muted-label">Active Jobs</p>
                <p class="mt-2 text-4xl font-black tracking-tight text-slate-900">{{ number_format($stats['active_jobs']) }}</p>
            </article>
            <article class="panel-card p-5">
                <p class="muted-label">New This Month</p>
                <p class="mt-2 text-4xl font-black tracking-tight text-slate-900">{{ number_format($stats['new_this_month']) }}</p>
            </article>
        </div>

        <div class="grid gap-6 xl:grid-cols-[0.9fr_1.1fr]">
            @php
                $isEditing = $editingCustomer !== null;
            @endphp

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

            <section class="table-shell">
                <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
                    <div>
                        <h3 class="text-2xl font-bold tracking-tight text-slate-900">Customer Directory</h3>
                        <p class="text-sm text-slate-500">{{ $customers->count() }} profiles</p>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="soft-table min-w-[860px]">
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
                                <tr @class(['bg-brand-50/40' => $isSelected])>
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
        </div>

        @if ($selectedCustomer)
            <section class="panel-card p-5 sm:p-6">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <h3 class="text-3xl font-black tracking-tight text-slate-900">{{ $selectedCustomer->name }}</h3>
                        <p class="mt-2 text-sm text-slate-500">{{ $selectedCustomer->address ?: 'No address provided.' }}</p>
                    </div>
                    <div class="flex gap-2">
                        @if ($selectedCustomer->email)
                            <x-badge tone="accent">{{ $selectedCustomer->email }}</x-badge>
                        @endif
                        @if ($selectedCustomer->phone)
                            <x-badge>{{ $selectedCustomer->phone }}</x-badge>
                        @endif
                    </div>
                </div>

                <div class="mt-6 grid gap-4 md:grid-cols-3">
                    <article class="detail-card">
                        <p class="muted-label">Total Job Orders</p>
                        <p class="mt-2 text-3xl font-black tracking-tight text-slate-900">{{ $selectedCustomer->job_orders_count }}</p>
                    </article>
                    <article class="detail-card">
                        <p class="muted-label">Active Jobs</p>
                        <p class="mt-2 text-3xl font-black tracking-tight text-slate-900">{{ $selectedCustomer->active_job_orders_count }}</p>
                    </article>
                    <article class="detail-card">
                        <p class="muted-label">Customer Since</p>
                        <p class="mt-2 text-3xl font-black tracking-tight text-slate-900">{{ $selectedCustomer->created_at->format('M Y') }}</p>
                    </article>
                </div>

                <div class="mt-6">
                    <h4 class="text-2xl font-bold text-slate-900">Recent Job Orders</h4>
                    <div class="mt-4 overflow-x-auto">
                        <table class="soft-table min-w-[760px]">
                            <thead>
                                <tr class="table-heading">
                                    <th>Order #</th>
                                    <th>Vehicle</th>
                                    <th>Status</th>
                                    <th>Estimated Cost</th>
                                    <th>Updated</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($recentJobs as $order)
                                    @php
                                        $tone = match ($order->status) {
                                            'completed' => 'success',
                                            'in_progress' => 'accent',
                                            'cancelled' => 'danger',
                                            default => 'warning',
                                        };
                                    @endphp
                                    <tr>
                                        <td class="font-semibold text-slate-900">{{ $order->order_number }}</td>
                                        <td>{{ $order->vehicle }}</td>
                                        <td><x-badge :tone="$tone">{{ str_replace('_', ' ', ucfirst($order->status)) }}</x-badge></td>
                                        <td>PHP {{ number_format((float) $order->estimated_cost, 2) }}</td>
                                        <td>{{ $order->updated_at->format('M d, Y h:i A') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="py-8 text-center text-sm text-slate-500">No job orders linked to this customer yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        @endif
    </section>
@endsection

