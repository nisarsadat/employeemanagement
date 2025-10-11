<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('mahajerats', function (Blueprint $table) {
            $table->id();

            // required
            $table->string('name');

            // from your picture (keep spellings as-is)
            $table->string('father_name')->nullable();
            $table->string('scientific_level')->nullable();

            $table->string('main_province')->nullable();
            $table->string('main_district')->nullable();
            $table->string('main_village')->nullable();

            $table->string('now_province')->nullable();
            $table->string('now_zone')->nullable();
            $table->string('now_district')->nullable();
            $table->string('now_vinage')->nullable();   // spelled like your note
            $table->string('specific_place')->nullable();
            $table->string('street_number')->nullable();
            $table->string('home_number')->nullable();
            $table->string('cordinates')->nullable();

            $table->string('country')->nullable();
            $table->string('male_amount')->nullable();
            $table->string('female_amount')->nullable();
            $table->string('phone_number')->nullable();

            // relation to resources (resource_code/phone come from here)
            $table->foreignId('resource_id')
                  ->nullable()
                  ->constrained('resources')
                  ->cascadeOnDelete();

            $table->text('note')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mahajerats');
    }
};
