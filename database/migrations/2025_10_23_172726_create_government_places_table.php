<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('government_places', function (Blueprint $table) {
            $table->id();

            $table->string('government_place_name')->nullable();
            $table->integer('daily_clientele')->nullable();

            $table->string('responsible_name')->nullable();
            $table->string('responsible_f_name')->nullable();
            $table->string('responsible_possition')->nullable();
            $table->string('responsible_phone')->nullable();

            $table->string('province')->nullable();
            $table->string('zone')->nullable();
            $table->string('district')->nullable();
            $table->string('village')->nullable();
            $table->string('specefic_place')->nullable();
            $table->string('coordinates')->nullable();

            $table->text('tafseel')->nullable();
            $table->string('amnyatee_khala')->nullable();
            $table->integer('guard_quantity')->nullable();
            $table->string('Province_addr')->nullable();

            $table->foreignId('resource_id')->nullable()->constrained('resources')->nullOnDelete();
            $table->text('note')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('government_places');
    }
};
