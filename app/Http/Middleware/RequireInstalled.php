<?php

namespace App\Http\Middleware;

use Closure;
use App\Installer\Installer;
use Illuminate\Auth\Access\Response;
use Illuminate\Support\Facades\Cache;

class RequireInstalled
{
    public function handle($request, Closure $next)
    {
        // Check if Shoutz0r is installed
        if(Installer::isInstalled()) {
            Response::allow();
            return $next($request);
        }

        return response()->view('install-required', [], 503);
    }
}
