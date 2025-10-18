<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('office_employee_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('office_employee_id')
                  ->constrained('office_employees')
                  ->onDelete('cascade'); // cascade row delete
            $table->string('file_name')->nullable();
            $table->string('file_path'); // stored on 'public' disk
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('size_bytes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('office_employee_documents');
    }
};
