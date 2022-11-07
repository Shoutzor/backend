<?php

namespace App\Broadcasting;

use App\Helpers\Authorization;
use App\Models\Role;
use App\Models\User;
use Exception;

/**
 * This channel will broadcast any changes to the Roles
 */
class RoleChannel
{
    /**
     * Create a new channel instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Authenticate the user's access to the channel.
     *
     * @param User $user
     * @return bool
     */
    public function join(?User $user, Role $role): bool
    {
        // guest role updates are always allowed
        if($role->name === 'guest') {
            return true;
        }

        // Guests can't access any other roles
        if(!$user) {
            return false;
        }

        try {
            // Admins can access updates for any role
            Authorization::validate($user)
                ->can('admin.role.list');

            return true;
        }
        catch(Exception $e) {
            // Check if the user has the requested role
            return $user->hasRole($role);
        }
    }
}
