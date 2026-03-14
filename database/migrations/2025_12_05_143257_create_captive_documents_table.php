<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('captive_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('captive_id')
                ->constrained('captives')
                ->onDelete('cascade'); // delete docs when captive is deleted
            $table->string('file_name' , 2000);
            $table->string('file_path' , 2000); // relative path in storage/app/public/...
            $table->string('mime_type' , 100)->nullable();
            $table->unsignedBigInteger('size_bytes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('captive_documents');
    }
};
