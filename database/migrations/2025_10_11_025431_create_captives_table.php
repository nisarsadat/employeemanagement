<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('captives', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // required

            $table->string('picture')->nullable();
            $table->string('nick_name')->nullable();
            $table->string('father_name')->nullable();

            // main address
            $table->string('main_province')->nullable();
            $table->string('main_district')->nullable();
            $table->string('main_village')->nullable();

            // current address
            $table->string('now_province')->nullable();
            $table->string('now_zone')->nullable();
            $table->string('now_district')->nullable();
            $table->string('now_village')->nullable();
            $table->string('specific_place')->nullable();
            $table->string('street_number')->nullable();
            $table->string('home_number')->nullable();

            $table->string('specific_form')->nullable();
            $table->string('band_distince')->nullable();
            $table->string('now_activity')->nullable();
            $table->string('phone_number')->nullable();
            $table->text('note')->nullable();
            $table->string('Province_addr')->nullable();
            $table->string('coordinates')->nullable();

            // relationship to resources table
            $table->foreignId('resource_id')
                ->nullable()
                ->constrained('resources')
                ->cascadeOnDelete();

            // ✅ composite unique constraint (same logic as others)
            $table->unique([
                'name',
                'father_name',
                'main_province',
                'main_district',
                'main_village',
            ], 'captives_identity_unique');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('captives');
    }
};
