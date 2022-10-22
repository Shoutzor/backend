<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\Access\Response;
use Illuminate\Support\Facades\Cache;

class RequireInstalled
{
    public function handle($request, Closure $next)
    {
        if(Cache::get('shoutzor.installed', false) === true) {
            Response::allow();
            return $next($request);
        }

        return response()->view('install-required', [], 503);
    }
}
