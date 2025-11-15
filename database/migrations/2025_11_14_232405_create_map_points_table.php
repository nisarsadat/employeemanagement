<?php

// database/migrations/2025_11_14_000000_create_map_points_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('map_points', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('father_name')->nullable();
            $table->string('code_number')->nullable();
            $table->string('coordinates'); // "lng,lat"
            $table->string('phone')->nullable();
            $table->text('note')->nullable();
            $table->string('picture_path')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('map_points');
    }
};

