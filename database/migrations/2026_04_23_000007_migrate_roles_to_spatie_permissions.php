<?php

use App\Models\User;
use App\Support\RolePermissionMatrix;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    public function up(): void
    {
        if ($this->isMySql()) {
            DB::statement("ALTER TABLE users MODIFY role ENUM('owner', 'admin', 'petani') NOT NULL DEFAULT 'petani'");
        }

        DB::table('users')->where('role', 'kasir')->update(['role' => 'petani']);

        foreach (RolePermissionMatrix::permissions() as $permissionName) {
            Permission::findOrCreate($permissionName, 'web');
        }

        foreach (RolePermissionMatrix::defaults() as $roleName => $permissions) {
            $role = Role::findOrCreate($roleName, 'web');
            $role->syncPermissions($permissions);
        }

        if (Schema::hasTable('users')) {
            User::query()->each(function (User $user): void {
                $roleName = $user->role === 'kasir' ? 'petani' : $user->role;

                if ($roleName) {
                    $user->syncRoles([$roleName]);
                }
            });
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        DB::table('users')->where('role', 'petani')->update(['role' => 'kasir']);

        if ($this->isMySql()) {
            DB::statement("ALTER TABLE users MODIFY role ENUM('owner', 'kasir', 'admin') NOT NULL DEFAULT 'kasir'");
        }

        User::query()->each(function (User $user): void {
            $user->syncRoles([]);
        });

        Role::query()->whereIn('name', ['owner', 'admin', 'petani'])->delete();
        Permission::query()->whereIn('name', RolePermissionMatrix::permissions())->delete();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    private function isMySql(): bool
    {
        return DB::getDriverName() === 'mysql';
    }
};
