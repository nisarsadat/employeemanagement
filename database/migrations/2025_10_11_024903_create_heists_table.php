<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('heists', function (Blueprint $table) {
            $table->id();
        $table->foreignId('information_id')
        ->nullable()
        ->constrained('information')   // because your table name is singular
        ->nullOnDelete();              // if Information deleted, set NULL (or use cascadeOnDelete if you prefer)
            $table->string('name'); // required

            $table->string('picture' , 500)->nullable();
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

            $table->string('coordinates')->nullable();
            $table->string('old_job')->nullable();
            $table->string('now_job')->nullable();
            $table->string('phone_number')->nullable();
            $table->string('done_roberies')->nullable();
            $table->string('gun_type')->nullable();
            $table->string('Province_addr')->nullable();

            // resource relation
            $table->foreignId('resource_id')
                ->nullable()
                ->constrained('resources')
                ->cascadeOnDelete();

            $table->text('note')->nullable();

            // ✅ composite unique constraint
            $table->unique([
                'name',
                'father_name',
                'main_province',
                'main_district',
                'main_village',
            ], 'heists_identity_unique');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('heists');
    }
};
