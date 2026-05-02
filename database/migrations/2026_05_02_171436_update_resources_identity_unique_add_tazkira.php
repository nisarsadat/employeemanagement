<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE resources DROP INDEX resources_identity_unique');

        DB::statement('
            ALTER TABLE resources
            ADD UNIQUE resources_identity_unique (
                name,
                father_name,
                grand_father_name,
                nick_name,
                tazkira,
                main_province,
                main_district,
                main_village
            )
        ');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE resources DROP INDEX resources_identity_unique');

        DB::statement('
            ALTER TABLE resources
            ADD UNIQUE resources_identity_unique (
                name,
                father_name,
                grand_father_name,
                nick_name,
                main_province,
                main_district,
                main_village
            )
        ');
    }
};