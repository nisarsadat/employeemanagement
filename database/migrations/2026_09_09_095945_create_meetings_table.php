<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('meetings', function (Blueprint $table) {
            $table->id();

            $table->string('meeting_title');
            $table->string('organ');
            $table->string('province');
            $table->string('district');
            $table->string('specific_place');
            $table->string('coordinates');

            $table->date('start_date');
            $table->date('end_date');

            $table->string('total_time');

            $table->text('participant');
            $table->text('boss_participant');

            $table->unsignedInteger('office_employee_count');

            $table->string('leader_name');

            $table->text('done_actions');
            $table->text('mashkok_situations');
            $table->text('problems');

            $table->string('meeting_supplier');
            $table->string('meeting_supplier_phone');

            $table->string('meeting_amniyat_responsable');
            $table->string('meeting_amniyat_responsable_phone');

            $table->unsignedInteger('amniyat_mansobine_count');

            $table->text('note');

            $table->timestamps();

            $table->index('meeting_title');
            $table->index('organ');
            $table->index('province');
            $table->index('district');
            $table->index('start_date');
            $table->index('end_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meetings');
    }
};