<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('banks', function (Blueprint $table) {
            $table->id();

            $table->string('bank_name');
            $table->string('bank_type')->nullable();
            $table->string('bank_owner')->nullable();
            $table->string('picture')->nullable();

            $table->string('responsible_name')->nullable();
            $table->string('responsible_f_name')->nullable();
            $table->string('responsible_position')->nullable();
            $table->string('responsible_phone')->nullable();

            $table->string('province')->nullable();
            $table->string('zone')->nullable();
            $table->string('district')->nullable();
            $table->string('village')->nullable();
            $table->string('specific_place')->nullable();
            $table->string('coordinates')->nullable();
            $table->string('which_offices_sallary')->nullable();

            $table->unsignedInteger('guard_quantity')->nullable();

            $table->text('note')->nullable();
            $table->string('Province_addr')->nullable();

            $table->foreignId('resource_id')
                ->nullable()
                ->constrained('resources')
                ->cascadeOnDelete();
            $table->unique('resource_id');

            $table->timestamps();

            /**
             * ✅ COMPOSITE UNIQUE (as requested)
             */
            $table->unique(
                [
                    'bank_name',
                    'bank_type',
                    'bank_owner',
                    'responsible_name',
                    'responsible_f_name',
                    'responsible_position',
                    'responsible_phone',
                    'province',
                    'zone',
                    'district',
                    'village',
                ],
                'banks_identity_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('banks');
    }
};
