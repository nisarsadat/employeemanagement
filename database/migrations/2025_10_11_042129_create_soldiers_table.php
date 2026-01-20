<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('soldiers', function (Blueprint $table) {
            $table->id();

            /* =====================
             | Identity
             |=====================*/
            $table->string('name');
            $table->string('father_name')->nullable();
            $table->string('phone_number')->nullable();
            $table->string('Province_addr')->nullable();

            /* =====================
             | ✅ Picture
             |=====================*/
            $table->string('picture')->nullable();

            /* =====================
             | Main address
             |=====================*/
            $table->string('main_province')->nullable();
            $table->string('main_district')->nullable();
            $table->string('main_village')->nullable();

            /* =====================
             | Current address
             |=====================*/
            $table->string('now_province')->nullable();
            $table->string('now_zone')->nullable();
            $table->string('now_district')->nullable();
            $table->string('now_village')->nullable();

            /* =====================
             | Location details
             |=====================*/
            $table->string('specific_place')->nullable();
            $table->string('street_number')->nullable();
            $table->string('home_number')->nullable();

            /* =====================
             | Job & guard info
             |=====================*/
            $table->string('old_job')->nullable();
            $table->string('now_job')->nullable();
            $table->unsignedInteger('guard_amount')->nullable();

            /* =====================
             | Coordinates
             |=====================*/
            $table->string('coordinates')->nullable();

            /* =====================
             | Resource relation
             |=====================*/
            $table->foreignId('resource_id')
                ->nullable()
                ->constrained('resources')
                ->nullOnDelete();

            /* =====================
             | Notes
             |=====================*/
            $table->text('notes')->nullable();

            $table->timestamps();

            /* =====================
             | ✅ UNIQUE identity
             |=====================
             | A soldier is unique by:
             | name + father_name + phone_number + Province_addr
             | + main_province + main_district + main_village
             */
            $table->unique(
                [
                    'name',
                    'father_name',
                    'phone_number',
                    'Province_addr',
                    'main_province',
                    'main_district',
                    'main_village',
                ],
                'soldiers_identity_unique'
            );

            /* =====================
             | Indexes
             |=====================*/
            $table->index('resource_id');
            $table->index(['name', 'father_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('soldiers');
    }
};
