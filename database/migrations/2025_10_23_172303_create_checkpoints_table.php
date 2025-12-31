<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('checkpoints', function (Blueprint $table) {
            $table->id();

            $table->string('checkpoint_name')->nullable();
            $table->integer('employee_quantity')->nullable();

            $table->string('organ')->nullable();
            $table->string('create_reason')->nullable();

            $table->string('officer_name')->nullable();
            $table->string('officer_f_name')->nullable();
            $table->string('officer_phone')->nullable();

            $table->string('gun_type')->nullable();

            $table->string('province')->nullable();
            $table->string('zone')->nullable();
            $table->string('district')->nullable();
            $table->string('village')->nullable();
            $table->string('specefic_place')->nullable();

            $table->string('coordinates')->nullable();
            $table->string('Province_addr')->nullable();

            $table->foreignId('resource_id')->nullable()->constrained('resources')->nullOnDelete();
            $table->text('note')->nullable();

            $table->timestamps();

            // ✅ UNIQUE SIGNATURE (composite unique)
            $table->unique([
                'checkpoint_name',
                'employee_quantity',
                'organ',
                'create_reason',
                'gun_type',
                'province',
                'zone',
                'district',
                'village',
            ], 'checkpoints_unique_signature');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('checkpoints');
    }
};
