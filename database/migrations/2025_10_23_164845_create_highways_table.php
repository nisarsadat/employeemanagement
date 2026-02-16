<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('highways', function (Blueprint $table) {
            $table->id();

            $table->string('high_way_name')->nullable();
            $table->string('province')->nullable();
            $table->string('zone')->nullable();
            $table->string('picture')->nullable();
            $table->string('district')->nullable();
            $table->string('village')->nullable();
            $table->string('specific_place')->nullable();
            $table->string('coordinates')->nullable();
            $table->string('Province_addr')->nullable();

            $table->foreignId('resource_id')->nullable()->constrained('resources')->nullOnDelete();
            $table->text('note')->nullable();

            $table->timestamps();

            // ✅ UNIQUE SIGNATURE (composite unique) based on your selected fields
            $table->unique([
                'high_way_name',
                'province',
                'zone',
                'district',
                'village',
            ], 'highways_unique_signature');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('highways');
    }
};
