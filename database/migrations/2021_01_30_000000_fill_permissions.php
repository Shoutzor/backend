<?php

use App\Models\User;
use App\Exceptions\ConfigPropertyMissingException;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Exceptions\PermissionAlreadyExists;
use Spatie\Permission\Exceptions\RoleAlreadyExists;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class FillPermissions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        /*
         * Create roles (if not existing)
         */
        $guest = $this->createRole('guest', 'this role is applied to unauthenticated users', true);
        $user = $this->createRole('user', 'this is the default role assigned to all users', true);
        $admin = $this->createRole('admin', 'this is a special role for administrators', true);

        /*
         * Create permissions (if not existing)
         */

        $this->createPermission(
            'website.access',
            '(dis)allows visiting the website (ie: require login)',
            [$guest, $user, $admin]
        );

        $this->createPermission('website.search', '(dis)allows searching', [$guest, $user, $admin]);
        $this->createPermission('website.upload', '(dis)allows uploading media', [$user, $admin]);
        $this->createPermission('website.request', '(dis)allows requests', [$user, $admin]);

        $this->createPermission('admin.access', '(dis)allows accessing the admin panel and sub-pages', [$admin]);

        $this->createPermission('admin.user.list', '(dis)allows listing shoutzor roles', [$admin]);
        $this->createPermission('admin.user.edit', '(dis)allows listing shoutzor roles', [$admin]);
        $this->createPermission('admin.user.delete', '(dis)allows listing shoutzor roles', [$admin]);

        $this->createPermission('admin.role.list', '(dis)allows listing shoutzor roles', [$admin]);
        $this->createPermission('admin.role.edit', '(dis)allows listing shoutzor roles', [$admin]);
        $this->createPermission('admin.role.delete', '(dis)allows listing shoutzor roles', [$admin]);

        // TODO replace with DB statements
        $user = User::where('username', 'admin')->first();
        $user->assignRole('user');
        $user->assignRole('admin');
    }

    /**
     * Create a role if it doesn't exist yet
     *
     * @param string $name the name of the role
     */
    private function createRole(string $name, string $description, bool $protected = false)
    {
        $tableNames = config('permission.table_names');

        if (empty($tableNames)) {
            throw new ConfigPropertyMissingException(
                'Error: config/permission.php not found and defaults could not be merged. Please publish the package configuration before proceeding, or drop the tables manually.'
            );
        }

        return DB::table($tableNames['roles'])->insertGetId([
            'name' => $name, 
            'description' => $description,
            'guard_name' => 'api',
            'protected' => $protected,
            'created_at' => now()
        ]);
    }

    /**
     * Create a permission if it doesn't exist yet.
     *
     * @param string $name the name of the permission
     * @param array $roles the roles to assign this permission to by default
     */
    private function createPermission(string $name, string $description, array $roles = [])
    {
        $tableNames = config('permission.table_names');

        if (empty($tableNames)) {
            throw new ConfigPropertyMissingException(
                'Error: config/permission.php not found and defaults could not be merged. Please publish the package configuration before proceeding, or drop the tables manually.'
            );
        }

        $permissionId = DB::table($tableNames['permissions'])->insertGetId([
            'name' => $name, 
            'description' => $description,
            'guard_name' => 'api',
            'created_at' => now()
        ]);

        //Assign the permission for the provided roles
        DB::table('role_has_permissions')->insert(
            collect($roles)->map(fn ($roleId) => [
                'role_id' => $roleId,
                'permission_id' => $permissionId
            ])->toArray()
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $tableNames = config('permission.table_names');

        if (empty($tableNames)) {
            throw new ConfigPropertyMissingException(
                'Error: config/permission.php not found and defaults could not be merged. Please publish the package configuration before proceeding, or drop the tables manually.'
            );
        }

        DB::table($tableNames['role_has_permissions'])->truncate();
        DB::table($tableNames['roles'])->truncate();
        DB::table($tableNames['permissions'])->truncate();
    }
}
