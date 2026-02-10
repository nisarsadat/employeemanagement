<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('entry_exits', function (Blueprint $table) {
            $table->id();


            $table->string('picture')->nullable();

            // from your picture (kept as-is)
            $table->string('entry_way');
            $table->string('way_type')->nullable();
            $table->string('province')->nullable();
            $table->string('zone')->nullable();
            $table->string('district')->nullable();
            $table->string('village')->nullable();
            $table->string('specific_place')->nullable();
            $table->string('cordinate')->nullable();
            $table->string('amnayaty_poste')->nullable();
            $table->string('amnayaty_ishala')->nullable();
            $table->string('where_went')->nullable();
            $table->string('where_come')->nullable();
            $table->string('Province_addr')->nullable();

            // relation to resources (resource_code/phone are derived from this)
            $table->foreignId('resource_id')
                ->nullable()
                ->constrained('resources')
                ->cascadeOnDelete();

            // extra
            $table->text('notes')->nullable();

            // ✅ UNIQUE: name + village + way_type
            $table->unique([ 'village', 'way_type'], 'entry_exits_name_village_type_unique');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('entry_exits');
    }
};
