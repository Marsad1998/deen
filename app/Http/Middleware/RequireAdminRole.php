<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RequireAdminRole
{
    /**
     * Handle an incoming request.
     *
     * @param  string  ...$roles
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $admin = Auth::guard('admin')->user();

        if (! $admin) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }
            return redirect()->guest(route('admin.login'));
        }

        if (! $admin->hasRole($roles)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Forbidden: Required administrative role not granted.',
                ], 403);
            }

            abort(403, 'Forbidden: Required administrative role not granted.');
        }

        return $next($request);
    }
}
