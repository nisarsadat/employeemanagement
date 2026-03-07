<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
            Schema::create('zone_employees', function (Blueprint $table) {
        $table->id();
        $table->string('picture', 500)->nullable();
        $table->string('name');
        $table->string('father_name')->nullable();
        $table->string('nickname')->nullable();
        $table->string('hawza')->nullable();
        $table->string('zone')->nullable();
        $table->string('degree')->nullable();
        $table->string('zone_entry_hour')->nullable();
        $table->text('notes')->nullable();
        $table->string('code_number')->nullable()->unique();
        $table->string('coordinates')->nullable()
            ->comment('latitude,longitude or address reference');
        $table->timestamps();
    });

    }

    public function down(): void
    {
        Schema::dropIfExists('zone_employees');
    }
};
