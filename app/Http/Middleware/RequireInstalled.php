<?php

namespace App\Http\Middleware;

use Closure;
use App\Installer\Installer;
use Illuminate\Auth\Access\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class RequireInstalled
{
    public function handle($request, Closure $next)
    {
        try {
            // Check if Shoutz0r is installed
            if (Installer::isInstalled()) {
                Response::allow();
                return $next($request);
            }
        }
        catch(\PDOException $e) {
            Log::critical("Failed to check if Shoutz0r is installed. Error: " . $e->errorInfo);

            return response()->json([
                'available' => false,
                'status' => 'SQL_ERROR'
            ], 503);
        }
        catch(\Throwable $e) {
            Log::critical("Failed to check if Shoutz0r is installed. Error: " . $e->getMessage());
        }

        return response()->json([
            'available' => false,
            'status' => 'NOT_INSTALLED'
        ], 503);
    }
}
