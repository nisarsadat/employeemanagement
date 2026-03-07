<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('mashkok_homes', function (Blueprint $table) {
            $table->id();
        $table->foreignId('information_id')
        ->nullable()
        ->constrained('information')   // because your table name is singular
        ->nullOnDelete();              // if Information deleted, set NULL (or use cascadeOnDelete if you prefer)
            $table->string('picture' , 500)->nullable();
            $table->string('province')->nullable();
            $table->string('zone')->nullable();
            $table->string('district')->nullable();
            $table->string('village')->nullable();
            $table->string('specific_place')->nullable();
            $table->string('street_number')->nullable();
            $table->string('home_number')->nullable();
            $table->string('coordinates')->nullable();

            $table->string('home_type')->nullable();
            $table->string('mashkok_type')->nullable();
            $table->string('shak_symbols')->nullable();

            $table->text('note')->nullable();
            $table->longText('tafseel')->nullable();
            $table->string('Province_addr')->nullable();

            $table->foreignId('resource_id')
                ->nullable()
                ->constrained('resources')
                ->cascadeOnDelete();

            $table->timestamps();

            /**
             * ✅ UNIQUE (composite) exactly as you requested:
             * province + zone + district + village + specific_place
             */
            $table->unique(
                ['province', 'zone', 'district', 'village', 'specific_place'],
                'mashkok_homes_location_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mashkok_homes');
    }
};
