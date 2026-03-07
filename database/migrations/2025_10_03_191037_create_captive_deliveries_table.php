<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('captive_deliveries', function (Blueprint $table) {
            $table->id();

            // picture path (nullable)
            $table->string('picture', 500)->nullable();
            // intro
            $table->string('name')->nullable();
            $table->string('father_name')->nullable();
            $table->string('nickname')->nullable();
            $table->string('grand_father_name')->nullable();

            // addresses
            $table->string('main_province')->nullable();
            $table->string('main_district')->nullable();
            $table->string('main_village')->nullable();

            $table->string('now_province')->nullable();
            $table->string('now_district')->nullable();
            $table->string('now_village')->nullable();

            // other
            $table->string('halat_madani')->nullable();
            $table->string('age')->nullable(); // keep string to match front
            $table->string('tazkira')->nullable();
            $table->text('criminal_history')->nullable();

            $table->string('old_job')->nullable();
            $table->string('new_job')->nullable();

            $table->string('language')->nullable();
            $table->string('scientific_level')->nullable();

            $table->string('captive_place')->nullable();
            $table->string('captive_marja')->nullable();
            $table->string('captive_date')->nullable(); // string as requested
            $table->string('captive_hour')->nullable();

            $table->string('suspect_type')->nullable();
            $table->string('phone_number')->nullable();
            $table->string('captive_halat')->nullable();
            $table->text('suspect_tools')->nullable();

            $table->text('notes')->nullable();
            $table->text('captive_reason')->nullable();
            $table->text('taslimi_warkawonky_person')->nullable();
            $table->text('taslimi_akhistonky_person')->nullable();
            $table->text('taslimi_akhistonky_organ')->nullable();
            $table->text('maznonyat_type')->nullable();
            $table->text('coordinates')->nullable();
            $table->string('Province_addr')->nullable();

            $table->timestamps();

            /**
             * ✅ UNIQUE (Composite)
             * Uniqueness is based on the exact fields you listed:
             * intro + main address
             */
            $table->unique(
                [
                    'name',
                    'father_name',
                    'nickname',
                    'grand_father_name',
                    'main_province',
                    'main_district',
                    'main_village',
                ],
                'captive_deliveries_intro_mainaddr_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('captive_deliveries');
    }
};
