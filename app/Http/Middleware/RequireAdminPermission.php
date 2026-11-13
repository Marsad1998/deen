<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RequireAdminPermission
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $admin = Auth::guard('admin')->user();

        if (! $admin) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }
            return redirect()->guest(route('admin.login'));
        }

        if (! $admin->hasPermission($permission)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => "Forbidden: Missing required permission [{$permission}].",
                ], 403);
            }

            abort(403, "Forbidden: Missing required permission [{$permission}].");
        }

        return $next($request);
    }
}
