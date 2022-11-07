<?php

namespace App\Http\Middleware;

use App\Helpers\Authorization;
use Closure;
use Exception;
use Illuminate\Auth\Access\Response;
use Illuminate\Support\Facades\Auth;

class Authorize extends \Illuminate\Auth\Middleware\Authorize
{
    /**
     * Since the spatie/laravel-permission package doesn't allow natively to assign a role to a guest user
     * this piece of middleware will intercept the request and execute the check manually.
     */
    public function handle($request, Closure $next, $ability, ...$models)
    {
        $user = Auth::guard('api')->user();

        try {
            // Check if the user (or guest) is authorized
            Authorization::validate($user)
                ->can($ability);

            //Permit the request
            Response::allow();
            return $next($request);
        }
        catch(Exception $e) {
            return response()->json(["message" => $e->getMessage()], 403);
        }
    }
}
