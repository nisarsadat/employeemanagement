<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('fasad_documents', function (Blueprint $table) {
            $table->id();

            $table->foreignId('fasad_id')
                ->constrained('fasads')
                ->cascadeOnDelete();

            $table->string('file_path');       // storage path
            $table->string('original_name');  // original filename
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('size')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fasad_documents');
    }
};
