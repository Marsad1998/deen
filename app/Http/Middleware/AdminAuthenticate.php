<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminAuthenticate
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $guard = Auth::guard('admin');

        if (! $guard->check()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }

            return redirect()->guest(route('admin.login'));
        }

        $admin = $guard->user();

        // Enforce active account status and administrative role
        if (! $admin->is_active || ! $admin->role_id) {
            $guard->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthorized administrative account.'], 403);
            }

            return redirect()->route('admin.login')->withErrors([
                'username' => 'Your account does not have administrative access.',
            ]);
        }

        return $next($request);
    }
}
