<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('lost_mujremeens', function (Blueprint $table) {
            $table->id();

            $table->string('picture')->nullable();
            $table->string('nickname')->nullable();
            $table->string('name');
            $table->string('father_name')->nullable();

            $table->string('main_province')->nullable();
            $table->string('main_district')->nullable();
            $table->string('main_village')->nullable();

            $table->string('now_province')->nullable();
            $table->string('now_district')->nullable();
            $table->string('now_village')->nullable();
            $table->string('now_specific_place')->nullable();
            $table->string('now_street_number')->nullable();
            $table->string('now_home_number')->nullable();

            $table->string('province')->nullable();
            $table->string('zone')->nullable();
            $table->string('district')->nullable();

            $table->string('old_job')->nullable();

            $table->text('jorm')->nullable();
            $table->text('history_jorm')->nullable();
            $table->string('lost_reason')->nullable();
            $table->string('phone_number')->nullable();

            $table->text('note')->nullable();
            $table->string('Province_addr')->nullable();
            $table->string('coordinates')->nullable();

            $table->foreignId('resource_id')
                ->nullable()
                ->constrained('resources')
                ->cascadeOnDelete();

            $table->timestamps();

            /**
             * ✅ UNIQUE (composite) for:
             * name + father_name + main_province + main_district + main_village
             */
            $table->unique(
                ['name', 'father_name', 'main_province', 'main_district', 'main_village'],
                'lost_mujremeens_identity_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lost_mujremeens');
    }
};
