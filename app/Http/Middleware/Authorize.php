<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\Access\Response;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;

class Authorize extends \Illuminate\Auth\Middleware\Authorize
{
    /**
     * Since the spatie/laravel-permission package doesn't allow natively to assign a role to a guest user
     * this piece of middleware will intercept the request and execute the check manually.
     */
    public function handle($request, Closure $next, $ability, ...$models)
    {
        $user = Auth::guard('api')->user();

        // Check if the user is authenticated
        if (!$user) {
            //Get the Guest role
            $role = Role::findByName('guest');

            //Check if the guest role could be found and has permission
            if ($role && $role->hasPermissionTo($ability)) {
                //Permit the request
                Response::allow();
                return $next($request);
            }
        } else {
            // Check if the user has been approved
            if(!$user->approved) {
                return response()->json(["message" => "This account has not been approved yet"], 403);
            }

            // Check if the user has been blocked
            if($user->blocked) {
                return response()->json(["message" => "This account is blocked"], 403);
            }

            // Finally, check if the user has the correct permission
            if ($user->hasPermissionTo($ability, 'api')) {
                Response::allow();
                return $next($request);
            }
        }

        return response()->json(["message" => "You do not have the required permissions"], 403);
    }
}
