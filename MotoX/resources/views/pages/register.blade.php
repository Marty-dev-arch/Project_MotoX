@extends('layouts.auth')

@section('content')
    <div class="auth-page-shell">
        <div class="auth-layout">
            <section class="auth-aside">
                <a href="{{ route('landing') }}" class="landing-brand">
                    <span class="landing-brand-icon">
                        <x-icon name="car" class="h-5 w-5" />
                    </span>
                    <span class="landing-brand-name">MotoX</span>
                </a>

                <h2 class="auth-aside-title">Create your Motorshop Account.</h2>
                <p class="auth-aside-text">
                    Set up your shop profile and start using MotoX with your own Shop Tracking records.
                </p>

                <div class="auth-benefit-list">
                    <div class="auth-benefit-item">
                        <span class="auth-benefit-icon"><x-icon name="inventory" class="h-4 w-4" /></span>
                        <span>Inventory with stock movement history</span>
                    </div>
                    <div class="auth-benefit-item">
                        <span class="auth-benefit-icon"><x-icon name="wrench" class="h-4 w-4" /></span>
                        <span>Work orders with status tracking</span>
                    </div>
                    <div class="auth-benefit-item">
                        <span class="auth-benefit-icon"><x-icon name="customers" class="h-4 w-4" /></span>
                        <span>Customer records in one place</span>
                    </div>
                </div>
            </section>

            <section class="auth-panel">
                <span class="auth-mark">
                    <x-icon name="id-card" class="h-10 w-10" />
                </span>

                <h1 class="mt-7 text-center text-4xl font-black tracking-tight text-slate-900 sm:text-5xl">Create Account</h1>
                <p class="mt-3 text-center text-base text-slate-500 sm:text-lg">Use your real shop details to register.</p>

                @if ($errors->any())
                    <div class="auth-alert mt-7">
                        <p class="font-semibold">Please fix the highlighted fields.</p>
                        <ul class="mt-2 list-disc space-y-1 pl-5 text-sm">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('register.store') }}" method="POST" class="mt-8 space-y-5">
                    @csrf

                    <label class="form-field gap-2.5">
                        <span class="auth-label">Shop Name</span>
                        <div class="auth-input-wrap">
                            <x-icon name="car" class="h-5 w-5 text-slate-500" />
                            <input type="text" name="shop_name" class="auth-input" placeholder="e.g. MotoX Garage" value="{{ old('shop_name') }}" required>
                        </div>
                    </label>

                    <label class="form-field gap-2.5">
                        <span class="auth-label">Owner Name</span>
                        <div class="auth-input-wrap">
                            <x-icon name="user" class="h-5 w-5 text-slate-500" />
                            <input type="text" name="owner_name" class="auth-input" placeholder="Full name" value="{{ old('owner_name') }}" required>
                        </div>
                    </label>

                    <div class="auth-grid">
                        <label class="form-field gap-2.5">
                            <span class="auth-label">Email Address</span>
                            <div class="auth-input-wrap">
                                <x-icon name="user" class="h-5 w-5 text-slate-500" />
                                <input type="email" name="email" class="auth-input" placeholder="you@example.com" value="{{ old('email') }}" required>
                            </div>
                        </label>

                        <label class="form-field gap-2.5">
                            <span class="auth-label">Contact Number</span>
                            <div class="auth-input-wrap">
                                <x-icon name="phone" class="h-5 w-5 text-slate-500" />
                                <input type="text" name="contact_number" class="auth-input" placeholder="+63 912 345 6789" value="{{ old('contact_number') }}">
                            </div>
                        </label>
                    </div>

                    <div class="auth-grid">
                        <label class="form-field gap-2.5">
                            <span class="auth-label">Password</span>
                            <div class="auth-input-wrap">
                                <x-icon name="lock" class="h-5 w-5 text-slate-500" />
                                <input id="register-password" type="password" name="password" class="auth-input auth-input-password" placeholder="At least 8 characters" required>
                                <button
                                    type="button"
                                    class="password-toggle"
                                    data-password-toggle
                                    data-target="register-password"
                                    aria-label="Show password"
                                >
                                    <x-icon name="eye" class="password-toggle-icon" data-password-icon="show" />
                                    <x-icon name="eye-off" class="password-toggle-icon hidden" data-password-icon="hide" />
                                </button>
                            </div>
                        </label>

                        <label class="form-field gap-2.5">
                            <span class="auth-label">Confirm Password</span>
                            <div class="auth-input-wrap">
                                <x-icon name="lock" class="h-5 w-5 text-slate-500" />
                                <input id="register-password-confirmation" type="password" name="password_confirmation" class="auth-input auth-input-password" placeholder="Repeat password" required>
                                <button
                                    type="button"
                                    class="password-toggle"
                                    data-password-toggle
                                    data-target="register-password-confirmation"
                                    aria-label="Show password"
                                >
                                    <x-icon name="eye" class="password-toggle-icon" data-password-icon="show" />
                                    <x-icon name="eye-off" class="password-toggle-icon hidden" data-password-icon="hide" />
                                </button>
                            </div>
                        </label>
                    </div>

                    <button type="submit" class="auth-submit mt-1">
                        <span>Register Account</span>
                        <x-icon name="chevron-right" class="h-5 w-5" />
                    </button>
                </form>

                <p class="mt-8 text-center text-base text-slate-500">
                    Already have an account?
                    <a href="{{ route('login') }}" class="font-semibold text-brand-700 transition hover:text-brand-800">Log In</a>
                </p>
            </section>
        </div>
    </div>
@endsection
