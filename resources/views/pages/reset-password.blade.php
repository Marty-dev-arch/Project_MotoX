@extends('layouts.auth')


{{-- Purpose: Renders the reset password page. --}}
@section('content')
    <div class="auth-page-shell">
        <div class="auth-layout">
            <section class="auth-aside">
                <div class="auth-aside-copy">
                    <h2 class="auth-aside-title">Set a new password.</h2>
                    <p class="auth-aside-text">
                        Create a strong password, then sign in again to continue managing your workshop.
                    </p>
                </div>
            </section>

            <section class="auth-panel">
                <h1 class="auth-title">Reset Password</h1>
                <p class="auth-subtitle">Enter your email and choose a new password.</p>

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

                <form action="{{ route('password.update') }}" method="POST" class="auth-form-stack" data-auth-password-form>
                    @csrf

                    <label class="form-field">
                        <span class="auth-label">Email Address</span>
                        <div class="auth-input-wrap">
                            <x-icon name="user" class="h-5 w-5 text-slate-500" />
                            <input type="email" name="email" class="auth-input" placeholder="you@example.com" value="{{ old('email', $email ?? '') }}" required readonly>
                        </div>
                    </label>

                    <label class="form-field">
                        <span class="auth-label">New Password</span>
                        <div class="auth-input-wrap">
                            <x-icon name="lock" class="h-5 w-5 text-slate-500" />
                            <input id="reset-password" type="password" name="password" class="auth-input auth-input-password" placeholder="8-16 characters" required minlength="8" maxlength="16" data-auth-password aria-describedby="reset-password-feedback">
                            <x-password-toggle target="reset-password" />
                        </div>
                        <div class="auth-password-feedback" id="reset-password-feedback" data-auth-password-feedback>
                            <p class="auth-password-help">Minimum 8 characters</p>
                            <p class="auth-password-message" data-auth-password-strength-message>Password must be at least 8 characters.</p>
                            <div class="auth-strength-meter" aria-hidden="true">
                                <span data-auth-password-strength-fill></span>
                            </div>
                            <p class="auth-strength-label" data-auth-password-strength-label>Weak</p>
                        </div>
                    </label>

                    <label class="form-field">
                        <span class="auth-label">Confirm Password</span>
                        <div class="auth-input-wrap">
                            <x-icon name="lock" class="h-5 w-5 text-slate-500" />
                            <input id="reset-password-confirmation" type="password" name="password_confirmation" class="auth-input auth-input-password" placeholder="Repeat new password" required minlength="8" maxlength="16" data-auth-password-confirmation>
                            <x-password-toggle target="reset-password-confirmation" />
                        </div>
                        <p class="auth-inline-error hidden" data-auth-password-match>Password confirmation does not match.</p>
                    </label>

                    <button type="submit" class="auth-submit mt-1">
                        <span>Update Password</span>
                        <x-icon name="chevron-right" class="h-4 w-4" />
                    </button>
                </form>

                <p class="mt-8 text-center text-base text-slate-500">
                    Back to
                    <a href="{{ route('login') }}" class="font-semibold text-brand-700 transition hover:text-brand-600">Log In</a>
                </p>
            </section>
        </div>
    </div>
@endsection
