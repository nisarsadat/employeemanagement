<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('saeen_fasads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('information_id')
            ->nullable()
            ->constrained('information')   // because your table name is singular
            ->nullOnDelete();              // if Information deleted, set NULL (or use cascadeOnDelete if you prefer)
            $table->string('name'); // required

            // from your picture (kept as-is)
            $table->string('picture' , 500)->nullable();
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

            $table->string('responsiability')->nullable();
            $table->string('activity_place')->nullable();
            $table->string('friends_number')->nullable();
            $table->string('phone_number')->nullable();
            $table->string('band_distince')->nullable();   // kept your exact spelling
            $table->string('band_place')->nullable();
            $table->string('old_job')->nullable();
            $table->string('now_job')->nullable();
            $table->string('resource_code')->nullable();
            $table->string('resource_phone_number')->nullable();
            $table->text('note')->nullable();
            $table->longText('tafseel')->nullable();
            $table->string('Province_addr')->nullable();
            $table->string('coordinates')->nullable();

            // relation to resources
            $table->foreignId('resource_id')
                  ->nullable()
                  ->constrained('resources')
                  ->cascadeOnDelete();

            // ✅ composite unique constraint (same logic as Fasad)
            $table->unique([
                'name',
                'father_name',
                'main_province',
                'main_district',
                'main_village',
            ], 'saeen_fasads_identity_unique');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('saeen_fasads');
    }
};
