@extends('layouts.app')

@section('content')
    <section class="space-y-6">
        @if (session('status'))
            <div class="auth-alert auth-alert-{{ session('status_tone', 'success') }}">
                <p class="font-semibold">{{ session('status') }}</p>
            </div>
        @endif

        <div>
            <h1 class="text-4xl font-black tracking-tight text-slate-900">{{ $heading }}</h1>
            <p class="mt-2 text-sm text-slate-500">{{ $subheading }}</p>
        </div>

        <form method="POST" action="{{ route('settings.update') }}" class="space-y-6" enctype="multipart/form-data">
            @csrf

            @php
                $ownerName = old('owner_name', $profile['owner_name']);
                $username = old('username', $profile['username']);
                $avatarInitials = collect(explode(' ', (string) $ownerName))
                    ->filter()
                    ->map(fn (string $part): string => mb_substr($part, 0, 1))
                    ->take(2)
                    ->implode('');
            @endphp

            <section id="settings" class="panel-card scroll-mt-28 p-6">
                <h2 class="text-2xl font-bold text-slate-900" data-i18n="Shop Preferences">Shop Preferences</h2>
                <p class="mt-1 text-sm text-slate-500">Edit your shop settings based on what users need.</p>

                <div class="mt-6 grid gap-6 lg:grid-cols-[180px_1fr] lg:items-start">
                    <div class="shop-avatar-panel">
                        <label class="profile-avatar-control" title="Upload profile photo">
                            @if ($profile['avatar_url'])
                                <img
                                    src="{{ $profile['avatar_url'] }}"
                                    alt="{{ $ownerName }}"
                                    class="profile-avatar-image"
                                    data-image-preview="profile-avatar"
                                >
                                <span class="profile-avatar-fallback hidden" data-profile-avatar-initials>{{ strtoupper($avatarInitials ?: 'MX') }}</span>
                            @else
                                <img
                                    src=""
                                    alt="{{ $ownerName }}"
                                    class="profile-avatar-image hidden"
                                    data-image-preview="profile-avatar"
                                >
                                <span class="profile-avatar-fallback" data-profile-avatar-initials>{{ strtoupper($avatarInitials ?: 'MX') }}</span>
                            @endif
                            <input
                                type="file"
                                name="avatar"
                                accept="image/*"
                                class="sr-only"
                                data-image-preview-input="profile-avatar"
                            >
                            <span class="profile-avatar-camera">
                                <x-icon name="camera" class="h-4 w-4" />
                            </span>
                        </label>
                        <p class="mt-4 text-center text-xs text-slate-500">PNG, JPG, WEBP up to 2MB.</p>
                    </div>

                    <div>
                        <div class="grid gap-4 md:grid-cols-2">
                            <label class="form-field">
                                <span class="muted-label">Display Name</span>
                                <input
                                    type="text"
                                    name="owner_name"
                                    value="{{ $ownerName }}"
                                    class="input-shell"
                                    data-profile-name-input
                                    required
                                >
                            </label>

                            <label class="form-field">
                                <span class="muted-label">Username</span>
                                <input
                                    type="text"
                                    name="username"
                                    value="{{ $username }}"
                                    class="input-shell"
                                    autocomplete="username"
                                    required
                                >
                            </label>

                            <label class="form-field">
                                <span class="muted-label">Shop Name</span>
                                <input
                                    type="text"
                                    name="shop_name"
                                    value="{{ old('shop_name', $profile['shop_name']) }}"
                                    class="input-shell"
                                    required
                                >
                            </label>

                            <label class="form-field">
                                <span class="muted-label">Contact Number</span>
                                <input
                                    type="text"
                                    name="contact_number"
                                    value="{{ old('contact_number', $profile['contact_number']) }}"
                                    class="input-shell"
                                >
                            </label>
                        </div>

                        <div class="mt-4 grid gap-4 md:grid-cols-4">
                            <label class="form-field">
                                <span class="muted-label">Default Labor Rate (PHP/hr)</span>
                                <input
                                    type="number"
                                    name="default_labor_rate"
                                    min="0"
                                    step="0.01"
                                    value="{{ old('default_labor_rate', $preferences['default_labor_rate']) }}"
                                    class="input-shell"
                                    required
                                >
                            </label>

                            <label class="form-field">
                                <span class="muted-label">Currency</span>
                                <select name="currency_code" class="input-shell" required>
                                    @foreach (['PHP', 'USD', 'EUR', 'GBP'] as $currency)
                                        <option value="{{ $currency }}" @selected(old('currency_code', $preferences['currency_code']) === $currency)>
                                            {{ $currency }}
                                        </option>
                                    @endforeach
                                </select>
                            </label>

                            <label class="form-field">
                                <span class="muted-label">Auto-Assign New Job Orders</span>
                                <select name="auto_assign_job_orders" class="input-shell">
                                    <option value="1" @selected((string) old('auto_assign_job_orders', $preferences['auto_assign_job_orders'] ? '1' : '0') === '1')>Enabled</option>
                                    <option value="0" @selected((string) old('auto_assign_job_orders', $preferences['auto_assign_job_orders'] ? '1' : '0') === '0')>Disabled</option>
                                </select>
                            </label>

                            <label class="form-field">
                                <span class="muted-label" data-i18n="Language Preference">Language Preference</span>
                                <select class="input-shell" data-language-preference>
                                    <option value="en-US" data-i18n="US English">US English</option>
                                    <option value="tl-PH" data-i18n="Philippines - Tagalog">Philippines - Tagalog</option>
                                </select>
                            </label>
                        </div>
                    </div>

                    <input
                        type="email"
                        name="email"
                        value="{{ old('email', $profile['email']) }}"
                        class="sr-only"
                        tabindex="-1"
                        required
                    >
                </div>
            </section>

            <section>
                <article id="notifications" class="panel-card scroll-mt-28 p-6">
                    <div class="flex items-center gap-3">
                        <span class="icon-chip appearance-card-icon">
                            <x-icon name="bell" class="h-5 w-5" />
                        </span>
                        <div>
                            <h2 class="text-2xl font-bold text-slate-900">Notifications</h2>
                            <p class="mt-1 text-sm text-slate-500">Notification preferences for alerts and updates.</p>
                        </div>
                    </div>

                    <div class="mt-6 space-y-3">
                        <div class="detail-card">
                            <label class="flex items-center justify-between gap-3">
                                <div>
                                    <p class="text-base font-semibold text-slate-900">Low-stock alerts</p>
                                    <p class="mt-1 text-sm text-slate-500">Shown in dashboard and inventory in real time.</p>
                                </div>
                                <select name="notify_low_stock_alerts" class="input-shell max-w-[140px]">
                                    <option value="1" @selected((string) old('notify_low_stock_alerts', $notifications['notify_low_stock_alerts'] ? '1' : '0') === '1')>Enabled</option>
                                    <option value="0" @selected((string) old('notify_low_stock_alerts', $notifications['notify_low_stock_alerts'] ? '1' : '0') === '0')>Disabled</option>
                                </select>
                            </label>
                        </div>
                        <div class="detail-card">
                            <label class="flex items-center justify-between gap-3">
                                <div>
                                    <p class="text-base font-semibold text-slate-900">Job order reminders</p>
                                    <p class="mt-1 text-sm text-slate-500">Highlighted for pending and in-progress work.</p>
                                </div>
                                <select name="notify_job_order_updates" class="input-shell max-w-[140px]">
                                    <option value="1" @selected((string) old('notify_job_order_updates', $notifications['notify_job_order_updates'] ? '1' : '0') === '1')>Enabled</option>
                                    <option value="0" @selected((string) old('notify_job_order_updates', $notifications['notify_job_order_updates'] ? '1' : '0') === '0')>Disabled</option>
                                </select>
                            </label>
                        </div>
                        <div class="detail-card">
                            <label class="flex items-center justify-between gap-3">
                                <div>
                                    <p class="text-base font-semibold text-slate-900">Billing updates</p>
                                    <p class="mt-1 text-sm text-slate-500">Notify when invoices become due or overdue.</p>
                                </div>
                                <select name="notify_billing_updates" class="input-shell max-w-[140px]">
                                    <option value="1" @selected((string) old('notify_billing_updates', $notifications['notify_billing_updates'] ? '1' : '0') === '1')>Enabled</option>
                                    <option value="0" @selected((string) old('notify_billing_updates', $notifications['notify_billing_updates'] ? '1' : '0') === '0')>Disabled</option>
                                </select>
                            </label>
                        </div>
                    </div>
                </article>
            </section>

            <div class="flex justify-end">
                <button type="submit" class="primary-button">
                    <span data-i18n="Save Settings">Save Settings</span>
                </button>
            </div>
        </form>
    </section>
@endsection
