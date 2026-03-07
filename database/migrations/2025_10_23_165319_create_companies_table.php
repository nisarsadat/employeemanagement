<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id();

            // Top block
            $table->string('com_name')->nullable();
            $table->string('com_type')->nullable();
            $table->string('picture' , 500)->nullable();
            $table->string('product_type')->nullable();
            $table->string('com_level')->nullable();
            $table->string('main_owner')->nullable();

            // Responsible person
            $table->string('responsible_name')->nullable();
            $table->string('responsible_f_name')->nullable();
            $table->string('responsible_position')->nullable();
            $table->string('responsible_phone')->nullable();

            // Company location
            $table->string('com_province')->nullable();
            $table->string('com_zone')->nullable();
            $table->string('com_district')->nullable();
            $table->string('com_village')->nullable();
            $table->string('com_specific_place')->nullable();
            $table->string('com_coordinates')->nullable();

            // Staff counts
            $table->integer('male_employee_quantity_inner')->nullable();
            $table->integer('female_employee_quantity_inner')->nullable();
            $table->integer('male_employee_quantity_out')->nullable();
            $table->integer('female_employee_quantity_out')->nullable();
            $table->integer('guard_quantity')->nullable();
            $table->string('Province_addr')->nullable();

            // Link to resources
            $table->foreignId('resource_id')->nullable()->constrained('resources')->nullOnDelete();
            $table->unique('resource_id');

            // Notes
            $table->text('note')->nullable();

            $table->timestamps();

            // ✅ UNIQUE SIGNATURE (composite unique) based on your selected fields
            $table->unique([
                'com_name',
                'com_type',
                'product_type',
                'com_province',
                'com_zone',
                'com_district',
                'com_village',
            ], 'companies_unique_signature');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
