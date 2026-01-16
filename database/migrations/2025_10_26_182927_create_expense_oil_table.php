<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('expense_oils', function (Blueprint $table) {
            $table->id();

            $table->foreignId('oil_account_id')->constrained('oil_accounts')->cascadeOnDelete();
            $table->foreignId('employee_id')->nullable()->constrained('office_employees')->nullOnDelete();

            $table->string('name')->nullable();
            $table->string('father_name')->nullable();
            $table->date('date')->nullable();

            $table->decimal('liter_in_number', 12, 2)->nullable();
            $table->string('liter_in_words')->nullable();

            $table->string('account_name')->nullable(); // if you store text snapshot
            $table->text('tafseel')->nullable();
            $table->string('payer')->nullable();
            $table->text('note')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expense_oils');
    }
};
