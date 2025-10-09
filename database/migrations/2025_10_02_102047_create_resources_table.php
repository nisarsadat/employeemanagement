<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('resources', function (Blueprint $table) {
            $table->id();

            $table->string('picture')->nullable();

            // introduction
            $table->string('name');
            $table->string('father_name')->nullable();
            $table->string('grand_father_name')->nullable();
            $table->string('nickname')->nullable();
            $table->string('tazkira')->nullable();
            $table->string('code_number')->nullable()->index();

            // addresses
            $table->string('main_province')->nullable();
            $table->string('main_district')->nullable();
            $table->string('main_village')->nullable();

            $table->string('now_province')->nullable();
            $table->string('now_district')->nullable();
            $table->string('now_village')->nullable();

            // job & provision place
            $table->string('old_job')->nullable();
            $table->string('new_job')->nullable();
            $table->string('job_place')->nullable();

            $table->string('zone')->nullable();
            $table->string('hawza')->nullable();
            $table->string('specific_place')->nullable();

            // others
            $table->string('phone_number')->nullable();
            $table->enum('activity', ['غیر فعال', 'فعال'])->nullable();
            $table->enum('halat_madani', ['مجرد', 'متاهل'])->nullable();
            $table->string('age')->nullable();
            $table->string('study_status')->nullable();
            $table->string('resource_saved_date')->nullable();
            $table->string('resource_activity_type')->nullable();
            $table->string('resource_cover')->nullable();
            $table->string('language')->nullable();
            $table->string('nation')->nullable();
            $table->string('project')->nullable();

            // relations
            $table->foreignId('resedint_id')->nullable()
                ->constrained('resedints')->nullOnDelete();

            $table->foreignId('officeemployee_id')->nullable()
                ->constrained('office_employees')->nullOnDelete();

            $table->text('notes')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resources');
    }
};
