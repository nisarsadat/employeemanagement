<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('security_assessments', function (Blueprint $table) {
            $table->id();

            $table->string('place_name');
            $table->string('responsible_owner_name');

            $table->string('province');
            $table->string('district');
            $table->string('specific_place');
            $table->string('coordinates');
            $table->string('direction');

            $table->unsignedInteger('daily_visitors_count');

            $table->text('risk_places');
            $table->text('vulnerable_places');
            $table->text('security_gaps');
            $table->text('weak_points');
            $table->text('security_incidents');

            $table->string('entrance_gate');
            $table->string('exit_gate');
            $table->string('emergency_gate');
            $table->string('secondary_gate');

            $table->string('physical_check_status');
            $table->string('technical_check_status');
            $table->string('security_cameras');

            $table->unsignedInteger('guards_count');
            $table->unsignedInteger('active_towers_count');
            $table->unsignedInteger('active_guard_posts_count');

            $table->string('security_responsible_name');
            $table->string('security_responsible_phone');

            $table->text('other_problems');

            $table->text('note');

            $table->timestamps();

            $table->index('place_name');
            $table->index('responsible_owner_name');
            $table->index('province');
            $table->index('district');
            $table->index('direction');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('security_assessments');
    }
};
