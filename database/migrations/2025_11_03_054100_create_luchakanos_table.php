<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('luchakanos', function (Blueprint $table) {
            $table->id();

            // Personal info
            $table->string('nickname')->nullable();
            $table->string('name')->nullable();
            $table->string('father_name')->nullable();
            $table->string('picture')->nullable();

            // Main Address
            $table->string('main_province')->nullable();
            $table->string('main_district')->nullable();
            $table->string('main_village')->nullable();

            // Current Address
            $table->string('now_province')->nullable();
            $table->string('now_zone')->nullable();
            $table->string('now_district')->nullable();
            $table->string('now_village')->nullable();
            $table->string('now_specific_place')->nullable();
            $table->string('now_street_number')->nullable();
            $table->string('now_home_number')->nullable();

            // Job / Activity
            $table->string('old_job')->nullable();
            $table->string('job')->nullable();
            $table->string('gun_type')->nullable();
            $table->string('phone_number')->nullable();

            // History / Notes
            $table->longText('history_jorm')->nullable();
            $table->longText('note')->nullable();
            $table->longText('coordinates')->nullable();
            $table->string('Province_addr')->nullable();

            // Relation with Resource
            $table->foreignId('resource_id')
                ->nullable()
                ->constrained('resources')
                ->nullOnDelete();

            $table->timestamps();

            /**
             * ✅ UNIQUE (composite) exactly as you requested:
             * nickname + name + father_name + main_province + main_district + main_village
             */
            $table->unique(
                ['nickname', 'name', 'father_name', 'main_province', 'main_district', 'main_village'],
                'luchakanos_identity_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('luchakanos');
    }
};
