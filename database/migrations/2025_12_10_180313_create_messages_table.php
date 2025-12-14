<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->id();

            $table->foreignId('conversation_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('sender_id')
                ->constrained('users')
                ->cascadeOnDelete();

            // ✅ reply feature
            $table->foreignId('reply_to_id')
                ->nullable()
                ->constrained('messages')
                ->nullOnDelete();

            $table->text('body')->nullable();

            // ✅ delivery + seen
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('seen_at')->nullable();

            // ✅ delete single message (soft delete)
            $table->softDeletes(); // deleted_at

            $table->timestamps();

            // ✅ indexes
            $table->index(['conversation_id', 'created_at']);
            $table->index(['conversation_id', 'seen_at']);
            $table->index(['conversation_id', 'deleted_at']);
            $table->index(['reply_to_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
