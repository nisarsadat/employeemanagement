<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('complexes', function (Blueprint $table) {
            $table->id();

            // Location
            $table->string('province')->nullable();
            $table->string('zone')->nullable();
            $table->string('district')->nullable();
            $table->string('village')->nullable();
            $table->string('specefic_place')->nullable();
            $table->string('street_number')->nullable();
            $table->string('home_number')->nullable();

            // Complex info
            $table->string('complex_name')->nullable();
            $table->string('activity_type')->nullable();
            $table->string('activity_place')->nullable();
            $table->string('supplier')->nullable();

            // Flow counts
            $table->integer('enter_male_quantity')->nullable();
            $table->integer('outer_male_quantity')->nullable();
            $table->integer('enter_femal_quantity')->nullable();
            $table->integer('outer_femal_quantity')->nullable();

            // Responsible person
            $table->string('responsible_name')->nullable();
            $table->string('responsible_f_name')->nullable();
            $table->string('responsible_phone')->nullable();
            $table->string('responsible_country')->nullable();

            // Security
            $table->integer('guard_quantity')->nullable();
            $table->string('Province_addr')->nullable();
            $table->string('coordinates')->nullable();

            // Link to Resource + optional note
            $table->foreignId('resource_id')->nullable()->constrained('resources')->nullOnDelete();
            $table->text('note')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('complexes');
    }
};
