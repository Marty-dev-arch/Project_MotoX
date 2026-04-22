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

                <h2 class="auth-aside-title">Welcome back.</h2>
                <p class="auth-aside-text">
                    Access your Motorshop dashboard, inventory, and work orders in one secure workspace.
                </p>

                <div class="auth-benefit-list">
                    <div class="auth-benefit-item">
                        <span class="auth-benefit-icon"><x-icon name="check-circle" class="h-4 w-4" /></span>
                        <span>Real-time inventory tracking</span>
                    </div>
                    <div class="auth-benefit-item">
                        <span class="auth-benefit-icon"><x-icon name="check-circle" class="h-4 w-4" /></span>
                        <span>Structured work-order flow</span>
                    </div>
                    <div class="auth-benefit-item">
                        <span class="auth-benefit-icon"><x-icon name="check-circle" class="h-4 w-4" /></span>
                        <span>Customer and vehicle records</span>
                    </div>
                </div>
            </section>

            <section class="auth-panel">
                <span class="auth-mark">
                    <x-icon name="wrench" class="h-10 w-10" />
                </span>

                <h1 class="mt-7 text-center text-4xl font-black tracking-tight text-slate-900 sm:text-5xl">Log In</h1>
                <p class="mt-3 text-center text-base text-slate-500 sm:text-lg">Sign in with your registered MotoX account.</p>

                @if ($errors->any())
                    <div class="auth-alert mt-7">
                        <p class="font-semibold">Unable to sign in.</p>
                        <ul class="mt-2 list-disc space-y-1 pl-5 text-sm">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('login.store') }}" method="POST" class="mt-8 space-y-5">
                    @csrf

                    <label class="form-field gap-2.5">
                        <span class="auth-label">Email Address</span>
                        <div class="auth-input-wrap">
                            <x-icon name="user" class="h-5 w-5 text-slate-500" />
                            <input type="email" name="email" class="auth-input" placeholder="you@example.com" value="{{ old('email') }}" required>
                        </div>
                    </label>

                    <label class="form-field gap-2.5">
                        <span class="auth-label">Password</span>
                        <div class="auth-input-wrap">
                            <x-icon name="lock" class="h-5 w-5 text-slate-500" />
                            <input id="login-password" type="password" name="password" class="auth-input auth-input-password" placeholder="Enter password" required>
                            <button
                                type="button"
                                class="password-toggle"
                                data-password-toggle
                                data-target="login-password"
                                aria-label="Show password"
                            >
                                <x-icon name="eye" class="password-toggle-icon" data-password-icon="show" />
                                <x-icon name="eye-off" class="password-toggle-icon hidden" data-password-icon="hide" />
                            </button>
                        </div>
                    </label>

                    <button type="submit" class="auth-submit mt-1">
                        <span>Continue</span>
                        <x-icon name="chevron-right" class="h-5 w-5" />
                    </button>
                </form>

                <div class="my-6 flex items-center gap-3">
                    <span class="h-px flex-1 bg-slate-200"></span>
                    <span class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-400">or</span>
                    <span class="h-px flex-1 bg-slate-200"></span>
                </div>

                <a href="{{ route('google.redirect') }}" class="google-login-btn">
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                        <path fill="#EA4335" d="M12 10.2v3.92h5.45c-.24 1.26-.95 2.33-2.03 3.06l3.28 2.54c1.91-1.75 3.01-4.34 3.01-7.42 0-.73-.06-1.43-.19-2.1H12Z"/>
                        <path fill="#34A853" d="M12 22c2.72 0 5-.9 6.67-2.42l-3.28-2.54c-.91.62-2.08.98-3.39.98-2.61 0-4.82-1.76-5.61-4.12H3.02v2.59A10 10 0 0 0 12 22Z"/>
                        <path fill="#4A90E2" d="M6.39 13.9a6.02 6.02 0 0 1 0-3.8V7.51H3.02a10 10 0 0 0 0 8.98l3.37-2.59Z"/>
                        <path fill="#FBBC05" d="M12 5.98c1.48 0 2.8.51 3.84 1.52l2.88-2.88A9.64 9.64 0 0 0 12 2a10 10 0 0 0-8.98 5.51l3.37 2.59c.8-2.36 3-4.12 5.61-4.12Z"/>
                    </svg>
                    Continue with Google
                </a>

                <p class="mt-8 text-center text-base text-slate-500">
                    Need an account?
                    <a href="{{ route('register') }}" class="font-semibold text-brand-700 transition hover:text-brand-800">Register</a>
                </p>
            </section>
        </div>
    </div>
@endsection
