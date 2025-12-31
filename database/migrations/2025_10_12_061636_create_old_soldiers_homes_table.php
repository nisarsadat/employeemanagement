<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('old_soldiers_homes', function (Blueprint $table) {
            $table->id();

            // location (UNIQUE identity)
            $table->string('province')->nullable();
            $table->string('zone')->nullable();
            $table->string('district')->nullable();
            $table->string('village')->nullable();
            $table->string('specific_place')->nullable();
            $table->string('street_number')->nullable();
            $table->string('home_number')->nullable();
            $table->string('coordinates')->nullable();

            // owner info
            $table->string('name');
            $table->string('father_name')->nullable();
            $table->string('main_province')->nullable();
            $table->string('old_job')->nullable();
            $table->string('now_activity')->nullable();

            $table->text('note')->nullable();
            $table->string('Province_addr')->nullable();

            // relation
            $table->foreignId('resource_id')
                ->nullable()
                ->constrained('resources')
                ->cascadeOnDelete();

            $table->timestamps();

            /**
             * ✅ UNIQUE (composite)
             * prevents duplicate homes in same exact location
             */
            $table->unique(
                [
                    'province',
                    'zone',
                    'district',
                    'village',
                    'specific_place',
                    'street_number',
                    'home_number',
                ],
                'old_soldiers_homes_location_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('old_soldiers_homes');
    }
};
