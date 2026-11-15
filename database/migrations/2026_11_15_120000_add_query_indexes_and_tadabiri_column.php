<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('information') && ! Schema::hasColumn('information', 'office_employee_tadabiri_id')) {
            Schema::table('information', function (Blueprint $table) {
                $table->unsignedBigInteger('office_employee_tadabiri_id')->nullable()->after('office_employee_info_id');
                $table->index('office_employee_tadabiri_id', 'information_tadabiri_employee_idx');
            });
        }

        $this->addDateKey('information', 'information_saved_date', 'information_saved_on');
        $this->addDateKey('events', 'event_date', 'event_on');
        $this->addDateKey('resources', 'resource_saved_date', 'resource_saved_on');

        $this->indexIfMissing('information', 'information_category', 'information_category_idx');
        $this->indexIfMissing('information', 'office_employee_info_id', 'information_employee_info_idx');
        $this->indexIfMissing('information', 'information_saved_on', 'information_saved_on_idx');
        $this->indexIfMissing('events', 'event_type', 'events_event_type_idx');
        $this->indexIfMissing('events', 'event_on', 'events_event_on_idx');
        $this->indexIfMissing('resources', 'activity', 'resources_activity_idx');
        $this->indexIfMissing('resources', 'hawza', 'resources_hawza_idx');
        $this->indexIfMissing('resources', 'resource_saved_on', 'resources_saved_on_idx');
        $this->indexIfMissing('khayrat_reports', 'saved_date', 'khayrat_saved_date_idx');
        $this->indexIfMissing('khayrat_reports', 'zone', 'khayrat_zone_idx');
        $this->indexIfMissing('khayrat_reports', 'hawza', 'khayrat_hawza_idx');
        $this->indexIfMissing('office_employees', 'job_type', 'office_employees_job_type_idx');
        $this->indexIfMissing('expense_oils', 'date', 'expense_oils_date_idx');

        if (Schema::hasTable('information') && ! $this->indexExists('information', 'information_match_idx')) {
            DB::statement('ALTER TABLE information ADD INDEX information_match_idx (name(40), father_name(40), main_province(40), main_districts(40), main_village(40))');
        }

        if (Schema::hasTable('office_employees') && Schema::hasColumn('office_employees', 'activity')) {
            $type = DB::table('information_schema.COLUMNS')
                ->where('TABLE_SCHEMA', DB::getDatabaseName())
                ->where('TABLE_NAME', 'office_employees')
                ->where('COLUMN_NAME', 'activity')
                ->value('DATA_TYPE');

            if ($type === 'text') {
                DB::statement("ALTER TABLE office_employees MODIFY activity ENUM('فعال', 'غیر فعال') NULL");
            }
        }
    }

    public function down(): void
    {
        $this->dropIndex('information', 'information_tadabiri_employee_idx');
        $this->dropIndex('information', 'information_category_idx');
        $this->dropIndex('information', 'information_employee_info_idx');
        $this->dropIndex('information', 'information_saved_on_idx');
        $this->dropIndex('information', 'information_match_idx');
        $this->dropIndex('events', 'events_event_type_idx');
        $this->dropIndex('events', 'events_event_on_idx');
        $this->dropIndex('resources', 'resources_activity_idx');
        $this->dropIndex('resources', 'resources_hawza_idx');
        $this->dropIndex('resources', 'resources_saved_on_idx');
        $this->dropIndex('khayrat_reports', 'khayrat_saved_date_idx');
        $this->dropIndex('khayrat_reports', 'khayrat_zone_idx');
        $this->dropIndex('khayrat_reports', 'khayrat_hawza_idx');
        $this->dropIndex('office_employees', 'office_employees_job_type_idx');
        $this->dropIndex('expense_oils', 'expense_oils_date_idx');

        foreach ([
            ['information', 'office_employee_tadabiri_id'],
            ['information', 'information_saved_on'],
            ['events', 'event_on'],
            ['resources', 'resource_saved_on'],
        ] as [$table, $column]) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, $column)) {
                Schema::table($table, function (Blueprint $blueprint) use ($column) {
                    $blueprint->dropColumn($column);
                });
            }
        }
    }

    private function addDateKey(string $table, string $source, string $target): void
    {
        if (! Schema::hasTable($table) || ! Schema::hasColumn($table, $source) || Schema::hasColumn($table, $target)) {
            return;
        }

        Schema::table($table, function (Blueprint $blueprint) use ($target) {
            $blueprint->date($target)->nullable();
        });

        DB::statement("UPDATE `{$table}` SET `{$target}` = DATE(`{$source}`) WHERE `{$source}` IS NOT NULL AND `{$source}` <> '' AND `{$source}` REGEXP '^[0-9]{4}-[0-9]{2}-[0-9]{2}'");
    }

    private function indexIfMissing(string $table, string $column, string $index): void
    {
        if (! Schema::hasTable($table) || ! Schema::hasColumn($table, $column) || $this->indexExists($table, $index)) {
            return;
        }

        Schema::table($table, function (Blueprint $blueprint) use ($column, $index) {
            $blueprint->index($column, $index);
        });
    }

    private function indexExists(string $table, string $index): bool
    {
        $row = DB::selectOne(
            'SELECT 1 AS found FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND INDEX_NAME = ? LIMIT 1',
            [DB::getDatabaseName(), $table, $index]
        );

        return $row !== null;
    }

    private function dropIndex(string $table, string $index): void
    {
        if (Schema::hasTable($table) && $this->indexExists($table, $index)) {
            Schema::table($table, function (Blueprint $blueprint) use ($index) {
                $blueprint->dropIndex($index);
            });
        }
    }
};
