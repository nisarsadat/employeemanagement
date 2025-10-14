<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('madrasas', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->string('madrasa_type')->nullable();

            $table->string('responsible_name')->nullable();
            $table->string('responsible_f_name')->nullable();
            $table->string('responsible_position')->nullable();
            $table->string('responsible_phone')->nullable();
            $table->string('responsible_main_province')->nullable();
            $table->string('tarafdari_type_responsible')->nullable();

            $table->string('mazhab')->nullable();
            $table->string('male_teacher_quantity')->nullable();
            $table->string('female_teacher_quantity')->nullable();
            $table->string('male_students_quantity')->nullable();
            $table->string('female_students_quantity')->nullable();

            $table->string('province')->nullable();
            $table->string('zone')->nullable();
            $table->string('district')->nullable();
            $table->string('village')->nullable();
            $table->string('specific_place')->nullable();
            $table->string('street_number')->nullable();
            $table->string('coordinates')->nullable();

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
        Schema::dropIfExists('madrasas');
    }
};
