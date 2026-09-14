<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\UserRole;
use App\UserStatus;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthController extends Controller
{
    /**
     * Tampilkan halaman login.
     */
    public function showLoginForm(Request $request): View|RedirectResponse
    {
        $user = $request->user();

        if ($user instanceof User && $user->status === UserStatus::Active) {
            return redirect()->route($this->dashboardRoute($user));
        }

        if ($user instanceof User) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        return view('auth.login');
    }

    /**
     * Proses autentikasi user.
     */
    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'identifier' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string'],
        ], [
            'identifier.required' => 'Email atau Username wajib diisi.',
            'identifier.max' => 'Email atau Username maksimal 255 karakter.',
            'password.required' => 'Kata sandi wajib diisi.',
        ]);

        $throttleKey = $this->throttleKey($request);
        $this->ensureIsNotRateLimited($throttleKey);
        $identifier = trim($credentials['identifier']);
        $field = filter_var($identifier, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        if (Auth::attempt([
            $field => $identifier,
            'password' => $credentials['password'],
            'status' => UserStatus::Active->value,
        ], $request->boolean('remember'))) {
            RateLimiter::clear($throttleKey);
            $request->session()->regenerate();

            /** @var User $user */
            $user = $request->user();

            return redirect()->route($this->dashboardRoute($user));
        }

        RateLimiter::hit($throttleKey, 60);

        return back()->withErrors([
            'identifier' => 'Email/Username atau Kata sandi tidak cocok.',
        ])->onlyInput('identifier');
    }

    /**
     * Proses logout.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('pelapor.index');
    }

    private function dashboardRoute(User $user): string
    {
        return match ($user->role) {
            UserRole::Admin => 'admin.dashboard',
            UserRole::SuperAdmin => 'super_admin.dashboard',
        };
    }

    private function ensureIsNotRateLimited(string $throttleKey): void
    {
        if (! RateLimiter::tooManyAttempts($throttleKey, 5)) {
            return;
        }

        $seconds = RateLimiter::availableIn($throttleKey);

        throw ValidationException::withMessages([
            'identifier' => "Terlalu banyak percobaan login. Coba lagi dalam {$seconds} detik.",
        ]);
    }

    private function throttleKey(Request $request): string
    {
        return Str::transliterate(Str::lower($request->string('identifier')->trim()->toString()).'|'.$request->ip());
    }
}
