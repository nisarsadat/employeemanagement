<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('jawasis', function (Blueprint $table) {
            $table->id();
        $table->foreignId('information_id')
        ->nullable()
        ->constrained('information')   // because your table name is singular
        ->nullOnDelete();              // if Information deleted, set NULL (or use cascadeOnDelete if you prefer)
            $table->string('picture')->nullable();
            $table->string('name')->nullable();
            $table->string('nick_name')->nullable();
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

            $table->string('coordinates')->nullable();
            $table->string('country')->nullable();

            $table->text('responsiability')->nullable();
            $table->text('done_activities')->nullable();
            $table->string('activity_place')->nullable();

            $table->integer('count_of_communication')->nullable();
            $table->string('phone_number')->nullable();

            $table->string('old_job')->nullable();
            $table->string('now_job')->nullable();

            $table->foreignId('resource_id')->nullable()->constrained()->nullOnDelete();
            $table->text('notes')->nullable();
            $table->longText('tafseel')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jawasis');
    }
};
