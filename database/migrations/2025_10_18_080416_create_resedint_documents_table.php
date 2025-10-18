<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('resedint_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('resedint_id')
                ->constrained('resedints')
                ->cascadeOnDelete(); // DB will delete the rows when a Resedint is deleted

            $table->string('file_name');
            $table->string('file_path');   // path relative to the disk (e.g. "resedints/5/xyz.pdf")
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('size_bytes')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resedint_documents');
    }
};
