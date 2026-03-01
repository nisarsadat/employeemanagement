<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('hotels', function (Blueprint $table) {
            $table->id();

            // Hotel
            $table->string('hotel_name');
            $table->string('type')->nullable();

            // Responsible
            $table->string('picture')->nullable();
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
            $table->string('cordenate')->nullable(); // kept spelling as provided

            // Other
            $table->string('employee_quantity')->nullable();
            $table->text('note')->nullable();
            $table->string('Province_addr')->nullable();

            // Link to resources
            $table->foreignId('resource_id')
                ->nullable()
                ->constrained('resources')
                ->cascadeOnDelete();
            $table->unique('resource_id');

            // ✅ unique composite (exact fields you listed)
            $table->unique([
                'hotel_name',
                'type',
                'province',
                'zone',
                'district',
                'village',
            ], 'hotels_unique_key');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hotels');
    }
};
