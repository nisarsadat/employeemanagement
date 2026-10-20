<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('meetings', function (Blueprint $table) {
            $table->foreignId('account_id')
                ->nullable()
                ->constrained('accounts')
                ->nullOnDelete();

            $table->string('message_to_finance')->nullable();
            $table->string('money_amount_words')->nullable();
            $table->string('finance_status')->default('pending');
            $table->timestamp('finance_paid_at')->nullable();
        });

        Schema::table('finances', function (Blueprint $table) {
            $table->foreignId('meeting_id')
                ->nullable()
                ->after('information_id')
                ->constrained('meetings')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('finances', function (Blueprint $table) {
            $table->dropConstrainedForeignId('meeting_id');
        });

        Schema::table('meetings', function (Blueprint $table) {
            $table->dropConstrainedForeignId('account_id');
            $table->dropColumn([
                'message_to_finance',
                'money_amount_words',
                'finance_status',
                'finance_paid_at',
            ]);
        });
    }
};
