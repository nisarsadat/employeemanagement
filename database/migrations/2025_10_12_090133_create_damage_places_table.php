<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('damage_places', function (Blueprint $table) {
            $table->id();

            $table->string('province')->nullable();
            $table->string('zone')->nullable();
            $table->string('district')->nullable();
            $table->string('village')->nullable();
            $table->string('specific_place')->nullable();
            $table->string('specific_dot')->nullable();
            $table->string('coordinates')->nullable();

            $table->string('tahdid_type')->nullable();
            $table->string('percentage')->nullable();
            $table->string('tahdid_place')->nullable();
            $table->string('amnyat_khala')->nullable();
            $table->text('cameesa_tafseel')->nullable();
            $table->string('guard_quantity')->nullable();

            $table->string('masool_name')->nullable();
            $table->string('masool_f_name')->nullable();
            $table->string('masool_position')->nullable();
            $table->string('masool_phone')->nullable();

            $table->text('note')->nullable();
            $table->string('Province_addr')->nullable();

            $table->foreignId('resource_id')
                ->nullable()
                ->constrained('resources')
                ->cascadeOnDelete();

            $table->timestamps();

            /**
             * ✅ UNIQUE (composite) exactly as you requested:
             * location + tahdid_type + masool fields
             */
            $table->unique(
                [
                    'province',
                    'zone',
                    'district',
                    'village',
                    'specific_place',
                    'specific_dot',
                    'coordinates',
                    'tahdid_type',
                    'masool_name',
                    'masool_f_name',
                    'masool_position',
                    'masool_phone',
                ],
                'damage_places_identity_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('damage_places');
    }
};
