<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('fasads', function (Blueprint $table) {
        $table->id();
        $table->foreignId('information_id')
        ->nullable()
        ->constrained('information')   // because your table name is singular
        ->nullOnDelete();              // if Information deleted, set NULL (or use cascadeOnDelete if you prefer)
        $table->string('name');
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
        $table->string('responsibility')->nullable();

        // Right column
        $table->string('activity_zone')->nullable();
        $table->string('friends_number')->nullable();
        $table->string('phone_number')->nullable();
        $table->string('bond_distance')->nullable();
        $table->string('last_band_reason')->nullable();
        $table->string('band_place')->nullable();
        $table->string('now_job')->nullable();
        $table->string('resource_code')->nullable();
        $table->string('resource_phone')->nullable();
        $table->string('Province_addr')->nullable();
        $table->string('coordinates')->nullable();
        $table->string('tafseel')->nullable();
        $table->string('notes')->nullable();

        // FK
        $table->foreignId('resource_id')
            ->nullable()
            ->constrained('resources')
            ->cascadeOnDelete();

        // ✅ composite unique constraint
        $table->unique([
            'name',
            'father_name',
            'main_province',
            'main_district',
            'main_village'
        ], 'fasads_identity_unique');

        $table->timestamps();
    });

    }

    public function down(): void
    {
        Schema::dropIfExists('fasads');
    }
};
