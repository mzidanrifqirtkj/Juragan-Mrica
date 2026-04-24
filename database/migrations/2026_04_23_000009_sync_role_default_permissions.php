<?php

use App\Support\RolePermissionMatrix;
use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    public function up(): void
    {
        foreach (RolePermissionMatrix::permissions() as $permissionName) {
            Permission::findOrCreate($permissionName, 'web');
        }

        foreach (RolePermissionMatrix::defaults() as $roleName => $permissions) {
            $role = Role::findOrCreate($roleName, 'web');

            if ($roleName === 'owner') {
                $role->syncPermissions($permissions);
                continue;
            }

            $role->syncPermissions($permissions);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        // Keep existing permission assignments on rollback.
    }
};
