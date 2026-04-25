<?php

namespace App\Http\Controllers;

use App\Models\Shop;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
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
                    'email' => 'These credentials do not match our records.',
                ])
                ->onlyInput('email');
        }

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    public function redirectToGoogle(): RedirectResponse
    {
        if (! $this->isGoogleOauthConfigured()) {
            return redirect()
                ->route('login')
                ->withErrors([
                    'email' => 'Google Sign-In is not configured yet. Set GOOGLE_CLIENT_ID, GOOGLE_CLIENT_SECRET, and GOOGLE_REDIRECT_URI in .env.',
                ]);
        }

        return Socialite::driver('google')
            ->redirectUrl(route('google.callback'))
            ->redirect();
    }

    public function handleGoogleCallback(Request $request): RedirectResponse
    {
        if (! $this->isGoogleOauthConfigured()) {
            return redirect()
                ->route('login')
                ->withErrors([
                    'email' => 'Google Sign-In is not configured yet. Please contact the administrator.',
                ]);
        }

        if ($request->filled('error')) {
            $description = $request->string('error_description')->toString();
            $message = $description !== '' ? $description : 'Google login was cancelled or failed.';

            return redirect()
                ->route('login')
                ->withErrors([
                    'email' => $message,
                ]);
        }

        try {
            $googleUser = Socialite::driver('google')
                ->redirectUrl(route('google.callback'))
                ->user();
        } catch (InvalidStateException $exception) {
            $googleUser = Socialite::driver('google')
                ->redirectUrl(route('google.callback'))
                ->stateless()
                ->user();
        } catch (Throwable $exception) {
            report($exception);

            return redirect()
                ->route('login')
                ->withErrors([
                    'email' => 'Google login failed. Please try again.',
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
        $clientId = (string) Config::get('services.google.client_id', '');
        $clientSecret = (string) Config::get('services.google.client_secret', '');
        $redirect = (string) Config::get('services.google.redirect', '');

        return $clientId !== '' && $clientSecret !== '' && $redirect !== '';
    }
}
