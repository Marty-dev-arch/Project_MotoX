@extends('layouts.auth')

@section('content')
    @php
        $oldOtp = preg_replace('/\D+/', '', (string) old('otp', ''));
        $otpDigits = str_split(str_pad(substr($oldOtp, 0, 6), 6));
        $providerLabel = ($provider ?? 'password') === 'google' ? 'Google sign in' : 'account password';
    @endphp

    <div class="auth-page-shell">
        <div class="auth-layout">
            <section class="auth-aside">
                <div class="auth-aside-copy">
                    <h2 class="auth-aside-title">Security verification.</h2>
                    <p class="auth-aside-text">
                        We need one more step before opening your MotoX workspace.
                    </p>
                </div>
            </section>

            <section class="auth-panel auth-otp-panel">
                <h1 class="auth-title">Check your email</h1>
                <p class="auth-subtitle">
                    We sent a passcode to
                    <span class="auth-otp-email">{{ $maskedEmail }}</span>
                    for {{ $providerLabel }}.
                </p>

                @if (session('status'))
                    <div class="auth-alert auth-alert-success mt-6">
                        <p class="font-semibold">{{ session('status') }}</p>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="auth-alert mt-6">
                        <p class="font-semibold">Unable to verify code.</p>
                        <ul class="mt-2 list-disc space-y-1 pl-5 text-sm">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('login.verify.store') }}" method="POST" class="auth-form-stack auth-otp-form" data-auth-otp-form>
                    @csrf
                    <input type="hidden" name="otp" value="{{ $oldOtp }}" data-auth-otp-code>

                    <label class="form-field">
                        <span class="auth-label">Verification Code</span>
                        <div class="auth-otp-grid" data-auth-otp-digits>
                            @for ($index = 0; $index < 6; $index++)
                                <input
                                    type="text"
                                    inputmode="numeric"
                                    pattern="[0-9]"
                                    maxlength="1"
                                    autocomplete="{{ $index === 0 ? 'one-time-code' : 'off' }}"
                                    class="auth-otp-digit"
                                    value="{{ $otpDigits[$index] !== ' ' ? $otpDigits[$index] : '' }}"
                                    aria-label="Verification digit {{ $index + 1 }}"
                                    data-auth-otp-digit
                                >
                            @endfor
                        </div>
                    </label>

                    <div class="auth-otp-actions">
                        <a href="{{ route('login') }}" class="auth-otp-cancel">Cancel</a>
                        <button type="submit" class="auth-submit auth-otp-submit">
                            <span>Verify</span>
                            <x-icon name="chevron-right" class="h-4 w-4" />
                        </button>
                    </div>
                </form>

                <form action="{{ route('login.verify.resend') }}" method="POST" class="auth-otp-resend">
                    @csrf
                    <span>Didn't get the code?</span>
                    <button type="submit">Click to resend</button>
                </form>
            </section>
        </div>
    </div>
@endsection
