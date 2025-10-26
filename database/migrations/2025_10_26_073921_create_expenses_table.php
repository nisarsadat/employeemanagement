<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void {
    Schema::create('expenses', function (Blueprint $table) {
      $table->id();
      $table->foreignId('account_id')->constrained('accounts')->cascadeOnUpdate()->restrictOnDelete();

      // pay to (OfficeEmployee or Resource) - polymorphic
      $table->nullableMorphs('payee'); // payee_type, payee_id

      $table->decimal('amount_price', 18, 2); // amount_price (پرداخت پول)
      $table->date('paid_at');                // date
      $table->string('phone_number', 30)->nullable();
      $table->string('reason_expense', 255)->nullable(); // reason expense
      $table->text('note')->nullable();       // note
      $table->timestamps();

      $table->index(['account_id','paid_at']);
    });
  }
  public function down(): void { Schema::dropIfExists('expenses'); }
};

