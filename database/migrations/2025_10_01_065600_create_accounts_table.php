<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();

            $table->string('name');                      // account name
            $table->decimal('price', 18, 2)->default(0); // account price
            $table->text('details')->nullable();         // details

            $table->timestamps();

            // ✅ index only on existing column
            $table->index('name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accounts');
    }
};
