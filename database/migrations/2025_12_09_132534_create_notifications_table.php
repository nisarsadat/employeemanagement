<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();

            // Who receives this notification (logged-in user)
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // What and how
            $table->string('module')->nullable();   // e.g. "resources", "events"
            $table->string('action')->nullable();   // "created", "updated", "deleted"
            $table->string('level')->default('info'); // info|success|warning|danger

            // Content
            $table->string('title');
            $table->text('message')->nullable();
            $table->json('changes')->nullable();    // 👈 only once, no ->after()

            // Where to go on click
            $table->string('route_name')->nullable();    // e.g. "resources.edit"
            $table->json('route_params')->nullable();    // e.g. {"id": 12}

            // Read status
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
