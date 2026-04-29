<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RbacSyncSeeder::class, // create roles/permissions FIRST
            UserSeeder::class,     // then create/assign the admin user
            // ProvinceSeeder::class,
            // DistrictSeeder::class,

            // ResourceSeeder::class,
        ]);
    }
}
