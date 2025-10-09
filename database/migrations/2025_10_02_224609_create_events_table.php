<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('events')) {
            Schema::create('events', function (Blueprint $table) {
                $table->id();

                // Relationships
                $table->foreignId('resource_id')->nullable()->constrained('resources')->cascadeOnDelete();
                $table->foreignId('office_employee_id')->nullable()->constrained('office_employees')->cascadeOnDelete();
                $table->foreignId('zone_employee_id')->nullable()->constrained('zone_employees')->cascadeOnDelete();

                // Core info
                $table->string('event_type')->nullable(); // جنایي / سیاسي / ...
                $table->string('event_specific_type')->nullable();
                $table->string('target_person')->nullable();
                $table->string('who_did')->nullable();
                $table->string('event_date')->nullable(); // store as string
                $table->string('event_hour')->nullable();

                // Place info
                $table->string('zone')->nullable();
                $table->string('hawza')->nullable();
                $table->string('specific_place')->nullable();

                // Talafat
                $table->string('jani_talafat')->nullable();
                $table->string('mali_talafat')->nullable();

                // Additional fields
                $table->foreignId('event_office_employee_code')->nullable()->constrained('office_employees')->nullOnDelete();
                $table->foreignId('event_zone_employee_code')->nullable()->constrained('zone_employees')->nullOnDelete();


                $table->text('tafseel')->nullable();
                $table->text('talks')->nullable();
                $table->text('notes')->nullable();

                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
