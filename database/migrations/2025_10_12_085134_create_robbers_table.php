<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('robbers', function (Blueprint $table) {
            $table->id();

            $table->string('nickname')->nullable();
            $table->string('name');
            $table->string('father_name')->nullable();

            $table->string('main_province')->nullable();
            $table->string('main_district')->nullable();
            $table->string('main_village')->nullable();

            $table->string('now_province')->nullable();
            $table->string('now_zone')->nullable();
            $table->string('now_district')->nullable();
            $table->string('now_village')->nullable();
            $table->string('now_specific_place')->nullable();
            $table->string('now_street_number')->nullable();
            $table->string('now_home_number')->nullable();

            $table->string('old_job')->nullable();
            $table->string('now_job')->nullable();
            $table->string('supplier')->nullable();
            $table->string('phone_number')->nullable();
            $table->text('history_form')->nullable();

            $table->text('note')->nullable();

            $table->foreignId('resource_id')
                  ->nullable()
                  ->constrained('resources')
                  ->cascadeOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('robbers');
    }
};
