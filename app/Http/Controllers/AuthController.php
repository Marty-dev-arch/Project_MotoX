<?php

namespace App\Http\Controllers;

use App\Models\PasswordResetOtp;
use App\Models\LoginVerificationOtp;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\InvalidStateException;
use Symfony\Component\HttpFoundation\Cookie;
use Throwable;

class AuthController extends Controller
{
    private const GOOGLE_ACCOUNT_COOKIE = 'motox_google_account';

    public function showLogin(Request $request): View
    {
        return view('pages.login', [
            'pageTitle' => 'Log In',
            'googleOauthConfigured' => $this->isGoogleOauthConfigured(),
            'googleOauthHint' => $this->googleOauthHint(),
            'googleAccount' => $this->rememberedGoogleAccount($request),
        ]);
    }

    public function login(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'remember' => ['nullable', 'boolean'],
        ]);

        $remember = $request->boolean('remember');
        $credentials = [
            'email' => strtolower(trim((string) $validated['email'])),
            'password' => (string) $validated['password'],
        ];

        if (! Auth::validate($credentials)) {
            return back()
                ->withErrors([
                    'email' => 'Invalid email or password',
                ])
                ->onlyInput('email');
        }

        $user = User::query()->where('email', $credentials['email'])->first();

        if (! $user) {
            return back()
                ->withErrors([
                    'email' => 'Invalid email or password',
                ])
                ->onlyInput('email');
        }

        if (($user->role ?? null) === 'client') {
            return back()
                ->withErrors([
                    'email' => 'Client accounts are no longer supported.',
                ])
                ->onlyInput('email');
        }

        return $this->startLoginVerification($request, $user, $remember, 'password');
    }

    public function showLoginVerification(Request $request): View|RedirectResponse
    {
        $pendingLogin = $this->pendingLogin($request);

        if (! $pendingLogin) {
            return redirect()
                ->route('login')
                ->withErrors(['email' => 'Start sign in again to receive a verification code.']);
        }

        return view('pages.login-verify', [
            'pageTitle' => 'Security Verification',
            'email' => (string) $pendingLogin['email'],
            'maskedEmail' => $this->maskEmail((string) $pendingLogin['email']),
            'provider' => (string) ($pendingLogin['provider'] ?? 'password'),
        ]);
    }

    public function verifyLoginOtp(Request $request): RedirectResponse
    {
        $pendingLogin = $this->pendingLogin($request);

        if (! $pendingLogin) {
            return redirect()
                ->route('login')
                ->withErrors(['email' => 'Start sign in again to receive a verification code.']);
        }

        $validated = $request->validate([
            'otp' => ['required', 'digits:6'],
        ]);

        $user = User::query()->find((int) $pendingLogin['user_id']);
        $email = strtolower(trim((string) $pendingLogin['email']));

        if (! $user || strtolower((string) $user->email) !== $email) {
            $request->session()->forget('login_verification');

            return redirect()
                ->route('login')
                ->withErrors(['email' => 'Verification session expired. Please sign in again.']);
        }

        $record = LoginVerificationOtp::query()
            ->activeFor($user->id, $email)
            ->latest('id')
            ->first();

        if (! $record) {
            return back()
                ->withErrors(['otp' => 'Verification code is invalid or expired.'])
                ->withInput();
        }

        if ($record->attempts >= 5) {
            return back()
                ->withErrors(['otp' => 'Too many attempts. Request a new code.'])
                ->withInput();
        }

        if (! Hash::check((string) $validated['otp'], $record->otp_hash)) {
            $record->increment('attempts');

            return back()
                ->withErrors(['otp' => 'Incorrect verification code.'])
                ->withInput();
        }

        $record->update(['consumed_at' => now()]);

        Auth::login($user, (bool) ($pendingLogin['remember'] ?? false));
        $request->session()->regenerate();
        $request->session()->forget('login_verification');
        $request->session()->put('auth.provider', (string) ($pendingLogin['provider'] ?? 'password'));

        return redirect()->route($this->homeRouteFor($user));
    }

    public function resendLoginOtp(Request $request): RedirectResponse
    {
        $pendingLogin = $this->pendingLogin($request);

        if (! $pendingLogin) {
            return redirect()
                ->route('login')
                ->withErrors(['email' => 'Start sign in again to receive a verification code.']);
        }

        $user = User::query()->find((int) $pendingLogin['user_id']);
        if (! $user) {
            $request->session()->forget('login_verification');

            return redirect()
                ->route('login')
                ->withErrors(['email' => 'Verification session expired. Please sign in again.']);
        }

        if (! $this->canSendResetOtpEmail()) {
            return back()
                ->withErrors(['otp' => $this->loginVerificationMailHint()]);
        }

        try {
            $message = $this->sendLoginVerificationOtp($request, $user, (string) ($pendingLogin['provider'] ?? 'password'));
        } catch (Throwable $exception) {
            report($exception);

            return back()
                ->withErrors(['otp' => $this->otpDeliveryErrorMessage($exception)]);
        }

        return redirect()
            ->route('login.verify')
            ->with('status', $message);
    }

    public function showForgotPassword(): View
    {
        return view('pages.forgot-password', [
            'pageTitle' => 'Forgot Password',
        ]);
    }

    public function sendPasswordResetLink(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
        ]);

        if (! $this->canSendResetOtpEmail()) {
            return back()
                ->withErrors([
                    'email' => $this->resetOtpMailHint(),
                ])
                ->withInput($request->only('email'));
        }

        $email = strtolower(trim((string) $validated['email']));
        $user = User::query()->where('email', $email)->first();

        if (! $user) {
            return redirect()
                ->route('password.otp.form', ['email' => $email])
                ->with('status', 'If this email is registered, an OTP has been sent.');
        }

        $latestOtp = PasswordResetOtp::query()
            ->where('email', $email)
            ->latest('id')
            ->first();

        if ($latestOtp && $latestOtp->created_at?->gt(now()->subMinute())) {
            return back()
                ->withErrors([
                    'email' => 'Please wait at least 1 minute before requesting a new OTP.',
                ])
                ->withInput($request->only('email'));
        }

        $otp = (string) random_int(100000, 999999);

        $otpRecord = PasswordResetOtp::query()->create([
            'email' => $email,
            'otp_hash' => Hash::make($otp),
            'attempts' => 0,
            'expires_at' => now()->addMinutes(10),
            'ip_address' => $request->ip(),
        ]);

        $message = implode("\n", [
            'MotoX Password Reset OTP',
            '',
            "Your OTP is: {$otp}",
            'This code expires in 10 minutes.',
            'If you did not request this, you can ignore this email.',
        ]);

        try {
            Mail::raw($message, function ($mail) use ($email): void {
                $mail
                    ->to($email)
                    ->subject('MotoX Password Reset OTP');
            });
        } catch (Throwable $exception) {
            report($exception);
            $otpRecord->delete();

            return back()
                ->withErrors([
                    'email' => $this->otpDeliveryErrorMessage($exception),
                ])
                ->withInput($request->only('email'));
        }

        return redirect()
            ->route('password.otp.form', ['email' => $email])
            ->with('status', 'A 6-digit OTP has been sent to your email.');
    }

    public function showVerifyOtp(Request $request): View
    {
        return view('pages.forgot-password-otp', [
            'pageTitle' => 'Verify OTP',
            'email' => $request->string('email')->toString(),
        ]);
    }

    public function verifyOtp(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'otp' => ['required', 'digits:6'],
        ]);

        $email = strtolower(trim((string) $validated['email']));
        $record = PasswordResetOtp::query()
            ->activeForEmail($email)
            ->latest('id')
            ->first();

        if (! $record) {
            return back()
                ->withErrors(['otp' => 'OTP is invalid or expired.'])
                ->withInput($request->only('email'));
        }

        if ($record->attempts >= 5) {
            return back()
                ->withErrors(['otp' => 'Too many attempts. Request a new OTP.'])
                ->withInput($request->only('email'));
        }

        $otp = (string) $validated['otp'];
        if (! Hash::check($otp, $record->otp_hash)) {
            $record->increment('attempts');

            return back()
                ->withErrors(['otp' => 'Incorrect OTP.'])
                ->withInput($request->only('email'));
        }

        $record->update([
            'consumed_at' => now(),
        ]);

        $request->session()->put('password_reset_verified', [
            'email' => $email,
            'verified_at' => now()->timestamp,
        ]);

        return redirect()
            ->route('password.reset')
            ->with('status', 'OTP verified. You can now reset your password.');
    }

    public function showResetPassword(Request $request): View
    {
        $verified = $request->session()->get('password_reset_verified');
        abort_if(! is_array($verified) || ! isset($verified['email']), 403, 'Password reset session not found.');

        return view('pages.reset-password', [
            'pageTitle' => 'Reset Password',
            'email' => (string) $verified['email'],
            'otpVerified' => true,
        ]);
    }

    public function resetPassword(Request $request): RedirectResponse
    {
        $verified = $request->session()->get('password_reset_verified');
        if (! is_array($verified) || ! isset($verified['email'])) {
            return redirect()
                ->route('password.request')
                ->withErrors(['email' => 'OTP verification is required.']);
        }

        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => $this->passwordValidationRules(['required', 'confirmed']),
        ], $this->passwordValidationMessages());

        $email = strtolower(trim((string) $validated['email']));
        if ($email !== strtolower(trim((string) $verified['email']))) {
            return back()->withErrors(['email' => 'Email does not match verified OTP session.']);
        }

        $user = User::query()->where('email', $email)->first();
        if (! $user) {
            return back()->withErrors(['email' => 'Account not found.']);
        }

        $user->forceFill([
            'password' => Hash::make((string) $validated['password']),
            'remember_token' => Str::random(60),
        ])->save();

        PasswordResetOtp::query()
            ->where('email', $email)
            ->update(['consumed_at' => now()]);

        $request->session()->forget('password_reset_verified');

        return redirect()
            ->route('login')
            ->with('status', 'Password updated successfully. Please log in.');
    }

    public function redirectToGoogle(Request $request): RedirectResponse
    {
        if (! $this->isGoogleOauthConfigured()) {
            return redirect()
                ->route('login')
                ->withErrors([
                    'email' => $this->googleOauthHint(),
                ]);
        }

        $googleAccount = $this->rememberedGoogleAccount($request);
        $googleOptions = $googleAccount
            ? ['login_hint' => $googleAccount['email']]
            : ['prompt' => 'select_account'];

        return Socialite::driver('google')
            ->redirectUrl($this->googleRedirectUrl())
            ->with($googleOptions)
            ->redirect();
    }

    public function handleGoogleCallback(Request $request): RedirectResponse
    {
        if (! $this->isGoogleOauthConfigured()) {
            return redirect()
                ->route('login')
                ->withErrors([
                    'email' => $this->googleOauthHint(),
                ]);
        }

        if ($request->filled('error')) {
            $description = urldecode($request->string('error_description')->toString());
            $normalizedDescription = strtolower($description);

            if (str_contains($normalizedDescription, 'redirect_uri_mismatch')) {
                $message = sprintf(
                    'Google redirect URI mismatch. Set Authorized redirect URI to: %s',
                    $this->configuredGoogleRedirectUrl(),
                );
            } else {
                $message = $description !== '' ? $description : 'Google login was cancelled or failed.';
            }

            return redirect()
                ->route('login')
                ->withErrors([
                    'email' => $message,
                ]);
        }

        try {
            $googleUser = Socialite::driver('google')
                ->redirectUrl($this->googleRedirectUrl())
                ->user();
        } catch (InvalidStateException $exception) {
            report($exception);

            return redirect()
                ->route('login')
                ->withErrors([
                    'email' => 'Google login session expired. Please try signing in again.',
                ]);
        } catch (Throwable $exception) {
            report($exception);

            $message = 'Google login failed. Please try again.';
            if ((bool) Config::get('app.debug', false)) {
                $message = 'Google login failed: '.$exception->getMessage();
            }

            return redirect()
                ->route('login')
                ->withErrors([
                    'email' => $message,
                ]);
        }

        $email = $googleUser->getEmail();

        if (! $email) {
            return redirect()
                ->route('login')
                ->withErrors([
                    'email' => 'No email was returned by Google. Use another Google account.',
                ]);
        }

        $displayName = trim((string) ($googleUser->getName() ?: Str::before($email, '@')));
        $displayName = $displayName !== '' ? $displayName : 'MotoX User';

        $user = DB::transaction(function () use ($email, $displayName): User {
            $isNewUser = false;
            $user = User::query()->where('email', $email)->first();

            if (! $user) {
                $isNewUser = true;
                $user = User::query()->create([
                    'name' => Str::limit($displayName, 120, ''),
                    'username' => $this->uniqueUsernameFor($email, $displayName),
                    'email' => $email,
                    'password' => Str::random(40),
                    'role' => 'admin',
                ]);
            }

            if ($isNewUser || ! $user->shop) {
                $shop = Shop::query()->create([
                    'user_id' => $user->id,
                    'name' => Str::limit("{$displayName}'s Garage", 120, ''),
                    'owner_name' => Str::limit($displayName, 120, ''),
                    'contact_number' => null,
                ]);

                $user->update(['shop_id' => $shop->id, 'role' => 'admin']);
            } elseif (! $user->shop_id && $user->shop) {
                $user->update(['shop_id' => $user->shop->id]);
            }

            return $user;
        });

        return $this->startLoginVerification($request, $user, false, 'google')
            ->withCookie($this->googleAccountCookie($displayName, $email));
    }

    public function showRegister(Request $request): View
    {
        return view('pages.register', [
            'pageTitle' => 'Create your account',
            'googleOauthConfigured' => $this->isGoogleOauthConfigured(),
            'googleOauthHint' => $this->googleOauthHint(),
            'googleAccount' => $this->rememberedGoogleAccount($request),
        ]);
    }

    public function register(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'shop_name' => ['required', 'string', 'max:120'],
            'owner_name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:255', Rule::unique(User::class, 'email')],
            'contact_number' => ['nullable', 'string', 'max:20', 'regex:/^\+[1-9]\d{6,14}$/'],
            'contact_country' => ['nullable', 'required_with:contact_number', 'string', 'size:2', 'regex:/^[a-z]{2}$/i'],
            'contact_dial_code' => ['nullable', 'required_with:contact_number', 'string', 'max:6', 'regex:/^\+[1-9]\d{0,4}$/'],
            'password' => $this->passwordValidationRules(['required', 'confirmed']),
            'terms' => ['accepted'],
        ], [
            ...$this->passwordValidationMessages(),
            'contact_number.regex' => 'Enter a valid international contact number for the selected country.',
            'contact_country.required_with' => 'Select the country code for the contact number.',
            'contact_dial_code.required_with' => 'Select the dial code for the contact number.',
        ]);

        DB::transaction(function () use ($validated): void {
            $user = User::query()->create([
                'name' => $validated['owner_name'],
                'username' => $this->uniqueUsernameFor($validated['email'], $validated['owner_name']),
                'email' => $validated['email'],
                'password' => $validated['password'],
                'role' => 'admin',
            ]);

            $shop = Shop::query()->create([
                'user_id' => $user->id,
                'name' => $validated['shop_name'],
                'owner_name' => $validated['owner_name'],
                'contact_number' => $validated['contact_number'] ?: null,
            ]);

            $user->update(['shop_id' => $shop->id]);
        });

        return redirect()
            ->route('login')
            ->with('status', 'Your account has been successfully registered.');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('landing');
    }

    /**
     * @return array{user_id:int,email:string,remember:bool,provider:string}|null
     */
    private function pendingLogin(Request $request): ?array
    {
        $pendingLogin = $request->session()->get('login_verification');

        if (! is_array($pendingLogin)
            || ! isset($pendingLogin['user_id'], $pendingLogin['email'], $pendingLogin['provider'])
        ) {
            return null;
        }

        return [
            'user_id' => (int) $pendingLogin['user_id'],
            'email' => (string) $pendingLogin['email'],
            'remember' => (bool) ($pendingLogin['remember'] ?? false),
            'provider' => (string) $pendingLogin['provider'],
        ];
    }

    private function startLoginVerification(Request $request, User $user, bool $remember, string $provider): RedirectResponse
    {
        if (! $this->canSendResetOtpEmail()) {
            return redirect()
                ->route('login')
                ->withErrors(['email' => $this->loginVerificationMailHint()])
                ->withInput($request->only('email'));
        }

        $request->session()->put('login_verification', [
            'user_id' => $user->id,
            'email' => strtolower((string) $user->email),
            'remember' => $remember,
            'provider' => $provider,
            'requested_at' => now()->timestamp,
        ]);

        try {
            $message = $this->sendLoginVerificationOtp($request, $user, $provider);
        } catch (Throwable $exception) {
            report($exception);
            $request->session()->forget('login_verification');

            return redirect()
                ->route('login')
                ->withErrors(['email' => $this->otpDeliveryErrorMessage($exception)])
                ->withInput($request->only('email'));
        }

        return redirect()
            ->route('login.verify')
            ->with('status', $message);
    }

    private function sendLoginVerificationOtp(Request $request, User $user, string $provider): string
    {
        $email = strtolower(trim((string) $user->email));
        $latestOtp = LoginVerificationOtp::query()
            ->where('user_id', $user->id)
            ->where('email', $email)
            ->whereNull('consumed_at')
            ->latest('id')
            ->first();

        if ($latestOtp && $latestOtp->created_at?->gt(now()->subMinute())) {
            return 'A verification code was sent recently. Check your email inbox.';
        }

        $otp = (string) random_int(100000, 999999);
        $otpRecord = LoginVerificationOtp::query()->create([
            'user_id' => $user->id,
            'email' => $email,
            'provider' => $provider,
            'otp_hash' => Hash::make($otp),
            'attempts' => 0,
            'expires_at' => now()->addMinutes(10),
            'ip_address' => $request->ip(),
        ]);

        $message = implode("\n", [
            'MotoX Security Verification',
            '',
            "Your verification code is: {$otp}",
            'This code expires in 10 minutes.',
            'If you did not try to sign in, change your password immediately.',
        ]);

        try {
            Mail::raw($message, function ($mail) use ($email): void {
                $mail
                    ->to($email)
                    ->subject('MotoX Security Verification Code');
            });
        } catch (Throwable $exception) {
            $otpRecord->delete();
            throw $exception;
        }

        return 'A 6-digit security code has been sent to '.$this->maskEmail($email).'.';
    }

    private function loginVerificationMailHint(): ?string
    {
        $hint = $this->resetOtpMailHint();

        return $hint ? str_replace('Password reset email', 'Security verification email', $hint) : null;
    }

    private function maskEmail(string $email): string
    {
        $email = strtolower(trim($email));
        if (! str_contains($email, '@')) {
            return $email;
        }

        [$local, $domain] = explode('@', $email, 2);
        $localMask = Str::substr($local, 0, 1).str_repeat('*', max(3, strlen($local) - 1));
        $domainParts = explode('.', $domain);
        $domainName = (string) ($domainParts[0] ?? '');
        $domainSuffix = count($domainParts) > 1 ? '.'.implode('.', array_slice($domainParts, 1)) : '';
        $domainMask = Str::substr($domainName, 0, 1).str_repeat('*', max(3, strlen($domainName) - 1));

        return "{$localMask}@{$domainMask}{$domainSuffix}";
    }

    private function isGoogleOauthConfigured(): bool
    {
        return count($this->missingGoogleOauthConfig()) === 0;
    }

    /**
     * @return array{name:string,email:string,initial:string}|null
     */
    private function rememberedGoogleAccount(Request $request): ?array
    {
        $payload = $request->cookie(self::GOOGLE_ACCOUNT_COOKIE);

        if (! is_string($payload) || $payload === '') {
            return null;
        }

        $account = json_decode($payload, true);
        if (! is_array($account)) {
            return null;
        }

        $email = strtolower(trim((string) ($account['email'] ?? '')));
        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return null;
        }

        $name = trim((string) ($account['name'] ?? ''));
        $name = $name !== '' ? $name : Str::before($email, '@');
        $initialSource = $name !== '' ? $name : $email;
        $initial = Str::upper(Str::substr($initialSource, 0, 1));

        return [
            'name' => Str::limit($name, 80, ''),
            'email' => $email,
            'initial' => $initial !== '' ? $initial : 'G',
        ];
    }

    private function googleAccountCookie(string $name, string $email): Cookie
    {
        $value = json_encode([
            'name' => trim($name),
            'email' => strtolower(trim($email)),
        ]);

        return cookie(
            self::GOOGLE_ACCOUNT_COOKIE,
            $value ?: '',
            60 * 24 * 90,
            '/',
            null,
            (bool) Config::get('session.secure', false),
            true,
            false,
            'Lax',
        );
    }

    private function missingGoogleOauthConfig(): array
    {
        $missing = [];

        if ((string) Config::get('services.google.client_id', '') === '') {
            $missing[] = 'GOOGLE_CLIENT_ID';
        }

        if ((string) Config::get('services.google.client_secret', '') === '') {
            $missing[] = 'GOOGLE_CLIENT_SECRET';
        }

        if ((string) Config::get('services.google.redirect', '') === '') {
            $missing[] = 'GOOGLE_REDIRECT_URI';
        }

        return $missing;
    }

    private function configuredGoogleRedirectUrl(): string
    {
        $configured = (string) Config::get('services.google.redirect', '');
        if ($configured !== '') {
            return $configured;
        }

        return route('google.callback');
    }

    private function googleRedirectUrl(): string
    {
        $configured = (string) Config::get('services.google.redirect', '');
        if ($configured !== '') {
            return $configured;
        }

        return route('google.callback');
    }

    private function googleOauthHint(): string
    {
        $missing = $this->missingGoogleOauthConfig();
        if (count($missing) === 0) {
            return 'Google OAuth is configured.';
        }

        return sprintf(
            'Google Sign-In is unavailable. Missing: %s. Expected redirect URI: %s',
            implode(', ', $missing),
            $this->configuredGoogleRedirectUrl(),
        );
    }

    private function canSendResetOtpEmail(): bool
    {
        return $this->resetOtpMailHint() === null;
    }

    private function resetOtpMailHint(): ?string
    {
        $defaultMailer = (string) Config::get('mail.default', '');
        $transport = (string) Config::get("mail.mailers.{$defaultMailer}.transport", '');

        if ($defaultMailer === '' || $transport === '' || in_array($transport, ['log', 'array'], true)) {
            return 'Password reset email is not configured. Set MAIL_MAILER=smtp and valid SMTP credentials in your .env file.';
        }

        $scheme = strtolower(trim((string) Config::get("mail.mailers.{$defaultMailer}.scheme", '')));
        if ($scheme !== '' && ! in_array($scheme, ['smtp', 'smtps'], true)) {
            return 'MAIL_SCHEME is invalid. Use MAIL_SCHEME=smtp for port 587 or MAIL_SCHEME=smtps for port 465.';
        }

        $host = strtolower(trim((string) Config::get("mail.mailers.{$defaultMailer}.host", '')));
        $username = trim((string) Config::get("mail.mailers.{$defaultMailer}.username", ''));
        $password = trim((string) Config::get("mail.mailers.{$defaultMailer}.password", ''));
        $fromAddress = strtolower(trim((string) Config::get('mail.from.address', '')));

        if ($host === '' || $username === '' || $password === '' || $fromAddress === '') {
            return 'SMTP mail settings are incomplete. Set MAIL_HOST, MAIL_USERNAME, MAIL_PASSWORD, and MAIL_FROM_ADDRESS in .env.';
        }

        if (str_contains($username, 'your_gmail@') || str_contains($password, 'your_google_app_password') || str_contains($fromAddress, 'your_gmail@')) {
            return 'Replace example mail values with your real Gmail address and a Google App Password in .env.';
        }

        return null;
    }

    private function otpDeliveryErrorMessage(Throwable $exception): string
    {
        $message = strtolower($exception->getMessage());

        if (
            str_contains($message, 'badcredentials')
            || str_contains($message, 'username and password not accepted')
            || str_contains($message, 'expected response code "235" but got code "535"')
        ) {
            return 'Gmail rejected SMTP login. Set MAIL_USERNAME to your Gmail and MAIL_PASSWORD to a 16-character Google App Password (not your normal Gmail password).';
        }

        return 'OTP email could not be sent. Check your mail configuration and try again.';
    }

    private function homeRouteFor(?User $user): string
    {
        if (! $user) {
            return 'login';
        }

        return 'dashboard';
    }

    /**
     * @param  array<int, string>  $prefixRules
     * @return array<int, string>
     */
    private function passwordValidationRules(array $prefixRules = []): array
    {
        return [
            ...$prefixRules,
            'string',
            'min:8',
            'max:16',
            'regex:/[a-z]/',
            'regex:/[A-Z]/',
            'regex:/[0-9]/',
            'regex:/[!@#$%&*]/',
        ];
    }

    /**
     * @return array<string, string>
     */
    private function passwordValidationMessages(): array
    {
        return [
            'password.min' => 'Password must be at least 8 characters.',
            'password.max' => 'Password must not be more than 16 characters.',
            'password.regex' => 'Password must include lowercase, uppercase, number, and one special character from ! @ # $ % & *.',
            'password.confirmed' => 'Password confirmation does not match.',
        ];
    }

    private function uniqueUsernameFor(string $email, string $fallbackName): string
    {
        $source = Str::before($email, '@') ?: $fallbackName;
        $base = Str::of($source)
            ->lower()
            ->replaceMatches('/[^a-z0-9_.-]+/', '-')
            ->trim('-_.')
            ->limit(48, '')
            ->toString();

        $base = $base !== '' ? $base : 'motox-user';
        $username = $base;
        $suffix = 2;

        while (User::query()->where('username', $username)->exists()) {
            $username = Str::limit($base, 52, '').'-'.$suffix;
            $suffix++;
        }

        return $username;
    }
}
