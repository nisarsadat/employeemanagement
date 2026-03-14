<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('event_documents')) {
            Schema::create('event_documents', function (Blueprint $table) {
                $table->id();
                $table->foreignId('event_id')->constrained('events')->cascadeOnDelete();
                $table->string('file_name' , 1200)->nullable();
                $table->string('file_path' , 1200);
                $table->string('mime_type' , 1200)->nullable();
                $table->unsignedBigInteger('size_bytes')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('event_documents');
    }
};
