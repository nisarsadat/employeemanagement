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

                // Main relations (unchanged)
                $table->foreignId('resource_id')->nullable()->constrained('resources')->cascadeOnDelete();
                $table->foreignId('office_employee_id')->nullable()->constrained('office_employees')->cascadeOnDelete();

                // New input fields (renamed to your spec)
                $table->string('event_type')->nullable();
                $table->string('event_specific_type')->nullable();
                $table->string('target_person')->nullable();
                $table->string('criminal')->nullable();
                $table->string('event_date')->nullable();
                $table->string('event_hour')->nullable();

                $table->string('event_zone')->nullable();
                $table->string('event_hawza')->nullable();
                $table->string('event_specific_place')->nullable();

                $table->string('talafat_jani')->nullable();
                $table->string('talafat_mali')->nullable();

                $table->text('tafseel')->nullable();
                $table->text('talks')->nullable();
                $table->string('coordinates')->nullable();
                $table->text('note')->nullable();


                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
