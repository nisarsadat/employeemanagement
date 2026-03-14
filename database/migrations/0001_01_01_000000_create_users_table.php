<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->string('username')->unique();
            $table->string('password');

            // avatar
            $table->string('image', 500)->nullable();

            // online / activity
            $table->boolean('is_online')->default(false);
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamp('last_login_at')->nullable();
            $table->timestamp('last_logout_at')->nullable();
            $table->timestamp('current_session_started_at')->nullable();

            // durations
            $table->unsignedBigInteger('last_session_duration_seconds')->default(0);
            $table->unsignedBigInteger('total_login_duration_seconds')->default(0);
            $table->unsignedBigInteger('today_login_duration_seconds')->default(0);
            $table->date('today_login_duration_date')->nullable();

            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};