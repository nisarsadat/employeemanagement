<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Create the table if it doesn't exist yet
        if (!Schema::hasTable('information_documents')) {
            Schema::create('information_documents', function (Blueprint $table) {
                $table->id();

                // FK to 'information' table (singular)
                $table->foreignId('information_id')
                      ->constrained(table: 'information')
                      ->cascadeOnDelete();

                // --- adjust/keep what you need below ---
                $table->string('file_name')->nullable();
                $table->string('file_path');           // storage path
                $table->string('mime_type')->nullable();
                $table->unsignedBigInteger('size_bytes')->nullable();
                // --------------------------------------

                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('information_documents');
    }
};
