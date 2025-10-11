<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('fasads', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // required

            // Optional profile/contact fields (from your picture)
            $table->string('picture')->nullable();
            $table->string('nick_name')->nullable();
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
            $table->string('specific_place')->nullable();
            $table->string('street_number')->nullable();
            $table->string('home_number')->nullable();

            // Misc
            $table->string('jabha')->nullable();
            $table->string('supplier_name')->nullable();
            $table->string('responsibility')->nullable(); // normalized

            // Right column fields
            $table->string('activity_zone')->nullable();
            $table->string('friends_number')->nullable();
            $table->string('phone_number')->nullable();
            $table->string('bond_distance')->nullable(); // normalized
            $table->string('last_band_reason')->nullable();
            $table->string('band_place')->nullable();
            $table->string('now_job')->nullable();
            $table->string('resource_code')->nullable();
            $table->string('resource_phone')->nullable();

            // Link to existing resources table
            $table->foreignId('resource_id')
                ->nullable()
                ->constrained('resources')
                ->cascadeOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fasads');
    }
};
