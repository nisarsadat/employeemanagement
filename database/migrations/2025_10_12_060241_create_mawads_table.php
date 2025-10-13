<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('mawads', function (Blueprint $table) {
            $table->id();

            $table->string('mawad_type')->nullable();
            $table->string('nickname')->nullable();
            $table->string('name'); // required
            $table->string('father_name')->nullable();

            // main address
            $table->string('main_province')->nullable();
            $table->string('main_district')->nullable();
            $table->string('main_village')->nullable();

            // current address
            $table->string('now_province')->nullable();
            $table->string('now_district')->nullable();
            $table->string('now_village')->nullable();

            // extra location block
            $table->string('province')->nullable();
            $table->string('zone')->nullable();
            $table->string('district')->nullable();
            $table->string('village')->nullable();

            $table->string('specific_place')->nullable();
            $table->string('coordinates')->nullable();
            $table->string('mawad_sales_phone')->nullable();

            $table->text('history_form')->nullable();
            $table->string('supplier')->nullable();
            $table->text('note')->nullable();

            $table->foreignId('resource_id')->nullable()
                  ->constrained('resources')
                  ->cascadeOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mawads');
    }
};
