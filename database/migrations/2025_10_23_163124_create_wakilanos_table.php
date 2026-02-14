<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('wakilanos', function (Blueprint $table) {
            $table->id();

            // Core block
            $table->string('picture')->nullable();       // free text / GPS
            $table->string('name')->nullable();
            $table->string('f_name')->nullable();
            $table->string('grand_father_name')->nullable();

            // Main address
            $table->string('main_province')->nullable();
            $table->string('main_district')->nullable();
            $table->string('main_village')->nullable();

            // Current address
            $table->string('now_province')->nullable();
            $table->string('now_zone')->nullable();
            $table->string('now_district')->nullable();
            $table->string('now_village')->nullable();
            $table->string('now_specific_place')->nullable();
            $table->string('now_street_number')->nullable();
            $table->string('now_home_number')->nullable();

            // Misc
            $table->string('hawza')->nullable();
            $table->string('nahya')->nullable();
            $table->string('Guzar')->nullable();
            $table->string('coordinates')->nullable();
            $table->string('place_tafseel')->nullable();
            $table->string('old_job')->nullable();
            $table->string('now_job')->nullable();
            $table->integer('home_quantity')->nullable();
            $table->integer('agent_quantity')->nullable();
            $table->integer('tanzim')->nullable();
            $table->string('phone_number')->nullable();
            $table->string('history_form')->nullable();
            $table->string('Province_addr')->nullable();

            // Link to resources
            $table->foreignId('resource_id')->nullable()->constrained('resources')->nullOnDelete();

            // Notes
            $table->text('note')->nullable();

            $table->timestamps();

            // ✅ UNIQUE (composite) for requested fields
            $table->unique([
                'name',
                'f_name',
                'grand_father_name',
                'main_province',
                'main_district',
                'main_village',
            ], 'wakilanos_unique_key');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wakilanos');
    }
};
