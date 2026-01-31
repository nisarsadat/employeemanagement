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
            $table->string('nick_name')->nullable();
            $table->string('code_number')->nullable()->index();
            $table->string('tazkira')->nullable();
            $table->string('project')->nullable();
            $table->string('zone')->nullable();
            $table->string('hawza')->nullable();
            $table->string('specefic_place')->nullable();

            // main address
            $table->string('main_province')->nullable();
            $table->string('main_district')->nullable();
            $table->string('main_village')->nullable();

            // current address
            $table->string('now_province')->nullable();
            $table->string('now_district')->nullable();
            $table->string('now_village')->nullable();
            $table->string('now_specific_place')->nullable();
            $table->string('now_street_number')->nullable();
            $table->string('now_home_number')->nullable();

            // jobs
            $table->string('old_job')->nullable();
            $table->string('now_job')->nullable();
            $table->string('job_place')->nullable();

            // status & education
            $table->enum('activity', ['غیر فعال','فعال'])->nullable();
            $table->enum('halat_madani', ['مجرد','متاهل'])->nullable();
            $table->string('age')->nullable();
            $table->string('elmi_level')->nullable();
            $table->string('faculty')->nullable();
            $table->string('specific_skills')->nullable();
            $table->string('train')->nullable();
            $table->string('trust_percentage')->nullable();

            // misc
            $table->string('resource_saved_date')->nullable();
            $table->string('resource_activity_type')->nullable();

            // relation
            $table->foreignId('office_employee_id')->nullable()
                  ->constrained('office_employees')->nullOnDelete();

            $table->string('resedint_code')->nullable();
            $table->string('resource_cover')->nullable();
            $table->string('language')->nullable();
            $table->string('nation')->nullable();
            $table->string('phone_number')->nullable();
            $table->string('phone_number_1')->nullable();
            $table->string('phone_number_2')->nullable();
            $table->string('Province_addr')->nullable();

            $table->string('coordinates')->nullable();

            $table->text('note')->nullable();

            $table->timestamps();
            // ✅ Prevent duplicate resource identity (composite unique)
            $table->unique([
                'name',
                'father_name',
                'grand_father_name',
                'nick_name',
                'code_number',
                'main_province',
                'main_district',
                'main_village',
            ], 'resources_identity_unique');

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resources');
    }
};
