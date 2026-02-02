<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('jawasis_documents', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('jawasis_id');
            $table->string('file_path');
            $table->string('original_name')->nullable();
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('size')->nullable();

            $table->timestamps();

            $table->foreign('jawasis_id')
                ->references('id')
                ->on('jawasis')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jawasis_documents');
    }
};
