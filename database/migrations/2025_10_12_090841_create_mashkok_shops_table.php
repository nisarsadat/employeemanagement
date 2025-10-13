<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('mashkok_shops', function (Blueprint $table) {
            $table->id();

            $table->string('province')->nullable();
            $table->string('zone')->nullable();
            $table->string('district')->nullable();
            $table->string('village')->nullable();
            $table->string('specific_place')->nullable();
            $table->string('market_name')->nullable();
            $table->string('coordinates')->nullable();

            $table->string('name');
            $table->string('father_name')->nullable();
            $table->string('main_province')->nullable();
            $table->string('phone_numbers')->nullable();

            $table->string('shop_type')->nullable();
            $table->string('mashkok_type')->nullable();

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
        Schema::dropIfExists('mashkok_shops');
    }
};
