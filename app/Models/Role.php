<?php

namespace App\Models;

use \Exception;
use Illuminate\Database\Eloquent\Concerns\HasEvents;

class Role extends \Spatie\Permission\Models\Role
{
    use HasEvents;

    protected $hidden = ['created_at', 'updated_at', 'guard_name'];

    public static function booted() {
        parent::booted();

        static::saving(function(Role $role) {
            // If the role is protected, make sure the name & description are not being changed
            if(
                $role->protected === 1 &&
                $role->isDirty(['name', 'description'])
            ) {
                throw new Exception("Tried updating protected properties on a role that is protected");
            }

            return true;
        });
    }

}
