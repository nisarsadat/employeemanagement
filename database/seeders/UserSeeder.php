<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['username' => 'admin'],
            ['name' => 'Admin', 'password' => Hash::make('password'), 'active' => true]
        );
        $manager = User::firstOrCreate(
            ['username' => 'manager'],
            ['name' => 'Manager', 'password' => Hash::make('password'), 'active' => true]
        );
        $user = User::firstOrCreate(
            ['username' => 'user'],
            ['name' => 'User', 'password' => Hash::make('password'), 'active' => true]
        );

        $admin->syncRoles(['admin']);
        $manager->syncRoles(['manager']);
        $user->syncRoles(['user']);
    }
}
