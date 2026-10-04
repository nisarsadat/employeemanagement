<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('meetings', function (Blueprint $table) {
            $table->id();

            $table->string('meeting_title')->nullable();
            $table->string('organ')->nullable();
            $table->string('province')->nullable();
            $table->string('district')->nullable();
            $table->string('specific_place')->nullable();
            $table->string('coordinates')->nullable();

            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();

            $table->string('total_time')->nullable();

            $table->text('participant')->nullable();
            $table->text('boss_participant')->nullable();

            $table->unsignedInteger('office_employee_count')->nullable();

            $table->string('leader_name')->nullable();

            $table->text('done_actions')->nullable();
            $table->text('mashkok_situations')->nullable();
            $table->text('problems')->nullable();

            $table->string('meeting_supplier')->nullable();
            $table->string('meeting_supplier_phone')->nullable();

            $table->string('meeting_amniyat_responsable')->nullable();
            $table->string('meeting_amniyat_responsable_phone')->nullable();

            $table->unsignedInteger('amniyat_mansobine_count')->nullable();

            $table->text('note')->nullable();

            $table->timestamps();

            $table->index('meeting_title');
            $table->index('organ');
            $table->index('province');
            $table->index('district');
            $table->index('start_date');
            $table->index('end_date');
        });

        Schema::create('meeting_office_employee', function (Blueprint $table) {
            $table->id();

            $table->foreignId('meeting_id')
                ->constrained('meetings')
                ->cascadeOnDelete();

            $table->foreignId('office_employee_id')
                ->constrained('office_employees')
                ->cascadeOnDelete();

            $table->timestamps();

            $table->unique(
                ['meeting_id', 'office_employee_id'],
                'meeting_employee_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meeting_office_employee');
        Schema::dropIfExists('meetings');
    }
};
