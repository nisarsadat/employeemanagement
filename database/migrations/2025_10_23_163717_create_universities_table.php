<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('universities', function (Blueprint $table) {
            $table->id();

            // Basic info
            $table->string('uni_name')->nullable();
            $table->string('picture')->nullable();
            $table->string('uni_type')->nullable();

            // Responsible person
            $table->string('responsible_name')->nullable();
            $table->string('responsible_f_name')->nullable();
            $table->string('responsible_position')->nullable();
            $table->string('responsible_phone')->nullable();

            // Counts
            $table->integer('male_teachers_quantity')->nullable();
            $table->integer('female_teachers_quantity')->nullable();
            $table->integer('male_students_quantity')->nullable();
            $table->integer('female_students_quantity')->nullable();

            // Location
            $table->string('province')->nullable();
            $table->string('zone')->nullable();
            $table->string('district')->nullable();
            $table->string('village')->nullable();
            $table->string('specific_place')->nullable();
            $table->string('street_number')->nullable();
            $table->string('home_number')->nullable();

            // Coordinates / notes
            $table->string('coordinates')->nullable();
            $table->string('Province_addr')->nullable();

            // Resource link
            $table->foreignId('resource_id')->nullable()->constrained('resources')->nullOnDelete();

            $table->text('note')->nullable();

            $table->timestamps();

            // ✅ UNIQUE (composite) for: uni_name + uni_type + location fields
            $table->unique([
                'uni_name',
                'uni_type',
                'province',
                'zone',
                'district',
                'village',
                'specific_place',
                'street_number',
                'home_number',
            ], 'universities_unique_key');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('universities');
    }
};
