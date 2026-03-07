<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('non_responsable_people', function (Blueprint $table) {
            $table->id();
        $table->foreignId('information_id')
        ->nullable()
        ->constrained('information')   // because your table name is singular
        ->nullOnDelete();              // if Information deleted, set NULL (or use cascadeOnDelete if you prefer)
            $table->string('nick_name')->nullable();
            $table->string('name');
            $table->string('father_name')->nullable();
            $table->string('picture' , 500)->nullable();

            // main address (identity)
            $table->string('main_province')->nullable();
            $table->string('main_district')->nullable();
            $table->string('main_village')->nullable();

            // current address
            $table->string('now_province')->nullable();
            $table->string('now_zone')->nullable();
            $table->string('now_district')->nullable();
            $table->string('now_village')->nullable();
            $table->string('now_specific_place')->nullable();
            $table->string('now_street_number')->nullable();
            $table->string('now_home_number')->nullable();

            $table->string('friends_quantity')->nullable();
            $table->string('supplier')->nullable();
            $table->string('phone_number')->nullable();
            $table->text('history_jorm')->nullable();

            $table->string('activity_place')->nullable();
            $table->string('old_job')->nullable();
            $table->string('now_job')->nullable();
            $table->string('gun_type')->nullable();
            $table->string('tanzim')->nullable();
            $table->string('takhalof_type')->nullable();

            $table->text('note')->nullable();
            $table->string('Province_addr')->nullable();
            $table->string('coordinates')->nullable();

            $table->foreignId('resource_id')
                ->nullable()
                ->constrained('resources')
                ->cascadeOnDelete();

            $table->timestamps();

            /**
             * ✅ UNIQUE (composite identity)
             */
            $table->unique(
                ['name', 'father_name', 'main_province', 'main_district', 'main_village'],
                'non_responsable_people_identity_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('non_responsable_people');
    }
};
