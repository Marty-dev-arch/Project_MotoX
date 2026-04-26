<?php

namespace App\Http\Controllers;

use App\Models\Shop;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\InvalidStateException;
use Throwable;

class AuthController extends Controller
{
    public function showLogin(): View
    {
        return view('pages.login', [
            'pageTitle' => 'Log In',
            'googleOauthConfigured' => $this->isGoogleOauthConfigured(),
            'googleOauthHint' => $this->googleOauthHint(),
        ]);
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials)) {
            return back()
                ->withErrors([
                    'email' => 'Invalid Email or Passsword.',
                ])
                ->onlyInput('email');
        }

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
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

        $status = Password::sendResetLink([
            'email' => strtolower(trim((string) $validated['email'])),
        ]);

        if ($status === Password::RESET_LINK_SENT) {
            return back()->with('status', __($status));
        }

        return back()
            ->withErrors(['email' => __($status)])
            ->withInput($request->only('email'));
    }

    public function showResetPassword(Request $request, string $token): View
    {
        return view('pages.reset-password', [
            'pageTitle' => 'Reset Password',
            'token' => $token,
            'email' => $request->string('email')->toString(),
        ]);
    }

    public function resetPassword(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'confirmed', 'min:8', 'max:255'],
        ]);

        $status = Password::reset(
            [
                'email' => strtolower(trim((string) $validated['email'])),
                'password' => (string) $validated['password'],
                'password_confirmation' => (string) $request->input('password_confirmation'),
                'token' => (string) $validated['token'],
            ],
            function (User $user, string $password): void {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            },
        );

        if ($status === Password::PASSWORD_RESET) {
            return redirect()
                ->route('login')
                ->with('status', __($status));
        }

        return back()
            ->withErrors(['email' => __($status)])
            ->withInput($request->only('email'));
    }

    public function redirectToGoogle(): RedirectResponse
    {
        if (! $this->isGoogleOauthConfigured()) {
            return redirect()
                ->route('login')
                ->withErrors([
                    'email' => $this->googleOauthHint(),
                ]);
        }

        return Socialite::driver('google')
            ->redirectUrl($this->googleRedirectUrl())
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
            try {
                $googleUser = Socialite::driver('google')
                    ->redirectUrl($this->googleRedirectUrl())
                    ->user();
            } catch (InvalidStateException $exception) {
                $googleUser = Socialite::driver('google')
                    ->redirectUrl($this->googleRedirectUrl())
                    ->stateless()
                    ->user();
            }
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
            $user = User::query()->where('email', $email)->first();

            if (! $user) {
                $user = User::query()->create([
                    'name' => Str::limit($displayName, 120, ''),
                    'email' => $email,
                    'password' => Str::random(40),
                ]);
            }

            if (! $user->shop) {
                Shop::query()->create([
                    'user_id' => $user->id,
                    'name' => Str::limit("{$displayName}'s Garage", 120, ''),
                    'owner_name' => Str::limit($displayName, 120, ''),
                    'contact_number' => null,
                ]);
            }

            return $user;
        });

        Auth::login($user, true);
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    public function showRegister(): View
    {
        return view('pages.register', [
            'pageTitle' => 'Create your account',
        ]);
    }

    public function register(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'shop_name' => ['required', 'string', 'max:120'],
            'owner_name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:255', Rule::unique(User::class, 'email')],
            'contact_number' => ['nullable', 'string', 'max:40'],
            'password' => ['required', 'string', 'confirmed', 'min:8', 'max:255'],
        ]);

        $user = DB::transaction(function () use ($validated): User {
            $user = User::query()->create([
                'name' => $validated['owner_name'],
                'email' => $validated['email'],
                'password' => $validated['password'],
            ]);

            Shop::query()->create([
                'user_id' => $user->id,
                'name' => $validated['shop_name'],
                'owner_name' => $validated['owner_name'],
                'contact_number' => $validated['contact_number'] ?: null,
            ]);

            return $user;
        });

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    private function isGoogleOauthConfigured(): bool
    {
        return count($this->missingGoogleOauthConfig()) === 0;
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
}
