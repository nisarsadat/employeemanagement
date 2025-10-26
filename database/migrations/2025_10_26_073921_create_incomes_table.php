<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void {
    Schema::create('incomes', function (Blueprint $table) {
      $table->id();
      $table->foreignId('account_id')->constrained('accounts')->cascadeOnUpdate()->restrictOnDelete();
      $table->decimal('amount', 18, 2);        // price amount (دریافت پول)
      $table->date('received_at');             // date
      $table->text('note')->nullable();        // note
      $table->timestamps();
      $table->index(['account_id','received_at']);
    });
  }
  public function down(): void { Schema::dropIfExists('incomes'); }
};

