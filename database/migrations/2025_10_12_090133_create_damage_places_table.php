<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('damage_places', function (Blueprint $table) {
            $table->id();

            $table->string('province')->nullable();
            $table->string('zone')->nullable();
            $table->string('district')->nullable();
            $table->string('village')->nullable();
            $table->string('specific_place')->nullable();
            $table->string('specific_dot')->nullable();
            $table->string('coordinates')->nullable();

            $table->string('tahdid_type')->nullable();
            $table->string('percentage')->nullable();
            $table->string('tahdid_place')->nullable();
            $table->string('amnyat_khala')->nullable();
            $table->text('cameesa_tafseel')->nullable();
            $table->string('guard_quantity')->nullable();

            $table->string('masool_name')->nullable();
            $table->string('masool_f_name')->nullable();
            $table->string('masool_position')->nullable();
            $table->string('masool_phone')->nullable();

            $table->text('note')->nullable();

            $table->foreignId('resource_id')
                  ->nullable()
                  ->constrained('resources')
                  ->cascadeOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('damage_places');
    }
};
