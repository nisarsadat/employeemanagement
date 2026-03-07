<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('schools', function (Blueprint $table) {
            $table->id();

            $table->string('school_name');
            $table->string('picture' , 500)->nullable();
            $table->string('school_type')->nullable();

            $table->string('principle_name')->nullable();
            $table->string('principle_f_name')->nullable();
            $table->string('principle_phone')->nullable();

            $table->unsignedInteger('male_teachers_quantity')->nullable();
            $table->unsignedInteger('female_teachers_quantity')->nullable();
            $table->unsignedInteger('male_students_quantity')->nullable();
            $table->unsignedInteger('female_students_quantity')->nullable();

            $table->string('province')->nullable();
            $table->string('zone')->nullable();
            $table->string('district')->nullable();
            $table->string('specific_place')->nullable();
            $table->string('village')->nullable();
            $table->string('street_number')->nullable();
            $table->string('coordinates')->nullable();

            $table->text('note')->nullable();
            $table->string('Province_addr')->nullable();

            $table->foreignId('resource_id')
                ->nullable()
                ->constrained('resources')
                ->cascadeOnDelete();
            $table->unique('resource_id');

            $table->timestamps();

            /**
             * ✅ UNIQUE (composite) exactly as you requested:
             * school + principle + address block
             */
            $table->unique(
                [
                    'school_name',
                    'school_type',
                    'principle_name',
                    'principle_f_name',
                    'principle_phone',
                    'province',
                    'zone',
                    'district',
                    'specific_place',
                    'village',
                    'street_number',
                ],
                'schools_identity_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('schools');
    }
};
