<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('income_oils', function (Blueprint $table) {
            $table->id();
            $table->foreignId('oil_account_id')->constrained('oil_accounts')->cascadeOnUpdate()->restrictOnDelete();
            $table->decimal('oil_quantity', 14, 3);      // how much came in
            $table->string('source')->nullable();        // where it comes from
            $table->text('note')->nullable();
            $table->date('date')->index();
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('income_oils');
    }
};
