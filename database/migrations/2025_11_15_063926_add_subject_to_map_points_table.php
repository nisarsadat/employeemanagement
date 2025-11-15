<?php
// database/migrations/xxxx_xx_xx_xxxxxx_add_subject_to_map_points_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('map_points', function (Blueprint $table) {
            $table->string('subject_type')->nullable()->index();
            $table->unsignedBigInteger('subject_id')->nullable()->index();
        });
    }

    public function down(): void
    {
        Schema::table('map_points', function (Blueprint $table) {
            $table->dropColumn(['subject_type', 'subject_id']);
        });
    }
};
