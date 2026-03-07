<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('mahajerats', function (Blueprint $table) {
            $table->id();
        $table->foreignId('information_id')
        ->nullable()
        ->constrained('information')   // because your table name is singular
        ->nullOnDelete();              // if Information deleted, set NULL (or use cascadeOnDelete if you prefer)
            // required
            $table->string('name');

            // from your picture (keep spellings as-is)
            $table->string('picture' , 500)->nullable();
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
            $table->string('Province_addr')->nullable();

            // relation to resources (resource_code/phone come from here)
            $table->foreignId('resource_id')
                ->nullable()
                ->constrained('resources')
                ->cascadeOnDelete();

            $table->text('note')->nullable();

            // ✅ UNIQUE (as you requested)
            $table->unique([
                'name',
                'father_name',
                'main_province',
                'main_district',
                'main_village',
            ], 'mahajerats_identity_unique');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mahajerats');
    }
};
