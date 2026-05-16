@extends('layouts.auth')


{{-- Purpose: Renders the password reset OTP page. --}}
@section('content')
    @php
        $otpEmail = old('email', $email ?? '');
        $oldOtp = preg_replace('/\D+/', '', (string) old('otp', ''));
        $otpDigits = str_split(str_pad(substr($oldOtp, 0, 6), 6));
    @endphp

    <div class="auth-page-shell">
        <div class="auth-layout">
            <section class="auth-aside">
                <div class="auth-aside-copy">
                    <h2 class="auth-aside-title">Check your email.</h2>
                    <p class="auth-aside-text">
                        Enter the 6-digit verification code we sent to continue resetting your password.
                    </p>
                </div>
            </section>

            <section class="auth-panel auth-otp-panel">
                <h1 class="auth-title">Please check your email</h1>
                <p class="auth-subtitle">
                    We sent a code to
                    <span class="auth-otp-email">{{ $otpEmail ?: 'your email address' }}</span>.
                    It expires in 10 minutes.
                </p>

                @if (session('status'))
                    <div class="mt-7 rounded-2xl border border-emerald-200/80 bg-emerald-50/90 px-4 py-3 text-emerald-700">
                        <p class="font-semibold">{{ session('status') }}</p>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="auth-alert mt-7">
                        <p class="font-semibold">Unable to verify OTP.</p>
                        <ul class="mt-2 list-disc space-y-1 pl-5 text-sm">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('password.otp.verify') }}" method="POST" class="auth-form-stack auth-otp-form" data-auth-otp-form>
                    @csrf

                    <input type="hidden" name="email" value="{{ $otpEmail }}">
                    <input type="hidden" name="otp" value="{{ $oldOtp }}" data-auth-otp-code>

                    <label class="form-field">
                        <span class="auth-label">OTP Code</span>
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
                                    aria-label="OTP digit {{ $index + 1 }}"
                                    data-auth-otp-digit
                                >
                            @endfor
                        </div>
                    </label>

                    <div class="auth-otp-actions">
                        <a href="{{ route('password.request') }}" class="auth-otp-cancel">Cancel</a>
                        <button type="submit" class="auth-submit auth-otp-submit">
                            <span>Verify</span>
                            <x-icon name="chevron-right" class="h-4 w-4" />
                        </button>
                    </div>
                </form>

                <p class="auth-otp-resend">
                    Didn't get the code?
                    <a href="{{ route('password.request') }}">Click to resend</a>
                </p>
            </section>
        </div>
    </div>
@endsection
