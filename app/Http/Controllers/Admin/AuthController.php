<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AuthController extends Controller
{
    /**
     * Display the admin login form.
     */
    public function showLoginForm(): View|RedirectResponse
    {
        if (Auth::guard('admin')->check()) {
            return redirect()->route('admin.dashboard');
        }

        return view('admin.auth.login');
    }

    /**
     * Process an administrative login request with strict rate limiting.
     */
    public function login(Request $request, AuditLogService $auditLogService): Response|JsonResponse|RedirectResponse
    {
        $credentials = $request->validate([
            'login'    => ['required', 'string'],
            'password' => ['required', 'string'],
            'remember' => ['nullable', 'boolean'],
        ]);

        $loginInput      = trim((string) $credentials['login']);
        $normalizedLogin = Str::transliterate(Str::lower($loginInput));
        $throttleKey     = 'admin_login:' . $normalizedLogin . '|' . $request->ip();

        // Security: Rate limiting check (max 5 failed attempts per 60 seconds per login + IP)
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            $auditLogService->log(
                action: 'admin.login_throttled',
                entity: 'Admin',
                changes: [
                    'login'               => $normalizedLogin,
                    'ip_address'          => $request->ip(),
                    'retry_after_seconds' => $seconds,
                ]
            );

            $message = "Too many login attempts. Please try again in {$seconds} seconds.";

            if ($request->expectsJson()) {
                return response()->json([
                    'message'     => $message,
                    'retry_after' => $seconds,
                ], 429, ['Retry-After' => (string) $seconds]);
            }

            return response()->view('admin.auth.login', [
                'throttleSeconds' => $seconds,
                'errorMessage'    => $message,
            ], 429, ['Retry-After' => (string) $seconds]);
        }

        $fieldType = filter_var($loginInput, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        $authAttempt = [
            $fieldType  => $loginInput,
            'password'  => $credentials['password'],
            'is_active' => true,
        ];

        if (Auth::guard('admin')->attempt($authAttempt, (bool) ($credentials['remember'] ?? false))) {
            $admin = Auth::guard('admin')->user();

            // Safeguard: Ensure user has an administrative role assigned
            if (! $admin->role_id) {
                Auth::guard('admin')->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                RateLimiter::hit($throttleKey, 60);

                return back()->withInput($request->only('login', 'remember'))->withErrors([
                    'login' => 'Access denied. Account does not have administrative privileges.',
                ]);
            }

            // Security: Clear rate limiter on successful authentication
            RateLimiter::clear($throttleKey);

            // Security: Prevent session fixation
            $request->session()->regenerate();

            $admin->update(['last_login_at' => now()]);

            // Record successful login audit log
            $auditLogService->log(
                action: 'admin.login',
                entity: 'Admin',
                entityId: $admin->id,
                changes: ['ip_address' => $request->ip()]
            );

            return redirect()->intended(route('admin.dashboard'));
        }

        // Security: Record rate limit hit on failed attempt (decay for 60 seconds)
        RateLimiter::hit($throttleKey, 60);

        // Security: Log failed login attempt without sensitive credential payload
        $auditLogService->log(
            action: 'admin.login_failed',
            entity: 'Admin',
            changes: [
                'login'      => $normalizedLogin,
                'ip_address' => $request->ip(),
            ]
        );

        // Generic error message to prevent account enumeration
        return back()->withInput($request->only('login', 'remember'))->withErrors([
            'login' => 'The provided credentials do not match our records or the account is inactive.',
        ]);
    }

    /**
     * Log out of the administrative session.
     */
    public function logout(Request $request, AuditLogService $auditLogService): RedirectResponse
    {
        $admin = Auth::guard('admin')->user();

        if ($admin) {
            $auditLogService->log(
                action: 'admin.logout',
                entity: 'Admin',
                entityId: $admin->id,
                changes: ['ip_address' => $request->ip()]
            );
        }

        Auth::guard('admin')->logout();

        // Security: Invalidate session and regenerate CSRF token
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login')->with('status', 'You have been successfully logged out.');
    }
}

