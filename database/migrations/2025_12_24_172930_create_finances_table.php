<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('finances', function (Blueprint $table) {
            $table->id();

            // 🔗 Relations
            $table->foreignId('office_employee_id')
                ->nullable()
                ->constrained('office_employees')
                ->nullOnDelete();

            $table->foreignId('resource_id')
                ->nullable()
                ->constrained('resources')
                ->nullOnDelete();

            $table->foreignId('account_id')
                ->constrained('accounts')
                ->cascadeOnDelete();
            $table->foreignId('event_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('information_id')->nullable()->constrained()->nullOnDelete();
            // 💰 Money
            $table->decimal('money_amount_number', 14, 2)->default(0);
            $table->string('money_amount_words', 500)->nullable();

            // 📅 Date
            $table->date('date')->nullable();

            // ☎ Snapshot
            $table->string('employee_phone', 50)->nullable();
            $table->string('expense_result', 255)->nullable();

            // 👥 People
            $table->string('money_distributer', 255)->nullable();
            $table->string('payer', 255)->nullable();
            $table->string('receiver', 255)->nullable();

            // 🏦 Type
            $table->string('account_type', 120)->nullable();

            // 📝 Notes
            $table->text('notes')->nullable();

            $table->timestamps();

            // ⚡ Indexes
            $table->index('office_employee_id');
            $table->index('resource_id');
            $table->index('account_id');
            $table->index('date');
            $table->index('account_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('finances');
    }
};
