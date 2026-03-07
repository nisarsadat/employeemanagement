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

            $table->string('picture', 500)->nullable();

            $table->string('name', 150);
            $table->string('father_name', 150)->nullable();
            $table->string('grand_father_name', 150)->nullable();
            $table->string('nick_name', 100)->nullable();
            $table->string('code_number', 50)->nullable()->unique();

            $table->string('bast', 100)->nullable();
            $table->string('level', 100)->nullable();
            $table->text('history_jahad')->nullable();
            $table->string('dilgay_mesher', 150)->nullable();
            $table->text('jahadi_mawaqaf')->nullable();
            $table->text('jahad_time_responsibility')->nullable();
            $table->string('tazkira', 100)->nullable();
            $table->string('blood_group', 20)->nullable();

            $table->string('jahad_place', 150)->nullable();
            $table->string('main_province', 100)->nullable();
            $table->string('main_district', 100)->nullable();
            $table->string('main_village', 150)->nullable();

            $table->string('now_province', 100)->nullable();
            $table->string('now_district', 100)->nullable();
            $table->string('now_village', 150)->nullable();
            $table->string('now_specific_place', 200)->nullable();
            $table->string('now_street_number', 50)->nullable();
            $table->string('now_home_number', 50)->nullable();

            $table->string('specific_job', 150)->nullable();
            $table->string('qisem', 100)->nullable();
            $table->string('zone', 100)->nullable();
            $table->string('hawza', 100)->nullable();
            $table->string('makhafat', 100)->nullable();
            $table->string('makafat', 100)->nullable();

            $table->string('madani_halat', 100)->nullable();
            $table->text('activity')->nullable();
            $table->string('relegy_study', 150)->nullable();
            $table->string('new_study', 150)->nullable();
            $table->string('faculty', 150)->nullable();
            $table->string('nizami', 100)->nullable();
            $table->string('job_type', 100)->nullable();
            $table->text('gun_knowledge')->nullable();

            $table->unsignedSmallInteger('age')->nullable();
            $table->string('language', 150)->nullable();
            $table->string('specific_skills', 200)->nullable();
            $table->string('nation', 100)->nullable();

            $table->string('phone_number', 30)->nullable();
            $table->string('Province_addr', 150)->nullable();

            $table->string('coordinates', 100)->nullable()
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

        // Start ID from 101
        DB::statement("ALTER TABLE office_employees AUTO_INCREMENT = 101;");
    }

    public function down(): void
    {
        Schema::dropIfExists('office_employees');
    }
};