<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('security_assessments', function (Blueprint $table) {
            $table->id();

            $table->string('place_name')->nullable();
            $table->string('responsible_owner_name')->nullable();

            $table->string('province')->nullable();
            $table->string('district')->nullable();
            $table->string('specific_place')->nullable();
            $table->string('coordinates')->nullable();
            $table->text('north')->nullable();
            $table->text('south')->nullable();
            $table->text('east')->nullable();
            $table->text('west')->nullable();

            $table->unsignedInteger('daily_visitors_count')->nullable();

            $table->text('risk_places')->nullable();
            $table->text('vulnerable_places')->nullable();
            $table->text('security_gaps')->nullable();
            $table->text('weak_points')->nullable();
            $table->text('security_incidents')->nullable();

            $table->string('entrance_gate')->nullable();
            $table->string('exit_gate')->nullable();
            $table->string('emergency_gate')->nullable();
            $table->string('secondary_gate')->nullable();

            $table->string('physical_check_status')->nullable();
            $table->string('technical_check_status')->nullable();
            $table->string('security_cameras')->nullable();

            $table->unsignedInteger('guards_count')->nullable();
            $table->unsignedInteger('active_towers_count')->nullable();
            $table->unsignedInteger('active_guard_posts_count')->nullable();

            $table->string('security_responsible_name')->nullable();
            $table->string('security_responsible_phone')->nullable();

            $table->text('other_problems')->nullable();

            $table->text('note')->nullable();

            $table->timestamps();

            $table->index('place_name');
            $table->index('responsible_owner_name');
            $table->index('province');
            $table->index('district');
        });

        Schema::create('security_assessment_office_employee', function (Blueprint $table) {
            $table->id();

            $table->foreignId('security_assessment_id');
            $table->foreignId('office_employee_id');

            $table->foreign('security_assessment_id', 'sao_assessment_fk')
                ->references('id')
                ->on('security_assessments')
                ->cascadeOnDelete();

            $table->foreign('office_employee_id', 'sao_employee_fk')
                ->references('id')
                ->on('office_employees')
                ->cascadeOnDelete();

            $table->timestamps();

            $table->unique(
                ['security_assessment_id', 'office_employee_id'],
                'security_assessment_employee_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('security_assessment_office_employee');
        Schema::dropIfExists('security_assessments');
    }
};
