<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('sundry_documents', function (Blueprint $table) {
            $table->id();

            $table->foreignId('outgoing_incoming_id')
                ->constrained('outgoing_incomings')
                ->cascadeOnDelete(); // ✅ deletes DB rows automatically

            $table->string('file_name' , 2000);
            $table->string('file_path' , 2000); // storage path like outgoing_incomings/1/abc.pdf
            $table->unsignedBigInteger('file_size')->nullable();
            $table->string('mime_type' , 100)->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sundry_documents');
    }
};
