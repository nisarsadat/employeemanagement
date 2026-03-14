<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('lost_mujremeen_documents', function (Blueprint $table) {
            $table->id();

            $table->foreignId('lost_mujremeen_id')
                ->constrained('lost_mujremeens')
                ->cascadeOnDelete();

            $table->string('file_path' , 2000);       // "storage/uploads/..."
            $table->string('original_name' , 2000);   // original filename
            $table->string('mime_type' , 100)->nullable();
            $table->unsignedBigInteger('size')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lost_mujremeen_documents');
    }
};
