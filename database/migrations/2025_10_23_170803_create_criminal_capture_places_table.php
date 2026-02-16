<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('criminal_capture_places', function (Blueprint $table) {
            $table->id();

            $table->string('name')->nullable();
            $table->string('father_name')->nullable();
            $table->string('job')->nullable();

            $table->string('picture')->nullable();
            $table->string('jorm_type')->nullable();
            $table->integer('criminals_quantity')->nullable();
            $table->string('office')->nullable();

            $table->string('province')->nullable();
            $table->string('zone')->nullable();
            $table->string('district')->nullable();
            $table->string('village')->nullable();
            $table->string('specefic_place')->nullable();

            $table->string('coordinates')->nullable();
            $table->string('Province_addr')->nullable();

            $table->foreignId('resource_id')->nullable()->constrained('resources')->nullOnDelete();
            $table->text('note')->nullable();

            $table->timestamps();

            // ✅ UNIQUE SIGNATURE
            $table->unique([
                'name',
                'father_name',
                'job',
                'jorm_type',
                'province',
                'zone',
                'district',
                'village',
            ], 'criminal_capture_places_unique_signature');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('criminal_capture_places');
    }
};
