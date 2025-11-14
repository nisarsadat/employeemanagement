<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('soldiers', function (Blueprint $table) {
            $table->id();

            // required
            $table->string('name');

            // from your picture (kept spellings close to your list)
            $table->string('father_name')->nullable();
            $table->string('main_province')->nullable();
            $table->string('main_district')->nullable();
            $table->string('main_village')->nullable();

            $table->string('now_province')->nullable();
            $table->string('now_zone')->nullable();
            $table->string('now_district')->nullable();
            $table->string('now_village')->nullable();
            $table->string('specific_place')->nullable();
            $table->string('street_number')->nullable();
            $table->string('home_number')->nullable();

            $table->string('old_job')->nullable();
            $table->string('now_job')->nullable();
            $table->string('guard_amount')->nullable();
            $table->string('phone_number')->nullable();
            $table->string('Province_addr')->nullable();


            // relation to resources (code/phone derived from this)
            $table->foreignId('resource_id')
                  ->nullable()
                  ->constrained('resources')
                  ->cascadeOnDelete();

            $table->text('notes')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('soldiers');
    }
};
