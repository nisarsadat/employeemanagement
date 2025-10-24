<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Ensure a clean slate
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $guard = 'web';

        // Define permissions
        $permissions = [
            // users.*
            'users.view', 'users.create', 'users.update', 'users.delete',
            // employees.*
            'employees.view', 'employees.create', 'employees.update', 'employees.delete',
        ];

        foreach ($permissions as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => $guard]);
        }

        // Roles
        $admin   = Role::firstOrCreate(['name' => 'admin', 'guard_name' => $guard]);
        $manager = Role::firstOrCreate(['name' => 'manager', 'guard_name' => $guard]);
        $user    = Role::firstOrCreate(['name' => 'user', 'guard_name' => $guard]);

        // Admin gets everything
        $admin->givePermissionTo(Permission::all());

        // Manager: employees.view/create/update only
        $manager->syncPermissions([
            'employees.view', 'employees.create', 'employees.update',
        ]);

        // Regular user: baseline permission
        $user->syncPermissions([
            'employees.view',
        ]);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
