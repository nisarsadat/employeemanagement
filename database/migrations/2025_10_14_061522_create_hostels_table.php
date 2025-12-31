<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('hostels', function (Blueprint $table) {
            $table->id();

            // Hostel
            $table->string('hostel_name');
            $table->string('hostel_type')->nullable();
            $table->string('people_quantity')->nullable();
            $table->string('people_type')->nullable();

            // Responsible
            $table->string('responsible_name')->nullable();
            $table->string('responsible_f_name')->nullable();
            $table->string('responsible_position')->nullable();
            $table->string('responsible_phone')->nullable();

            // Address
            $table->string('province')->nullable();
            $table->string('zone')->nullable();
            $table->string('district')->nullable();
            $table->string('village')->nullable();
            $table->string('specific_place')->nullable();
            $table->string('coordenates')->nullable(); // kept spelling as provided

            // Other
            $table->text('note')->nullable();
            $table->string('Province_addr')->nullable();

            // Link to resources
            $table->foreignId('resource_id')
                ->nullable()
                ->constrained('resources')
                ->cascadeOnDelete();

            // ✅ unique composite (exact fields you listed)
            $table->unique([
                'hostel_name',
                'hostel_type',
                'people_quantity',
                'people_type',
                'province',
                'zone',
                'district',
                'village',
            ], 'hostels_unique_key');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hostels');
    }
};
