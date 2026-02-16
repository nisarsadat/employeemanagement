<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('econemy_projects', function (Blueprint $table) {
            $table->id();

            $table->string('coordinates')->nullable();

            $table->string('name')->nullable();
            $table->string('type')->nullable();

            $table->string('picture')->nullable();
            // Responsible person (mirror spellings)
            $table->string('responsiable_name')->nullable();
            $table->string('responsiable_f_name')->nullable();
            $table->string('responsible_possition')->nullable();
            $table->string('responsible_phone')->nullable();

            // Location
            $table->string('province')->nullable();
            $table->string('zone')->nullable();
            $table->string('district')->nullable();
            $table->string('village')->nullable();
            $table->string('specefic_place')->nullable();

            // Other
            $table->string('amnyatee_khala')->nullable();
            $table->integer('employee_quantity')->nullable();
            $table->integer('gaurd_quantity')->nullable();
            $table->string('Province_addr')->nullable();

            // Link to Resource + note
            $table->foreignId('resource_id')->nullable()->constrained('resources')->nullOnDelete();
            $table->text('note')->nullable();

            $table->timestamps();

            // ✅ UNIQUE SIGNATURE (composite unique) based on your selected fields
            $table->unique([
                'name',
                'type',
                'province',
                'zone',
                'district',
                'village',
            ], 'econemy_projects_unique_signature');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('econemy_projects');
    }
};
