<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('heists', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // required

            $table->string('picture')->nullable();
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
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('heists');
    }
};
