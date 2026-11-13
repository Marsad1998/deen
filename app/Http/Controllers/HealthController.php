<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Throwable;

class HealthController extends Controller
{
    /**
     * Basic application liveness check.
     */
    public function check(): JsonResponse
    {
        return response()->json([
            'status' => 'ok',
            'app' => config('app.name', 'DEEN Electronics'),
            'framework' => 'Laravel ' . app()->version(),
            'environment' => config('app.env'),
            'timestamp' => now()->toIso8601String(),
        ], 200);
    }

    /**
     * Database connectivity health check.
     */
    public function checkDb(): JsonResponse
    {
        try {
            DB::connection()->getPdo();

            return response()->json([
                'status' => 'healthy',
                'database' => 'connected',
                'connection' => config('database.default'),
                'database_name' => DB::connection()->getDatabaseName(),
            ], 200);
        } catch (Throwable $e) {
            return response()->json([
                'status' => 'unhealthy',
                'database' => 'disconnected',
                'error' => $e->getMessage(),
            ], 503);
        }
    }
}
