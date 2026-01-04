<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('districts', function (Blueprint $table) {
            $table->id(); // BIGINT UNSIGNED

            $table->string('name');

            // ✅ correct FK type matches provinces.id automatically
            $table->foreignId('province_id')
                ->constrained('provinces')
                ->cascadeOnDelete();

            // ✅ prevent duplicates inside one province
            $table->unique(['province_id', 'name'], 'districts_province_name_unique');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('districts');
    }
};
