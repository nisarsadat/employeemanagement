<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('expense_oils', function (Blueprint $table) {
            $table->id();
            $table->foreignId('oil_account_id')->constrained('oil_accounts')->cascadeOnUpdate()->restrictOnDelete();
            $table->string('name');                      // short title/label
            $table->string('destination')->nullable();   // where it gone
            $table->decimal('oil_quantity', 14, 3);      // how much went out  <-- remove if you truly don’t want it
            $table->text('note')->nullable();
            $table->date('date')->index();
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('expense_oils');
    }
};
