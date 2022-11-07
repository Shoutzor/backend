<?php

namespace App\Helpers;

use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Validation\UnauthorizedException;
use Spatie\Permission\Models\Role;

class Authorization {

    private ?User $user;
    private ?Role $guest;

    private function __construct(?User $user) {
        $this->user = $user;

        if($user === null) {
            $this->guest = Role::findByName('guest');
        }
    }

    /**
     * Checks if the user is authorized to do anything at all
     * ie: if the account is active
     * @param User $user
     * @return void
     */
    private static function validateUser(User $user) {
        // Check if the user has been approved
        if (!$user->approved) {
            throw new UnauthorizedException("This account has not been approved yet");
        }

        // Check if the user has been blocked
        if ($user->blocked) {
            throw new UnauthorizedException("This account is blocked");
        }
    }

    /**
     * Creates a new validation instance on a user object
     * @param User|null $user
     * @return static
     */
    public static function validate(?User $user) : self {
        // Before doing anything, check if the user is actually
        // authorized to do anything yet.
        if($user) {
            static::validateUser($user);
        }

        return new Authorization($user);
    }

    /**
     * Checks if the user is authenticated
     * to be used when you want to exclude guests
     * @return $this
     * @throws AuthorizationException
     */
    public function requiresAuthentication(): self
    {
        if($this->user === null) {
            throw new AuthorizationException("User authentication required");
        }

        return $this;
    }

    /**
     * Checks if the user has the correct permissions
     * @param mixed ...$permissions the permission(s) to check
     * @return Authorization
     * @throws AuthorizationException
     */
    public function can(...$permissions): self {
        // Ensure permissions is always an array
        $permissions = collect($permissions)->flatten();

        // If the user isn't authenticated, check the guest role
        $obj = $this->user ?? $this->guest;

        foreach($permissions as $permission) {
            if(!$obj->hasPermissionTo($permission)) {
                throw new AuthorizationException("User does not have the $permission permission");
            }
        }

        return $this;
    }

    public function hasRole(...$roles): self {
        // Ensure roles is always an array
        $roles = collect($roles)->flatten();

        if($this->user) {
            foreach($roles as $role) {
                if(!$this->user->hasRole($role)) {
                    throw new AuthorizationException("User does not have the $role role");
                }
            }
        }
        else {
            //(inverted) Check if the only role in the array is "guest".
            if(
                !(
                    count($roles) == 1 &&
                    in_array('guest', $roles)
                )
            ) {
                throw new AuthenticationException("Guest does not have the required role");
            }
        }

        return $this;
    }
}