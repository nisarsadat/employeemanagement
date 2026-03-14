<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('information_documents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('information_id');
            $table->string('file_name' , 2000);
            $table->string('file_path' , 2000);
            $table->string('mime_type' , 100)->nullable();
            $table->unsignedBigInteger('size_bytes')->nullable();
            $table->timestamps();

            $table->foreign('information_id')->references('id')->on('information')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('information_documents');
    }
};
