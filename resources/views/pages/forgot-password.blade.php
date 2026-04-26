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

                <h2 class="auth-aside-title">Reset your password.</h2>
                <p class="auth-aside-text">
                    Enter your email and we will send a reset link to get you back to your dashboard.
                </p>
            </section>

            <section class="auth-panel">
                <span class="auth-mark">
                    <x-icon name="lock" class="h-10 w-10" />
                </span>

                <h1 class="mt-7 text-center text-4xl font-black tracking-tight text-slate-900 sm:text-5xl">Forgot Password</h1>
                <p class="mt-3 text-center text-base text-slate-500 sm:text-lg">Use your account email to request a reset link.</p>

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

                <form action="{{ route('password.email') }}" method="POST" class="mt-8 space-y-5">
                    @csrf

                    <label class="form-field gap-2.5">
                        <span class="auth-label">Email Address</span>
                        <div class="auth-input-wrap">
                            <x-icon name="user" class="h-5 w-5 text-slate-500" />
                            <input type="email" name="email" class="auth-input" placeholder="you@example.com" value="{{ old('email') }}" required>
                        </div>
                    </label>

                    <button type="submit" class="auth-submit mt-1">
                        <span>Send Reset Link</span>
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
