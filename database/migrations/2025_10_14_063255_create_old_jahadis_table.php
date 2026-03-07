<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('old_jahadis', function (Blueprint $table) {
            $table->id();

            $table->string('picture' , 500)->nullable();
            $table->string('nick_name')->nullable();
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

            // Work & details
            $table->string('old_job')->nullable();
            $table->string('now_job')->nullable();
            $table->string('employee_quantity')->nullable();
            $table->string('supplier')->nullable();
            $table->string('phone_number')->nullable();
            $table->string('tanzim')->nullable();
            $table->string('organ')->nullable();
            $table->string('jorm_history')->nullable();

            $table->text('note')->nullable();
            $table->text('coordinates')->nullable();
            $table->string('Province_addr')->nullable();

            $table->foreignId('resource_id')
                ->nullable()
                ->constrained('resources')
                ->cascadeOnDelete();

            // ✅ unique composite (exact fields you specified)
            $table->unique([
                'nick_name',
                'name',
                'father_name',
                'main_province',
                'main_district',
                'main_village',
            ], 'old_jahadis_unique_key');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('old_jahadis');
    }
};
