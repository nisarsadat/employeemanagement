<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('office_employees', function (Blueprint $table) {
            $table->id();

            $table->string('picture' , 500)->nullable();

            $table->string('name');
            $table->string('father_name')->nullable();
            $table->string('grand_father_name')->nullable();
            $table->string('nick_name')->nullable();
            $table->string('code_number')->nullable()->unique();

            $table->string('bast')->nullable();
            $table->string('level')->nullable();
            $table->text('history_jahad')->nullable();
            $table->string('dilgay_mesher')->nullable();
            $table->text('jahadi_mawaqaf')->nullable();
            $table->text('jahad_time_responsibility')->nullable();
            $table->string('tazkira')->nullable();
            $table->string('blood_group')->nullable();

            $table->string('jahad_place')->nullable();
            $table->string('main_province')->nullable();
            $table->string('main_district')->nullable();
            $table->string('main_village')->nullable();

            $table->string('now_province')->nullable();
            $table->string('now_district')->nullable();
            $table->string('now_village')->nullable();
            $table->string('now_specific_place')->nullable();
            $table->string('now_street_number')->nullable();
            $table->string('now_home_number')->nullable();

            $table->string('specific_job')->nullable();
            $table->string('qisem')->nullable();
            $table->string('zone')->nullable();
            $table->string('hawza')->nullable();
            $table->string('makhafat')->nullable();
            $table->string('makafat')->nullable();

            $table->string('madani_halat')->nullable();
            $table->text('activity')->nullable();
            $table->string('relegy_study')->nullable();
            $table->string('new_study')->nullable();
            $table->string('faculty')->nullable();
            $table->string('nizami')->nullable();
            $table->string('job_type')->nullable();
            $table->text('gun_knowledge')->nullable();

            $table->unsignedSmallInteger('age')->nullable();
            $table->string('language')->nullable();
            $table->string('specific_skills')->nullable();
            $table->string('nation')->nullable();

            $table->string('phone_number')->nullable();
            $table->string('Province_addr')->nullable();

            $table->string('coordinates')->nullable()
                  ->comment('latitude,longitude or address reference');

            $table->date('join_date')->nullable();
            $table->date('left_date')->nullable();

            $table->text('note')->nullable();

            $table->timestamps();

            $table->unique([
                'name',
                'father_name',
                'grand_father_name',
                'main_province',
                'main_district',
                'main_village',
            ], 'office_employees_identity_unique');
        });

        // ✅ Start ID from 100
        DB::statement("ALTER TABLE office_employees AUTO_INCREMENT = 101;");
    }

    public function down(): void
    {
        Schema::dropIfExists('office_employees');
    }
};
