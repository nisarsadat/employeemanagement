<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('masjids', function (Blueprint $table) {
            $table->id();

            // Masjid details
            $table->string('masjid_name')->nullable();
            $table->string('masjid_type')->nullable();

            // Responsible person
            $table->string('responsible_name')->nullable();
            $table->string('responsible_f_name')->nullable();
            $table->string('responsible_position')->nullable();
            $table->string('responsible_mazhab')->nullable();
            $table->string('responsible_old_job')->nullable();
            $table->string('responsible_now_job')->nullable();
            $table->string('responsible_tarfdary')->nullable();

            // Responsible address (main)
            $table->string('responsible_main_province')->nullable();
            $table->string('responsible_main_district')->nullable();
            $table->string('responsible_main_village')->nullable();

            // Responsible address (now)
            $table->string('responsible_now_province')->nullable();
            $table->string('responsible_now_district')->nullable();
            $table->string('responsible_now_village')->nullable();

            // Masjid info
            $table->string('masjid_people_mazhab')->nullable();
            $table->integer('masjid_homes_quantity')->nullable();

            // Masjid address
            $table->string('masjid_province')->nullable();
            $table->string('masjid_zone')->nullable();
            $table->string('masjid_district')->nullable();
            $table->string('masjid_village')->nullable();
            $table->string('masjid_specific_place')->nullable();
            $table->string('masjid_street_number')->nullable();
            $table->string('masjid_coordinates')->nullable();

            // Common fields
            $table->foreignId('resource_id')->nullable()->constrained('resources')->nullOnDelete();
            $table->text('note')->nullable();
            $table->string('Province_addr')->nullable();

            $table->timestamps();

            // ✅ UNIQUE SIGNATURE (composite unique) based on your selected fields
            $table->unique([
                'masjid_name',
                'masjid_type',
                'masjid_province',
                'masjid_zone',
                'masjid_district',
                'masjid_village',
            ], 'masjids_unique_signature');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('masjids');
    }
};
