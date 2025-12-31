<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('donelostplaces', function (Blueprint $table) {
            $table->id();

            // Top
            $table->string('place_name')->nullable();

            // Lost person info
            $table->string('lost_person_name')->nullable();
            $table->string('lost_person_f_name')->nullable();
            $table->string('lost_person_phone')->nullable();
            $table->string('lost_person_job')->nullable();

            // Lost person's main address
            $table->string('main_province')->nullable();
            $table->string('main_district')->nullable();
            $table->string('main_village')->nullable();

            // Lost person's current address
            $table->string('now_province')->nullable();
            $table->string('now_district')->nullable();
            $table->string('now_village')->nullable();

            // Place location
            $table->string('province')->nullable();
            $table->string('zone')->nullable();
            $table->string('district')->nullable();
            $table->string('village')->nullable();
            $table->string('specific_place')->nullable();
            $table->string('coordinates')->nullable();

            // Other
            $table->integer('lost_quantity')->nullable();
            $table->string('Province_addr')->nullable();

            // Resource link + note
            $table->foreignId('resource_id')->nullable()->constrained('resources')->nullOnDelete();
            $table->text('note')->nullable();

            $table->timestamps();

            // ✅ UNIQUE SIGNATURE (composite unique) based on your selected fields
            $table->unique([
                'place_name',
                'lost_person_name',
                'lost_person_f_name',
                'lost_person_phone',
                'lost_person_job',
                'main_province',
                'main_district',
                'main_village',
            ], 'donelostplaces_unique_signature');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('donelostplaces');
    }
};
