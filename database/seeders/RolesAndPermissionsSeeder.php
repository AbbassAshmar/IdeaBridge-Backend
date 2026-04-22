<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = [
            'list ideas',
            'view ideas',
            'create idea',
            'take idea',
            'update idea',
            'manage comments',
        ];

        foreach ($permissions as $permissionName) {
            Permission::firstOrCreate([
                'name' => $permissionName,
                'guard_name' => 'web',
            ]);
        }

        $userRole = Role::firstOrCreate([
            'name' => 'User',
            'guard_name' => 'web',
        ]);

        $developerRole = Role::firstOrCreate([
            'name' => 'Developer',
            'guard_name' => 'web',
        ]);

        $userRole->syncPermissions([
            'list ideas',
            'view ideas',
            'create idea',
            'manage comments',
        ]);

        $developerRole->syncPermissions($permissions);
    }
}
