<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('car_sales_companies', function (Blueprint $table) {
            $table->id();

            $table->string('car_sales_company_name');
            $table->string('picture')->nullable();

            $table->string('responsible_name')->nullable();
            $table->string('responsible_f_name')->nullable();
            $table->string('responsible_position')->nullable();
            $table->string('responsible_phone')->nullable();

            $table->string('province')->nullable();
            $table->string('zone')->nullable();
            $table->string('district')->nullable();
            $table->string('village')->nullable();
            $table->string('specific_place')->nullable();
            $table->string('coordinates')->nullable();

            $table->text('note')->nullable();
            $table->string('Province_addr')->nullable();

            $table->foreignId('resource_id')
                ->nullable()
                ->constrained('resources')
                ->cascadeOnDelete();

            $table->timestamps();

            /**
             * ✅ UNIQUE (composite) exactly as you requested
             */
            $table->unique(
                [
                    'car_sales_company_name',
                    'responsible_name',
                    'responsible_f_name',
                    'responsible_position',
                    'responsible_phone',
                    'province',
                    'zone',
                    'district',
                    'village',
                ],
                'car_sales_companies_identity_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('car_sales_companies');
    }
};
