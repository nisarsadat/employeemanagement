<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('mawads', function (Blueprint $table) {
            if (!Schema::hasColumn('mawads', 'name')) {
                $table->string('name')->after('nickname'); // align with your order
            }
            if (!Schema::hasColumn('mawads', 'mawad_type')) $table->string('mawad_type')->nullable();
            if (!Schema::hasColumn('mawads', 'nickname')) $table->string('nickname')->nullable();
            if (!Schema::hasColumn('mawads', 'father_name')) $table->string('father_name')->nullable();

            if (!Schema::hasColumn('mawads', 'main_province')) $table->string('main_province')->nullable();
            if (!Schema::hasColumn('mawads', 'main_district')) $table->string('main_district')->nullable();
            if (!Schema::hasColumn('mawads', 'main_village')) $table->string('main_village')->nullable();

            if (!Schema::hasColumn('mawads', 'now_province')) $table->string('now_province')->nullable();
            if (!Schema::hasColumn('mawads', 'now_district')) $table->string('now_district')->nullable();
            if (!Schema::hasColumn('mawads', 'now_village')) $table->string('now_village')->nullable();

            if (!Schema::hasColumn('mawads', 'province')) $table->string('province')->nullable();
            if (!Schema::hasColumn('mawads', 'zone')) $table->string('zone')->nullable();
            if (!Schema::hasColumn('mawads', 'district')) $table->string('district')->nullable();
            if (!Schema::hasColumn('mawads', 'village')) $table->string('village')->nullable();

            if (!Schema::hasColumn('mawads', 'specific_place')) $table->string('specific_place')->nullable();
            if (!Schema::hasColumn('mawads', 'coordinates')) $table->string('coordinates')->nullable();
            if (!Schema::hasColumn('mawads', 'mawad_sales_phone')) $table->string('mawad_sales_phone')->nullable();

            if (!Schema::hasColumn('mawads', 'history_form')) $table->text('history_form')->nullable();
            if (!Schema::hasColumn('mawads', 'supplier')) $table->string('supplier')->nullable();
            if (!Schema::hasColumn('mawads', 'note')) $table->text('note')->nullable();

            if (!Schema::hasColumn('mawads', 'resource_id')) {
                $table->foreignId('resource_id')->nullable()->index();
                // add FK only if resources table/column exists and matches type
                if (Schema::hasTable('resources')) {
                    $table->foreign('resource_id')->references('id')->on('resources')->cascadeOnDelete();
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('mawads', function (Blueprint $table) {
            // Rollback (optional): drop only what you added
            if (Schema::hasColumn('mawads', 'name')) $table->dropColumn('name');
            // …drop others if needed
        });
    }
};
