<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('apartments', function (Blueprint $table) {
            $table->id();

            $table->string('block_name')->nullable();
            $table->unsignedInteger('apartments_quantity')->nullable();
            $table->string('picture')->nullable();
            $table->string('shahrak_name')->nullable();

            $table->string('responsible_name')->nullable();
            $table->string('responsible_f_name')->nullable();
            $table->string('responsible_position')->nullable();
            $table->string('responsible_phone')->nullable();

            $table->string('province')->nullable();
            $table->string('zone')->nullable();
            $table->string('district')->nullable();
            $table->string('village')->nullable();
            $table->string('specific_place')->nullable();
            $table->string('coordinates')->nullable();

            $table->unsignedInteger('guard_quantity')->nullable();

            $table->text('note')->nullable();
            $table->string('Province_addr')->nullable();

            $table->foreignId('resource_id')
                ->nullable()
                ->constrained('resources')
                ->cascadeOnDelete();
            $table->unique('resource_id');

            $table->timestamps();

            // ✅ UNIQUE constraints (as you requested)
            $table->unique('block_name');
            $table->unique('apartments_quantity');
            $table->unique('shahrak_name');

            $table->unique('responsible_name');
            $table->unique('responsible_f_name');
            $table->unique('responsible_position');
            $table->unique('responsible_phone');

            $table->unique('province');
            $table->unique('zone');
            $table->unique('district');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('apartments');
    }
};
