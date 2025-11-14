<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tujarans', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->string('father_name')->nullable();

            // Main address
            $table->string('main_province')->nullable();
            $table->string('main_district')->nullable();
            $table->string('main_village')->nullable();

            // Current address
            $table->string('now_province')->nullable();
            $table->string('now_zone')->nullable();
            $table->string('now_district')->nullable();
            $table->string('now_village')->nullable();
            $table->string('now_specific_place')->nullable();
            $table->string('now_street_number')->nullable();
            $table->string('now_home_number')->nullable();

            // Work & org
            $table->string('old_job')->nullable();
            $table->string('now_job')->nullable();
            $table->string('organ')->nullable();

            // Other
            $table->string('guard_guntity')->nullable(); // kept spelling
            $table->string('phone_number')->nullable();
            $table->string('people_type')->nullable();

            $table->text('note')->nullable();
            $table->string('Province_addr')->nullable();


            // Resource relation
            $table->foreignId('resource_id')
                ->nullable()
                ->constrained('resources')
                ->cascadeOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tujarans');
    }
};
