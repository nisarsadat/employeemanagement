<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('captive_delivery_documents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('captive_delivery_id');
            $table->string('file_name' , 2000);  // original name
            $table->string('file_path', 2000);  // storage path
            $table->string('mime_type' , 100)->nullable();
            $table->unsignedBigInteger('size_bytes')->nullable();
            $table->timestamps();

            $table->foreign('captive_delivery_id')
                  ->references('id')->on('captive_deliveries')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('captive_delivery_documents');
    }
};
