<?php

// database/migrations/2026_02_16_000000_create_resource_formats_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('resource_formats', function (Blueprint $table) {
      $table->id();

      $table->foreignId('resource_id')
        ->constrained('resources')
        ->cascadeOnDelete();

      // polymorphic pointer to the format record
      $table->string('formatable_type');
      $table->unsignedBigInteger('formatable_id');

      $table->timestamps();

      // ✅ THE GLOBAL RULE:
      $table->unique('resource_id'); // one format per resource globally

      // optional but helpful
      $table->unique(['formatable_type', 'formatable_id']); // one lock per record
      $table->index(['formatable_type', 'formatable_id']);
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('resource_formats');
  }
};
