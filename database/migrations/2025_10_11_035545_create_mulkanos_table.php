<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('mulkanos', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // required

            $table->string('picture' , 500)->nullable();
            $table->string('father_name')->nullable();
            $table->string('grand_father_name')->nullable();

            // main address
            $table->string('main_province')->nullable();
            $table->string('main_district')->nullable();
            $table->string('main_village')->nullable();

            // current address
            $table->string('now_province')->nullable();
            $table->string('now_zone')->nullable();
            $table->string('now_district')->nullable();
            $table->string('now_village')->nullable();
            $table->string('specific_place')->nullable();
            $table->string('street_number')->nullable();
            $table->string('home_number')->nullable();
            $table->string('guzar')->nullable();
            $table->string('hawza')->nullable();
            $table->string('nahya')->nullable();
            $table->string('street')->nullable();

            // misc
            $table->string('tafseel_sahi')->nullable();
            $table->string('street_amount')->nullable();
            $table->string('old_job')->nullable();
            $table->string('new_job')->nullable();
            $table->string('agent_quantity')->nullable();
            $table->string('home_quantity')->nullable();
            $table->string('phone_number')->nullable();
            $table->string('jorm_history')->nullable();
            $table->string('tanzim')->nullable();
            $table->string('Province_addr')->nullable();
            $table->string('coordinates')->nullable();

            // relation to resources
            $table->foreignId('resource_id')
                ->nullable()
                ->constrained('resources')
                ->cascadeOnDelete();
            $table->unique('resource_id');

            $table->text('notes')->nullable();

            // ✅ composite unique constraint (same logic)
            $table->unique([
                'name',
                'father_name',
                'main_province',
                'main_district',
                'main_village',
            ], 'mulkanos_identity_unique');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mulkanos');
    }
};
