<?php

namespace App\Http\Middleware;

use Closure;

class HandleCors extends \Illuminate\Http\Middleware\HandleCors {
    /**
     *
     * Apollo breaks when the file upload size is too large because the 413 doesnt'
     * include the Access-Control-Allow-Origin header. Therefor not processing the 413
     * and instead focussing on the CORS error.
     *
     * This is just a workaround by ALWAYS including the cors header
     *
     * @param $request
     * @param Closure $next
     * @param $ability
     * @param ...$models
     * @return void
     */
    public function handle($request, Closure $next)
    {
        if ($this->cors->isPreflightRequest($request)) {
            return $next($request);
        }

        return $next($request)
            ->header('Access-Control-Allow-Origin', '*');
    }
}