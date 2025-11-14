<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $u = User::firstOrCreate(
            ['username' => 'admin'],
            ['name' => 'Admin User', 'password' => Hash::make('password'), 'image' => null]
        );

        // assign admin role (RbacSyncSeeder must run first)
        $u->assignRole('admin');
    }
}
