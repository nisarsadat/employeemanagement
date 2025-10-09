<?php
// database/migrations/2025_10_02_120000_add_unique_code_number_to_all_tables.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('office_employees')) {
            Schema::table('office_employees', function (Blueprint $table) {
                if (!Schema::hasColumn('office_employees', 'code_number')) {
                    $table->string('code_number')->unique()->after('nickname');
                } else {
                    // ensure unique index exists
                    $indexes = $this->listIndexes('office_employees');
                    if (!in_array('office_employees_code_number_unique', $indexes, true)) {
                        $table->unique('code_number');
                    }
                }
            });
        }

        if (Schema::hasTable('zone_employees')) {
            Schema::table('zone_employees', function (Blueprint $table) {
                if (!Schema::hasColumn('zone_employees', 'code_number')) {
                    $table->string('code_number')->unique()->after('nickname');
                } else {
                    $indexes = $this->listIndexes('zone_employees');
                    if (!in_array('zone_employees_code_number_unique', $indexes, true)) {
                        $table->unique('code_number');
                    }
                }
            });
        }

        if (Schema::hasTable('resedints')) {
            Schema::table('resedints', function (Blueprint $table) {
                if (!Schema::hasColumn('resedints', 'code_number')) {
                    $table->string('code_number')->unique()->after('nickname');
                } else {
                    $indexes = $this->listIndexes('resedints');
                    if (!in_array('resedints_code_number_unique', $indexes, true)) {
                        $table->unique('code_number');
                    }
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('office_employees')) {
            Schema::table('office_employees', fn (Blueprint $t) => $t->dropUnique(['code_number']));
        }
        if (Schema::hasTable('zone_employees')) {
            Schema::table('zone_employees', fn (Blueprint $t) => $t->dropUnique(['code_number']));
        }
        if (Schema::hasTable('resedints')) {
            Schema::table('resedints', fn (Blueprint $t) => $t->dropUnique(['code_number']));
        }
    }

    // helper (works on MySQL; otherwise you can skip the index check)
    private function listIndexes(string $table): array
    {
        try {
            return collect(\DB::select("SHOW INDEXES FROM {$table}"))
                ->pluck('Key_name')->unique()->all();
        } catch (\Throwable $e) {
            return [];
        }
    }
};
