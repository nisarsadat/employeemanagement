<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('meeting_day_points', function (Blueprint $table) {
            $table->id();
            $table->foreignId('office_employee_id')
                ->constrained('office_employees')
                ->cascadeOnDelete();
            $table->date('point_date');
            $table->unsignedTinyInteger('points')->default(1);
            $table->timestamps();

            $table->unique(
                ['office_employee_id', 'point_date'],
                'employee_day_point_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meeting_day_points');
    }
};
