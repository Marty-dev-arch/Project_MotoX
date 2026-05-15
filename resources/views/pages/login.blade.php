@extends('layouts.auth')

@section('content')
    <div class="auth-page-shell">
        <div class="auth-page-inner">
            <a href="{{ route('landing') }}" class="auth-home-link">
                <x-icon name="chevron-left" class="h-4 w-4" />
                <span>Back to home</span>
            </a>

            <div class="auth-layout auth-layout-login" data-auth-route-switch>
                <section class="auth-panel auth-panel-form">
                <h1 class="auth-title">Sign In</h1>
                <p class="auth-subtitle">Use your registered MotoX account to continue.</p>

                @if (session('status'))
                    <div class="auth-alert auth-alert-success mt-6" data-registration-success="{{ session('status') }}">
                        <p class="font-semibold">{{ session('status') }}</p>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="auth-alert mt-6 text-center">
                        @foreach ($errors->all() as $error)
                            <p class="font-semibold">{{ $error }}</p>
                        @endforeach
                    </div>
                @endif

                <div class="auth-google-area">
                    @if ($googleOauthConfigured ?? false)
                        <a href="{{ route('google.redirect') }}" class="google-login-btn @if ($googleAccount ?? null) google-login-btn-account @endif">
                            @if ($googleAccount ?? null)
                                <span class="google-account-avatar">{{ $googleAccount['initial'] }}</span>
                                <span class="google-account-copy">
                                    <span class="google-account-title">Continue as <span class="google-account-name">{{ $googleAccount['name'] }}</span></span>
                                    <span class="google-account-email">{{ $googleAccount['email'] }}</span>
                                </span>
                                <svg class="google-account-mark" viewBox="0 0 24 24" aria-hidden="true">
                                    <path fill="#EA4335" d="M12 10.2v3.92h5.45c-.24 1.26-.95 2.33-2.03 3.06l3.28 2.54c1.91-1.75 3.01-4.34 3.01-7.42 0-.73-.06-1.43-.19-2.1H12Z"/>
                                    <path fill="#34A853" d="M12 22c2.72 0 5-.9 6.67-2.42l-3.28-2.54c-.91.62-2.08.98-3.39.98-2.61 0-4.82-1.76-5.61-4.12H3.02v2.59A10 10 0 0 0 12 22Z"/>
                                    <path fill="#4A90E2" d="M6.39 13.9a6.02 6.02 0 0 1 0-3.8V7.51H3.02a10 10 0 0 0 0 8.98l3.37-2.59Z"/>
                                    <path fill="#FBBC05" d="M12 5.98c1.48 0 2.8.51 3.84 1.52l2.88-2.88A9.64 9.64 0 0 0 12 2a10 10 0 0 0-8.98 5.51l3.37 2.59c.8-2.36 3-4.12 5.61-4.12Z"/>
                                </svg>
                            @else
                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                    <path fill="#EA4335" d="M12 10.2v3.92h5.45c-.24 1.26-.95 2.33-2.03 3.06l3.28 2.54c1.91-1.75 3.01-4.34 3.01-7.42 0-.73-.06-1.43-.19-2.1H12Z"/>
                                    <path fill="#34A853" d="M12 22c2.72 0 5-.9 6.67-2.42l-3.28-2.54c-.91.62-2.08.98-3.39.98-2.61 0-4.82-1.76-5.61-4.12H3.02v2.59A10 10 0 0 0 12 22Z"/>
                                    <path fill="#4A90E2" d="M6.39 13.9a6.02 6.02 0 0 1 0-3.8V7.51H3.02a10 10 0 0 0 0 8.98l3.37-2.59Z"/>
                                    <path fill="#FBBC05" d="M12 5.98c1.48 0 2.8.51 3.84 1.52l2.88-2.88A9.64 9.64 0 0 0 12 2a10 10 0 0 0-8.98 5.51l3.37 2.59c.8-2.36 3-4.12 5.61-4.12Z"/>
                                </svg>
                                Continue with Google
                            @endif
                        </a>
                    @else
                        <button type="button" class="google-login-btn opacity-60 cursor-not-allowed" disabled aria-disabled="true">
                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                <path fill="#EA4335" d="M12 10.2v3.92h5.45c-.24 1.26-.95 2.33-2.03 3.06l3.28 2.54c1.91-1.75 3.01-4.34 3.01-7.42 0-.73-.06-1.43-.19-2.1H12Z"/>
                                <path fill="#34A853" d="M12 22c2.72 0 5-.9 6.67-2.42l-3.28-2.54c-.91.62-2.08.98-3.39.98-2.61 0-4.82-1.76-5.61-4.12H3.02v2.59A10 10 0 0 0 12 22Z"/>
                                <path fill="#4A90E2" d="M6.39 13.9a6.02 6.02 0 0 1 0-3.8V7.51H3.02a10 10 0 0 0 0 8.98l3.37-2.59Z"/>
                                <path fill="#FBBC05" d="M12 5.98c1.48 0 2.8.51 3.84 1.52l2.88-2.88A9.64 9.64 0 0 0 12 2a10 10 0 0 0-8.98 5.51l3.37 2.59c.8-2.36 3-4.12 5.61-4.12Z"/>
                            </svg>
                            Continue with Google
                        </button>
                        <p class="auth-provider-hint">{{ $googleOauthHint ?? 'Google Sign-In is currently unavailable. Configure Google OAuth in the app environment.' }}</p>
                    @endif
                </div>

                <div class="auth-divider">
                    <span></span>
                    <strong>or use your email password</strong>
                    <span></span>
                </div>

                <form action="{{ route('login.store') }}" method="POST" class="auth-form-stack">
                    @csrf

                    <label class="form-field">
                        <span class="auth-label">Email Address</span>
                        <div class="auth-input-wrap">
                            <x-icon name="user" class="h-5 w-5 text-slate-500" />
                            <input type="email" name="email" class="auth-input" placeholder="Email" value="{{ old('email') }}" autocomplete="email" required>
                        </div>
                    </label>

                    <label class="form-field">
                        <span class="auth-label">Password</span>
                        <div class="auth-input-wrap">
                            <x-icon name="lock" class="h-5 w-5 text-slate-500" />
                            <input id="login-password" type="password" name="password" class="auth-input auth-input-password" placeholder="Password" autocomplete="current-password" required>
                        </div>
                    </label>

                    <div class="auth-form-options">
                        <label class="auth-check-label">
                            <input
                                type="checkbox"
                                name="remember"
                                value="1"
                                class="h-4 w-4 rounded border-slate-300 text-brand-600 focus:ring-brand-200"
                                @checked(old('remember'))
                            >
                            <span>Remember Me</span>
                        </label>
                        <a href="{{ route('password.request') }}" class="auth-soft-link">Forgot password?</a>
                    </div>

                    <button type="submit" class="auth-submit">
                        <span>Continue</span>
                        <x-icon name="chevron-right" class="h-4 w-4" />
                    </button>
                </form>
                </section>

                <section class="auth-aside auth-aside-right">
                    <div class="auth-aside-copy">
                        <h2 class="auth-aside-title">Hello, Friend!</h2>
                        <p class="auth-aside-text">
                            Register with your personal shop details to use all MotoX site features.
                        </p>
                        <a href="{{ route('register') }}" class="auth-panel-cta" data-auth-route-target="register">Sign Up</a>
                    </div>
                </section>
            </div>
        </div>
    </div>
@endsection
