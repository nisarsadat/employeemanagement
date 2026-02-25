<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('badmashanos', function (Blueprint $table) {
            $table->id();
        $table->foreignId('information_id')
        ->nullable()
        ->constrained('information')   // because your table name is singular
        ->nullOnDelete();              // if Information deleted, set NULL (or use cascadeOnDelete if you prefer)
            // Personal
            $table->string('nickname')->nullable();
            $table->string('name'); // required
            $table->string('father_name')->nullable();
            $table->string('picture')->nullable();

            // Main address
            $table->string('main_province')->nullable();
            $table->string('main_district')->nullable();
            $table->string('main_village')->nullable();

            // Current address
            $table->string('now_province')->nullable();
            $table->string('now_zone')->nullable();
            $table->string('now_district')->nullable();
            $table->string('now_village')->nullable();
            $table->string('now_specific_place')->nullable();
            $table->string('now_street_number')->nullable();
            $table->string('now_home_number')->nullable();

            // Job & relations
            $table->string('old_job')->nullable();
            $table->string('job')->nullable();
            $table->string('friends_quantity')->nullable();
            $table->string('supplier')->nullable();
            $table->string('phone_number')->nullable();

            // History / notes
            $table->string('history_jorm')->nullable();
            $table->string('jorm_type')->nullable();
            $table->text('note')->nullable();
            $table->string('Province_addr')->nullable();
            $table->string('coordinates')->nullable();

            // Link to resources
            $table->foreignId('resource_id')
                ->nullable()
                ->constrained('resources')
                ->cascadeOnDelete();

            // ✅ unique composite (exact fields you listed)
            $table->unique([
                'nickname',
                'name',
                'father_name',
                'main_province',
                'main_district',
                'main_village',
            ], 'badmashanos_unique_key');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('badmashanos');
    }
};
