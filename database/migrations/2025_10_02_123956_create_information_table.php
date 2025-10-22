<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('information', function (Blueprint $table) {
            $table->id();

            // Foreign keys
            $table->unsignedBigInteger('resource_id')->nullable();
            $table->unsignedBigInteger('office_employee_id')->nullable();

            // Personal info
            $table->string('name')->nullable();
            $table->string('nick_name')->nullable();
            $table->string('father_name')->nullable();
            $table->string('grand_father_name')->nullable();

            // Main address
            $table->string('main_province')->nullable();
            $table->string('main_districts')->nullable();
            $table->string('main_village')->nullable();

            // Current address
            $table->string('now_province')->nullable();
            $table->string('now_district')->nullable();
            $table->string('now_village')->nullable();

            // Info types
            $table->string('syasi_info_type')->nullable();
            $table->string('jenayee_info_type')->nullable();

            // Info details
            $table->string('information_saved_date')->nullable();
            $table->string('information_saved_hour')->nullable();
            $table->string('information_degree')->nullable();

            // Text areas
            $table->text('tafseel')->nullable();
            $table->text('manager_comment')->nullable();
            $table->text('lead_drirection')->nullable();
            $table->text('talks')->nullable();
            $table->text('note')->nullable();

            $table->timestamps();

            // FK constraints
            $table->foreign('resource_id')->references('id')->on('resources')->nullOnDelete();
            $table->foreign('office_employee_id')->references('id')->on('office_employees')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('information');
    }
};
