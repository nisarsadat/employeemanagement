<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('shoras', function (Blueprint $table) {
            $table->id();

            // Top (from your sheet)
            $table->string('shora_village')->nullable();
            $table->text('note')->nullable();

            // Core shora details
            $table->string('shora_name')->nullable();
            $table->string('shora_goal')->nullable();
            $table->integer('people_quantity')->nullable();
            $table->integer('week_meeting_quantity')->nullable();
            $table->string('regester')->nullable();                 // keeping your spelling
            $table->string('shora_meeting_place')->nullable();

            // Responsible person
            $table->string('responsible_name')->nullable();
            $table->string('responsible_f_name')->nullable();
            $table->string('responsible_job')->nullable();
            $table->string('responsible_phone')->nullable();

            // Supplier info
            $table->string('supplier_name')->nullable();
            $table->string('supplier_job')->nullable();
            $table->string('supplier_phone')->nullable();

            // Misc from sheet
            $table->string('emarat_tarfdary')->nullable();          // mirrored as read

            // Location block
            $table->string('shora_province')->nullable();
            $table->string('shora_zone')->nullable();
            $table->string('shora_district')->nullable();

            // Resource link
            $table->foreignId('resource_id')->nullable()->constrained('resources')->nullOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shoras');
    }
};
