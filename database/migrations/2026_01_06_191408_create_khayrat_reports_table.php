<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('khayrat_reports', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('resource_id')->nullable();
            $table->unsignedBigInteger('office_employee_id')->nullable();

            $table->string('zone')->nullable();
            $table->string('hawza')->nullable();

            // ✅ keep your exact field name
            $table->string('specefic_place')->nullable();

            $table->date('saved_date')->nullable();

            $table->timestamps();

            // Optional (if you have these tables)
            $table->foreign('resource_id')->references('id')->on('resources')->nullOnDelete();
            $table->foreign('office_employee_id')->references('id')->on('office_employees')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('khayrat_reports');
    }
};
