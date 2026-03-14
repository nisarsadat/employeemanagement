<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
        {public function up(): void
        {
            Schema::create('saeen_fasad_documents', function (Blueprint $table) {
                $table->id();

                $table->foreignId('saeen_fasad_id')
                    ->constrained('saeen_fasads')
                    ->cascadeOnDelete(); // ✅ delete docs rows when SaeenFasad deleted

                $table->string('file_path' , 2000);
                $table->string('original_name' , 2000)->nullable();
                $table->string('mime_type' , 100)->nullable();
                $table->unsignedBigInteger('size')->nullable();

                $table->timestamps();
            });
        }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('saeen_fasad_documents');
    }
};
