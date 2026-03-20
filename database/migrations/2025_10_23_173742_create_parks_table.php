<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('parks', function (Blueprint $table) {
            $table->id();

            $table->string('park_name')->nullable();
            $table->string('park_type')->nullable();
            $table->integer('dayly_clientele')->nullable();

            $table->string('responsible_name')->nullable();
            $table->string('responsible_f_name')->nullable();
            $table->string('responsible_phone')->nullable();
            $table->string('responsible_possition')->nullable();
            $table->string('picture' , 500)->nullable();

            $table->string('province')->nullable();
            $table->string('district')->nullable();
            $table->string('zone')->nullable();
            $table->string('village')->nullable();
            $table->string('specefic_place')->nullable();
            $table->string('coordinates')->nullable();

            $table->integer('employee_quantity')->nullable();
            $table->integer('guard_quantity')->nullable();
            $table->string('Province_addr')->nullable();

            $table->foreignId('resource_id')->nullable()->constrained('resources')->nullOnDelete();
            $table->unique('resource_id');
            $table->text('notes')->nullable();

            $table->timestamps();

            // ✅ UNIQUE SIGNATURE (composite unique)
            $table->unique([
                'park_name',
                'park_type',
                'province',
                'zone',
                'village',
            ], 'parks_unique_signature');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('parks');
    }
};
