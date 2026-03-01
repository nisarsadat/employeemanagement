<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('damage_rasan_places', function (Blueprint $table) {
            $table->id();

            $table->string('picture')->nullable();
            $table->string('province')->nullable();
            $table->string('zone')->nullable();
            $table->string('district')->nullable();
            $table->string('village')->nullable();
            $table->string('specific_place')->nullable();
            $table->string('specific_dot')->nullable();
            $table->string('coordinates')->nullable();
            $table->string('tahdid_type')->nullable();
            $table->string('percentage')->nullable();
            $table->string('damage_place')->nullable();

            $table->text('note')->nullable();
            $table->string('Province_addr')->nullable();


            $table->foreignId('resource_id')
                  ->nullable()
                  ->constrained('resources')
                  ->cascadeOnDelete();
            $table->unique('resource_id');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('damage_rasan_places');
    }
};
