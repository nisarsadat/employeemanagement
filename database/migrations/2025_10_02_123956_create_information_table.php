<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // CREATE the base table (singular)
        Schema::create('information', function (Blueprint $table) {
            $table->id();

            // foreign keys (nullable as you wanted)
            $table->unsignedBigInteger('resource_id')->nullable();
            $table->unsignedBigInteger('officeemployee_id')->nullable();
            $table->unsignedBigInteger('zone_employee_id')->nullable();
            $table->unsignedBigInteger('information_office_employee_code')->nullable();
            $table->unsignedBigInteger('infomation_zone_employee_code')->nullable();

            // main fields
            $table->string('name')->nullable();
            $table->string('father_name')->nullable();
            $table->string('nickname')->nullable();
            $table->string('grand_father_name')->nullable();

            $table->string('main_province')->nullable();
            $table->string('main_district')->nullable();
            $table->string('main_village')->nullable();

            $table->string('now_province')->nullable();
            $table->string('now_district')->nullable();
            $table->string('now_village')->nullable();

            $table->string('seyasi')->nullable();
            $table->string('jenayee')->nullable();
            $table->string('information_date')->nullable();
            $table->string('information_hour')->nullable();

            $table->string('importance_degree')->nullable();
            $table->string('information_category')->nullable();

            $table->text('tafseel')->nullable();
            $table->text('talks')->nullable();
            $table->text('outcome')->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();

            // FKs (adjust table names if yours differ)
            $table->foreign('resource_id')->references('id')->on('resources')->nullOnDelete();
            $table->foreign('officeemployee_id')->references('id')->on('office_employees')->nullOnDelete();
            $table->foreign('zone_employee_id')->references('id')->on('zone_employees')->nullOnDelete();
            $table->foreign('information_office_employee_code')->references('id')->on('office_employees')->nullOnDelete();
            $table->foreign('infomation_zone_employee_code')->references('id')->on('zone_employees')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('information');
    }
};
