<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('super_markets', function (Blueprint $table) {
            $table->id();

            $table->string('supermarket_name')->nullable();
            $table->string('market_activity_type')->nullable();
            $table->integer('daily_clientele')->nullable();
            $table->integer('shops_quantity')->nullable();

            $table->string('responsible_name')->nullable();
            $table->string('responsible_f_name')->nullable();
            $table->string('responsible_phone')->nullable();
            $table->string('responsible_job')->nullable();

            $table->string('province')->nullable();
            $table->string('zone')->nullable();
            $table->string('district')->nullable();
            $table->string('village')->nullable();
            $table->string('picture')->nullable();
            $table->string('specefic_place')->nullable();
            $table->string('coordinates')->nullable();

            $table->integer('employee_quantity')->nullable();
            $table->integer('guard_quantity')->nullable();
            $table->string('Province_addr')->nullable();

            $table->foreignId('resource_id')->nullable()->constrained('resources')->nullOnDelete();
            $table->unique('resource_id');
            $table->text('note')->nullable();

            $table->timestamps();

            // ✅ UNIQUE SIGNATURE (composite unique)
            $table->unique([
                'supermarket_name',
                'market_activity_type',
                'daily_clientele',
                'shops_quantity',
                'province',
                'zone',
                'district',
                'village',
            ], 'super_markets_unique_signature');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('super_markets');
    }
};
