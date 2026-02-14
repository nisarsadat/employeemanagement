<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('mashkok_people', function (Blueprint $table) {
            $table->id();

            $table->string('picture')->nullable();
            $table->string('name');
            $table->string('father_name')->nullable();

            // Main address
            $table->string('main_province')->nullable();
            $table->string('main_district')->nullable();
            $table->string('main_village')->nullable();

            // Current address
            $table->string('now_province')->nullable();
            $table->string('now_zone')->nullable();
            $table->string('now_districts')->nullable();
            $table->string('now_village')->nullable();
            $table->string('now_specific_place')->nullable();
            $table->string('now_street_number')->nullable();
            $table->string('now_home_number')->nullable();
            $table->string('coordinates')->nullable();
            $table->string('mashkokyat_type')->nullable();

            // Work & contact
            $table->string('old_job')->nullable();
            $table->string('now_job')->nullable();
            $table->string('phone_number')->nullable();
            $table->string('mushkokyat_points')->nullable();

            $table->text('note')->nullable();
            $table->text('tafseel')->nullable();
            $table->string('Province_addr')->nullable();

            // Resource relation
            $table->foreignId('resource_id')
                ->nullable()
                ->constrained('resources')
                ->cascadeOnDelete();

            // ✅ unique composite (exact fields you listed)
            $table->unique([
                'name',
                'father_name',
                'main_province',
                'main_district',
                'main_village',
            ], 'mashkok_people_unique_key');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mashkok_people');
    }
};
