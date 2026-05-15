@extends('layouts.auth')

@section('content')
    <div class="auth-page-shell">
        <div class="auth-page-inner auth-page-inner-register">
            <a href="{{ route('landing') }}" class="auth-home-link">
                <x-icon name="chevron-left" class="h-4 w-4" />
                <span>Back to home</span>
            </a>

            <div class="auth-layout auth-layout-register" data-auth-route-switch>
                <section class="auth-aside auth-aside-left">
                    <div class="auth-aside-copy">
                        <h2 class="auth-aside-title">Welcome Back!</h2>
                        <p class="auth-aside-text">
                            Enter your MotoX account details to return to your dashboard, inventory, and work orders.
                        </p>
                        <a href="{{ route('login') }}" class="auth-panel-cta" data-auth-route-target="login">Sign In</a>
                    </div>
                </section>

                <section class="auth-panel auth-panel-form">
                <h1 class="auth-title">Create Account</h1>
                <p class="auth-subtitle">Use your real shop details to register.</p>

                @if ($errors->any())
                    <div class="auth-alert mt-6">
                        <p class="font-semibold">Please fix the highlighted fields.</p>
                        <ul class="mt-2 list-disc space-y-1 pl-5 text-sm">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
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
                                Sign up with Google
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
                            Sign up with Google
                        </button>
                        <p class="auth-provider-hint">{{ $googleOauthHint ?? 'Google Sign-In is currently unavailable. Configure Google OAuth in the app environment.' }}</p>
                    @endif
                </div>

                <div class="auth-divider">
                    <span></span>
                    <strong>or use your email for registration</strong>
                    <span></span>
                </div>

                <form action="{{ route('register.store') }}" method="POST" class="auth-form-stack" data-auth-password-form data-auth-phone-form>
                    @csrf

                    <div class="auth-grid">
                        <label class="form-field">
                            <span class="auth-label">Shop Name</span>
                            <div class="auth-input-wrap">
                                <x-icon name="car" class="h-5 w-5 text-slate-500" />
                                <input type="text" name="shop_name" class="auth-input" placeholder="MotoX Garage" value="{{ old('shop_name') }}" autocomplete="organization" required>
                            </div>
                        </label>

                        <label class="form-field">
                            <span class="auth-label">Owner Name</span>
                            <div class="auth-input-wrap">
                                <x-icon name="user" class="h-5 w-5 text-slate-500" />
                                <input type="text" name="owner_name" class="auth-input" placeholder="Full name" value="{{ old('owner_name') }}" autocomplete="name" required>
                            </div>
                        </label>
                    </div>

                    <div class="auth-grid auth-contact-grid">
                        <label class="form-field auth-register-email-field">
                            <span class="auth-label">Email Address</span>
                            <div class="auth-input-wrap">
                                <x-icon name="user" class="h-5 w-5 text-slate-500" />
                                <input type="email" name="email" class="auth-input" placeholder="Email" value="{{ old('email') }}" autocomplete="email" required>
                            </div>
                        </label>

                        <label class="form-field">
                            <span class="auth-label">Contact Number</span>
                            <div class="auth-input-wrap auth-phone-wrap">
                                <input type="tel" class="auth-input auth-phone-input" placeholder="912 345 6789" value="{{ old('contact_number') }}" inputmode="numeric" autocomplete="tel" data-auth-phone-input>
                                <x-icon name="phone" class="auth-phone-icon h-5 w-5 text-slate-500" />
                                <input type="hidden" name="contact_number" value="{{ old('contact_number') }}" data-auth-phone-full>
                                <input type="hidden" name="contact_country" value="{{ old('contact_country', 'ph') }}" data-auth-phone-country>
                                <input type="hidden" name="contact_dial_code" value="{{ old('contact_dial_code', '+63') }}" data-auth-phone-dial-code>
                            </div>
                            <p class="auth-inline-error hidden" data-auth-phone-error></p>
                        </label>
                    </div>

                    <div class="auth-grid auth-password-grid">
                        <label class="form-field auth-password-field">
                            <span class="auth-label">Password</span>
                            <div class="auth-input-wrap">
                                <x-icon name="lock" class="h-5 w-5 text-slate-500" />
                                <input id="register-password" type="password" name="password" class="auth-input auth-input-password" placeholder="8-16 characters" autocomplete="new-password" required minlength="8" maxlength="16" data-auth-password aria-describedby="register-password-feedback">
                            </div>
                            <div class="auth-password-feedback" id="register-password-feedback" data-auth-password-feedback>
                                <p class="auth-password-help"></p>
                                <p class="auth-password-message" data-auth-password-strength-message>Password must be at least 8 characters.</p>
                                <div class="auth-strength-meter" aria-hidden="true">
                                    <span data-auth-password-strength-fill></span>
                                </div>
                                <p class="auth-strength-label" data-auth-password-strength-label>Weak</p>
                            </div>
                        </label>

                        <label class="form-field auth-password-confirmation-field">
                            <span class="auth-label">Confirm Password</span>
                            <div class="auth-input-wrap">
                                <x-icon name="lock" class="h-5 w-5 text-slate-500" />
                                <input id="register-password-confirmation" type="password" name="password_confirmation" class="auth-input auth-input-password" placeholder="Repeat password" autocomplete="new-password" required minlength="8" maxlength="16" data-auth-password-confirmation>
                            </div>
                            <p class="auth-inline-error hidden" data-auth-password-match>Password confirmation does not match.</p>
                        </label>
                    </div>

                    <label class="auth-consent">
                        <input type="checkbox" name="terms" value="1" class="sr-only" required @checked(old('terms'))>
                        <span class="auth-consent-dot" aria-hidden="true"></span>
                        <span>
                            I agree to the
                            <a href="{{ route('policies') }}">Terms</a>
                            &amp;
                            <a href="{{ route('privacy') }}">Privacy Policy</a>
                        </span>
                    </label>

                    <button type="submit" class="auth-submit">
                        <span>Sign Up</span>
                        <x-icon name="chevron-right" class="h-4 w-4" />
                    </button>
                </form>
                </section>
            </div>
        </div>
    </div>
@endsection
