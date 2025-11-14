<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('emarat_offices', function (Blueprint $table) {
            $table->id();

            $table->string('province')->nullable();
            $table->string('zone')->nullable();
            $table->string('district')->nullable();
            $table->string('village')->nullable();
            $table->string('specific_place')->nullable();
            $table->string('street_number')->nullable();
            $table->string('home_number')->nullable();
            $table->string('coordenates')->nullable();
            $table->string('home_type')->nullable();
            $table->string('responsible_office')->nullable();
            $table->string('tahdid_mutawajeh')->nullable();
            $table->string('guard_quantity')->nullable();
            $table->text('note')->nullable();
            $table->string('Province_addr')->nullable();


            $table->foreignId('resource_id')
                ->nullable()
                ->constrained('resources')
                ->cascadeOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('emarat_offices');
    }
};
