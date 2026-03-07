<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('sarafyanos', function (Blueprint $table) {
            $table->id();

            // required
            $table->string('name');

            // from your picture (kept spellings)
            $table->string('picture' , 500)->nullable();

            $table->string('sarafi_name')->nullable();
            $table->string('sarafi_type')->nullable();
            $table->string('father_name')->nullable();
            $table->string('position')->nullable();
            $table->string('phone_number')->nullable();

            $table->string('province')->nullable();
            $table->string('zone')->nullable();
            $table->string('district')->nullable();
            $table->string('village')->nullable();
            $table->string('specific_place')->nullable();
            $table->string('market_name')->nullable();
            $table->string('shop_number')->nullable();
            $table->string('cordinate')->nullable();

            $table->string('provinces_hawala')->nullable();
            $table->string('countries_hawala')->nullable();
            $table->string('employee_quantity')->nullable();
            $table->string('Province_addr')->nullable();

            // relation to resources (code/phone derived from here)
            $table->foreignId('resource_id')
                ->nullable()
                ->constrained('resources')
                ->cascadeOnDelete();
            $table->unique('resource_id');

            $table->text('notes')->nullable();

            // ✅ composite unique constraint (as you requested)
            $table->unique([
                'name',
                'sarafi_name',
                'sarafi_type',
                'father_name',
                'zone',
                'district',
                'village',
            ], 'sarafyanos_identity_unique');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sarafyanos');
    }
};
