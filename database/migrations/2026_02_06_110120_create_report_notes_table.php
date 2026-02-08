<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('report_notes', function (Blueprint $table) {
            $table->id();
            $table->text('report_note');
            $table->enum('type', ['daily', 'weekly', 'monthly', 'quarter', 'yearly'])->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_notes');
    }
};
