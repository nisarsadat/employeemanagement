<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // ✅ If table doesn't exist (rare), do nothing here
        if (!Schema::hasTable('mawads')) {
            return;
        }

        Schema::table('mawads', function (Blueprint $table) {

            // ---------- Identity ----------
            if (!Schema::hasColumn('mawads', 'mawad_type')) {
                $table->string('mawad_type')->nullable()->after('id');
            }
            if (!Schema::hasColumn('mawads', 'nickname')) {
                $table->string('nickname')->nullable()->after('mawad_type');
            }
            if (!Schema::hasColumn('mawads', 'name')) {
                $table->string('name')->after('nickname'); // required in your create migration
            }
            if (!Schema::hasColumn('mawads', 'father_name')) {
                $table->string('father_name')->nullable()->after('name');
            }

            // ---------- Main address ----------
            if (!Schema::hasColumn('mawads', 'main_province')) {
                $table->string('main_province')->nullable();
            }
            if (!Schema::hasColumn('mawads', 'main_district')) {
                $table->string('main_district')->nullable();
            }
            if (!Schema::hasColumn('mawads', 'main_village')) {
                $table->string('main_village')->nullable();
            }

            // ---------- Current address ----------
            if (!Schema::hasColumn('mawads', 'now_province')) {
                $table->string('now_province')->nullable();
            }
            if (!Schema::hasColumn('mawads', 'now_district')) {
                $table->string('now_district')->nullable();
            }
            if (!Schema::hasColumn('mawads', 'now_village')) {
                $table->string('now_village')->nullable();
            }

            // ---------- Extra location block ----------
            if (!Schema::hasColumn('mawads', 'province')) {
                $table->string('province')->nullable();
            }
            if (!Schema::hasColumn('mawads', 'zone')) {
                $table->string('zone')->nullable();
            }
            if (!Schema::hasColumn('mawads', 'district')) {
                $table->string('district')->nullable();
            }
            if (!Schema::hasColumn('mawads', 'village')) {
                $table->string('village')->nullable();
            }

            if (!Schema::hasColumn('mawads', 'specific_place')) {
                $table->string('specific_place')->nullable();
            }
            if (!Schema::hasColumn('mawads', 'coordinates')) {
                $table->string('coordinates')->nullable();
            }
            if (!Schema::hasColumn('mawads', 'mawad_sales_phone')) {
                $table->string('mawad_sales_phone')->nullable();
            }

            // ---------- Other fields ----------
            if (!Schema::hasColumn('mawads', 'history_form')) {
                $table->text('history_form')->nullable();
            }
            if (!Schema::hasColumn('mawads', 'supplier')) {
                $table->string('supplier')->nullable();
            }
            if (!Schema::hasColumn('mawads', 'note')) {
                $table->text('note')->nullable();
            }
            if (!Schema::hasColumn('mawads', 'Province_addr')) {
                $table->string('Province_addr')->nullable();
            }

            // ---------- FK ----------
            if (!Schema::hasColumn('mawads', 'resource_id')) {
                $table->foreignId('resource_id')->nullable()
                    ->constrained('resources')
                    ->cascadeOnDelete();
            }
        });

        // ✅ Create UNIQUE index if missing
        // (MySQL safe check)
        try {
            $indexes = DB::select("SHOW INDEX FROM mawads WHERE Key_name = 'mawads_identity_unique'");
            if (empty($indexes)) {
                Schema::table('mawads', function (Blueprint $table) {
                    $table->unique(
                        ['mawad_type', 'name', 'father_name', 'main_province', 'main_district', 'main_village'],
                        'mawads_identity_unique'
                    );
                });
            }
        } catch (\Throwable $e) {
            // ignore (some DB drivers may behave differently)
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('mawads')) {
            return;
        }

        // ✅ Drop unique index safely
        try {
            $indexes = DB::select("SHOW INDEX FROM mawads WHERE Key_name = 'mawads_identity_unique'");
            if (!empty($indexes)) {
                Schema::table('mawads', function (Blueprint $table) {
                    $table->dropUnique('mawads_identity_unique');
                });
            }
        } catch (\Throwable $e) {
            // ignore
        }

        Schema::table('mawads', function (Blueprint $table) {

            // ✅ drop FK safely
            // dropConstrainedForeignId assumes constraint name: mawads_resource_id_foreign
            if (Schema::hasColumn('mawads', 'resource_id')) {
                try {
                    $table->dropConstrainedForeignId('resource_id');
                } catch (\Throwable $e) {
                    // if constraint name doesn't match, still remove the column
                    try {
                        $table->dropColumn('resource_id');
                    } catch (\Throwable $e2) {}
                }
            }

            // ✅ Safe drop columns (only if exist)
            $cols = [
                'mawad_type',
                'nickname',
                'name',
                'father_name',

                'main_province',
                'main_district',
                'main_village',

                'now_province',
                'now_district',
                'now_village',

                'province',
                'zone',
                'district',
                'village',

                'specific_place',
                'coordinates',
                'mawad_sales_phone',

                'history_form',
                'supplier',
                'note',
                'Province_addr',
            ];

            foreach ($cols as $c) {
                if (Schema::hasColumn('mawads', $c)) {
                    try {
                        $table->dropColumn($c);
                    } catch (\Throwable $e) {
                        // ignore
                    }
                }
            }
        });
    }
};
