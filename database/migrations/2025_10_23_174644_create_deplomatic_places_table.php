<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('deplomatic_places', function (Blueprint $table) {
            $table->id();

            $table->string('deplomatic_place_name')->nullable();
            $table->string('activity_type')->nullable();
            $table->string('activity_place')->nullable();

            $table->integer('enter_male_quantity')->nullable();
            $table->integer('outer_male_quantity')->nullable();
            $table->integer('enter_female_quantity')->nullable();
            $table->integer('outer_female_quantity')->nullable();

            $table->string('responsible_name')->nullable();
            $table->string('responsible_f_name')->nullable();
            $table->string('responsible_possition')->nullable(); // keep spelling
            $table->string('responsible_country')->nullable();
            $table->string('responsible_phone')->nullable();

            $table->string('province')->nullable();
            $table->string('zone')->nullable();
            $table->string('district')->nullable();
            $table->string('village')->nullable();
            $table->string('specefic_place')->nullable();
            $table->string('street_number')->nullable();
            $table->string('home_number')->nullable();

            $table->integer('guard_quantity')->nullable();
            $table->string('coordinates')->nullable();
            $table->string('Province_addr')->nullable();

            $table->foreignId('resource_id')->nullable()->constrained('resources')->nullOnDelete();
            $table->text('note')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deplomatic_places');
    }
};
