<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('sarayanos', function (Blueprint $table) {
            $table->id();

            // Saray info
            $table->string('saray_name');
            $table->string('picture' , 500)->nullable();
            $table->string('saray_type')->nullable();

            // Responsible person info
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
            $table->string('cordenates')->nullable();

            // Other info
            $table->string('employee_quantity')->nullable();
            $table->text('note')->nullable();
            $table->string('Province_addr')->nullable();

            // Resource relation
            $table->foreignId('resource_id')
                ->nullable()
                ->constrained('resources')
                ->cascadeOnDelete();
            $table->unique('resource_id');

            // ✅ unique composite (exact fields you listed)
            $table->unique([
                'saray_name',
                'saray_type',
                'province',
                'zone',
                'district',
                'village',
            ], 'sarayanos_unique_key');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sarayanos');
    }
};
