@extends('layouts.auth')


{{-- Purpose: Renders the forgot password request page. --}}
@section('content')
    <div class="auth-page-shell">
        <div class="auth-layout">
            <section class="auth-aside">
                <div class="auth-aside-copy">
                    <h2 class="auth-aside-title">Reset your password.</h2>
                    <p class="auth-aside-text">
                        Enter your email and we will send a one-time password (OTP) to verify your account.
                    </p>
                </div>
            </section>

            <section class="auth-panel">
                <h1 class="auth-title">Forgot Password</h1>
                <p class="auth-subtitle">Use your account email to request a secure OTP code.</p>

                @if (session('status'))
                    <div class="mt-7 rounded-2xl border border-emerald-200/80 bg-emerald-50/90 px-4 py-3 text-emerald-700">
                        <p class="font-semibold">{{ session('status') }}</p>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="auth-alert mt-7">
                        <p class="font-semibold">Unable to send reset link.</p>
                        <ul class="mt-2 list-disc space-y-1 pl-5 text-sm">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('password.email') }}" method="POST" class="auth-form-stack">
                    @csrf

                    <label class="form-field">
                        <span class="auth-label">Email Address</span>
                        <div class="auth-input-wrap">
                            <x-icon name="user" class="h-5 w-5 text-slate-500" />
                            <input type="email" name="email" class="auth-input" placeholder="you@example.com" value="{{ old('email') }}" required>
                        </div>
                    </label>

                    <button type="submit" class="auth-submit mt-1">
                        <span>Send OTP</span>
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
