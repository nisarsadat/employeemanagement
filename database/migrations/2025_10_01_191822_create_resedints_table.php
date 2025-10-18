<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('resedints', function (Blueprint $table) {
            $table->id();

            $table->string('picture')->nullable();

            $table->string('name');
            $table->string('father_name')->nullable();
            $table->string('nickname')->nullable();

            $table->string('main_province')->nullable();
            $table->string('main_district')->nullable();
            $table->string('main_village')->nullable();

            $table->string('now_province')->nullable();
            $table->string('now_district')->nullable();
            $table->string('now_village')->nullable();

            $table->string('phone_number')->nullable();
            $table->string('whatsapp_number')->nullable();

            $table->string('hawza')->nullable();
            $table->string('zone')->nullable();

            $table->string('code_number')->nullable()->unique();

            $table->foreignId('officeemployee_id')
                ->nullable()
                ->constrained('office_employees')
                ->nullOnDelete();

            $table->text('notes')->nullable();

            // NEW
            $table->string('coordinates')->nullable()->comment('latitude,longitude or address reference');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resedints');
    }
};
