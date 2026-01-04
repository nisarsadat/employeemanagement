<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mawads', function (Blueprint $table) {
            $table->id();

            // ---------- Identity ----------
            $table->string('mawad_type')->nullable();
            $table->string('nickname')->nullable();
            $table->string('name'); // required
            $table->string('father_name')->nullable();

            // ---------- Main address ----------
            $table->string('main_province')->nullable();
            $table->string('main_district')->nullable();
            $table->string('main_village')->nullable();

            // ---------- Current address ----------
            $table->string('now_province')->nullable();
            $table->string('now_district')->nullable();
            $table->string('now_village')->nullable();

            // ---------- Extra location ----------
            $table->string('province')->nullable();
            $table->string('zone')->nullable();
            $table->string('district')->nullable();
            $table->string('village')->nullable();

            $table->string('specific_place')->nullable();
            $table->string('coordinates')->nullable();

            // ---------- Contact & sales ----------
            $table->string('mawad_sales_phone')->nullable();
            $table->string('supplier')->nullable();
            $table->string('Province_addr')->nullable();

            // ---------- History & notes ----------
            $table->text('history_form')->nullable();
            $table->text('note')->nullable();

            // ---------- Relation ----------
            $table->foreignId('resource_id')
                ->nullable()
                ->constrained('resources')
                ->cascadeOnDelete();

            $table->timestamps();

            /**
             * ✅ UNIQUE (composite identity)
             * mawad_type + name + father_name + main_province + main_district + main_village
             */
            $table->unique(
                ['mawad_type', 'name', 'father_name', 'main_province', 'main_district', 'main_village'],
                'mawads_identity_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mawads');
    }
};
