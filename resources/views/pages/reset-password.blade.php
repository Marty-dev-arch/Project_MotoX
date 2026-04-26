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

                <h2 class="auth-aside-title">Set a new password.</h2>
                <p class="auth-aside-text">
                    Create a strong password, then sign in again to continue managing your workshop.
                </p>
            </section>

            <section class="auth-panel">
                <span class="auth-mark">
                    <x-icon name="lock" class="h-10 w-10" />
                </span>

                <h1 class="mt-7 text-center text-4xl font-black tracking-tight text-slate-900 sm:text-5xl">Reset Password</h1>
                <p class="mt-3 text-center text-base text-slate-500 sm:text-lg">Enter your email and choose a new password.</p>

                @if ($errors->any())
                    <div class="auth-alert mt-7">
                        <p class="font-semibold">Unable to reset password.</p>
                        <ul class="mt-2 list-disc space-y-1 pl-5 text-sm">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('password.update') }}" method="POST" class="mt-8 space-y-5">
                    @csrf
                    <input type="hidden" name="token" value="{{ $token }}">

                    <label class="form-field gap-2.5">
                        <span class="auth-label">Email Address</span>
                        <div class="auth-input-wrap">
                            <x-icon name="user" class="h-5 w-5 text-slate-500" />
                            <input type="email" name="email" class="auth-input" placeholder="you@example.com" value="{{ old('email', $email ?? '') }}" required>
                        </div>
                    </label>

                    <label class="form-field gap-2.5">
                        <span class="auth-label">New Password</span>
                        <div class="auth-input-wrap">
                            <x-icon name="lock" class="h-5 w-5 text-slate-500" />
                            <input id="reset-password" type="password" name="password" class="auth-input auth-input-password" placeholder="At least 8 characters" required>
                            <button
                                type="button"
                                class="password-toggle"
                                data-password-toggle
                                data-target="reset-password"
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
                            <input id="reset-password-confirmation" type="password" name="password_confirmation" class="auth-input auth-input-password" placeholder="Repeat new password" required>
                            <button
                                type="button"
                                class="password-toggle"
                                data-password-toggle
                                data-target="reset-password-confirmation"
                                aria-label="Show password"
                            >
                                <x-icon name="eye" class="password-toggle-icon" data-password-icon="show" />
                                <x-icon name="eye-off" class="password-toggle-icon hidden" data-password-icon="hide" />
                            </button>
                        </div>
                    </label>

                    <button type="submit" class="auth-submit mt-1">
                        <span>Update Password</span>
                        <x-icon name="chevron-right" class="h-5 w-5" />
                    </button>
                </form>

                <p class="mt-8 text-center text-base text-slate-500">
                    Back to
                    <a href="{{ route('login') }}" class="font-semibold text-brand-700 transition hover:text-brand-800">Log In</a>
                </p>
            </section>
        </div>
    </div>
@endsection
