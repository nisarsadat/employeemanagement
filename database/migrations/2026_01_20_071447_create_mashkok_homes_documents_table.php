<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mashkok_homes_documents', function (Blueprint $table) {
            $table->id();

            $table->foreignId('mashkok_home_id')
                ->constrained('mashkok_homes')
                ->cascadeOnDelete();

            // disk-relative path (uploads/...)
            $table->string('file_path' , 2000);
            $table->string('original_name' , 2000)->nullable();
            $table->string('mime_type' , 100)->nullable();
            $table->unsignedBigInteger('size')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mashkok_homes_documents');
    }
};
