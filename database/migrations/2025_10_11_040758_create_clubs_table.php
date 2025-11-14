<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('clubs', function (Blueprint $table) {
            $table->id();

            // required
            $table->string('name');

            // from your picture (kept spellings)
            $table->string('club_name')->nullable();
            $table->string('club_type')->nullable();
            $table->string('father_name')->nullable();
            $table->string('position')->nullable();
            $table->string('phone_number')->nullable();

            $table->string('province')->nullable();
            $table->string('zone')->nullable();
            $table->string('district')->nullable();
            $table->string('specific_place')->nullable();
            $table->string('cordinate')->nullable();
            $table->string('am_students')->nullable();
            $table->string('pm_students')->nullable();
            $table->string('Province_addr')->nullable();


            // relation to resources (code/phone derived from resource)
            $table->foreignId('resource_id')
                  ->nullable()
                  ->constrained('resources')
                  ->cascadeOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clubs');
    }
};
