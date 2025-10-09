<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('office_employees', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('father_name')->nullable();
            $table->string('nickname')->nullable();
            $table->string('main_province')->nullable();
            $table->string('main_district')->nullable();
            $table->string('main_village')->nullable();
            $table->string('now_province')->nullable();
            $table->string('now_district')->nullable();
            $table->string('now_village')->nullable();
            $table->string('releg_study')->nullable();
            $table->string('new_study')->nullable();
            $table->string('phone_number')->nullable();
            $table->string('tazkira')->nullable();
            $table->string('picture')->nullable();
            $table->string('apply_date')->nullable();
            $table->string('join_date')->nullable();
            $table->string('left_date')->nullable();
            $table->string('code_number')->nullable()->unique();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('office_employees');
    }
};
