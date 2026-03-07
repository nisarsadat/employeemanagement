<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('heist_documents', function (Blueprint $table) {
            $table->id();

            $table->foreignId('heist_id')
                ->constrained('heists')
                ->cascadeOnDelete();

            $table->string('file_path' , 500);          // storage path
            $table->string('original_name' , 500)->nullable();
            $table->string('mime_type' , 500)->nullable();
            $table->unsignedBigInteger('size')->nullable(); // bytes

            $table->timestamps();

            $table->index('heist_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('heist_documents');
    }
};
