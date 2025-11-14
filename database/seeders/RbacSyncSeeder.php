<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RbacSyncSeeder extends Seeder
{
    public function run(): void
    {
        $actions = config('rbac.actions', ['view','create','update','delete']);
        $modules = config('rbac.modules', []);
        $allPerms = [];

        foreach ($modules as $m) {
            foreach ($actions as $a) {
                $name = "{$m}.{$a}";
                Permission::findOrCreate($name, 'web'); // guard_name='web'
                $allPerms[] = $name;
            }
        }

        foreach (config('rbac.roles', []) as $roleName => $grants) {
            $role = Role::findOrCreate($roleName, 'web');

            if ($grants === '*') {
                $role->syncPermissions($allPerms);
            } else {
                $role->syncPermissions($grants);
            }
        }

        // OPTIONAL: ensure your first user has the admin role
        $user = \App\Models\User::where('username', 'admin')->first(); // or find(1)
        if ($user && !$user->hasRole('admin')) {
            $user->assignRole('admin');
        }
    }
}
