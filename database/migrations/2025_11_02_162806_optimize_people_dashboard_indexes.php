<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // ---- office_employees
        Schema::table('office_employees', function (Blueprint $table) {
            if ($this->tableHasColumn('office_employees', 'zone')
                && ! $this->indexExists('office_employees', 'office_employees_zone_index')) {
                $table->index('zone', 'office_employees_zone_index');
            }

            if ($this->tableHasColumn('office_employees', 'activity')
                && ! $this->indexExists('office_employees', 'office_employees_activity_index')) {
                $table->index('activity', 'office_employees_activity_index');
            }
        });

        // ---- resources
        Schema::table('resources', function (Blueprint $table) {
            if ($this->tableHasColumn('resources', 'office_employee_id')
                && ! $this->indexExists('resources', 'resources_office_employee_id_index')) {
                $table->index('office_employee_id', 'resources_office_employee_id_index');
            }

            if ($this->tableHasColumn('resources', 'resource_saved_date')
                && ! $this->indexExists('resources', 'resources_resource_saved_date_index')) {
                $table->index('resource_saved_date', 'resources_resource_saved_date_index');
            }

            if ($this->tableHasColumn('resources', 'created_at')
                && ! $this->indexExists('resources', 'resources_created_at_index')) {
                $table->index('created_at', 'resources_created_at_index');
            }
        });

        // ---- captive_deliveries
        Schema::table('captive_deliveries', function (Blueprint $table) {
            if ($this->tableHasColumn('captive_deliveries', 'captive_date')
                && ! $this->indexExists('captive_deliveries', 'captive_deliveries_captive_date_index')) {
                $table->index('captive_date', 'captive_deliveries_captive_date_index');
            }

            if ($this->tableHasColumn('captive_deliveries', 'created_at')
                && ! $this->indexExists('captive_deliveries', 'captive_deliveries_created_at_index')) {
                $table->index('created_at', 'captive_deliveries_created_at_index');
            }
        });

        // ---- optional: events / informations if they exist in your DB
        if (Schema::hasTable('events')) {
            Schema::table('events', function (Blueprint $table) {
                if ($this->tableHasColumn('events', 'office_employee_id')
                    && ! $this->indexExists('events', 'events_office_employee_id_index')) {
                    $table->index('office_employee_id', 'events_office_employee_id_index');
                }

                if ($this->tableHasColumn('events', 'event_date')
                    && ! $this->indexExists('events', 'events_event_date_index')) {
                    $table->index('event_date', 'events_event_date_index');
                }

                if ($this->tableHasColumn('events', 'created_at')
                    && ! $this->indexExists('events', 'events_created_at_index')) {
                    $table->index('created_at', 'events_created_at_index');
                }
            });
        }

        if (Schema::hasTable('informations')) {
            Schema::table('informations', function (Blueprint $table) {
                if ($this->tableHasColumn('informations', 'office_employee_id')
                    && ! $this->indexExists('informations', 'informations_office_employee_id_index')) {
                    $table->index('office_employee_id', 'informations_office_employee_id_index');
                }

                if ($this->tableHasColumn('informations', 'information_saved_date')
                    && ! $this->indexExists('informations', 'informations_saved_date_index')) {
                    $table->index('information_saved_date', 'informations_saved_date_index');
                }

                if ($this->tableHasColumn('informations', 'created_at')
                    && ! $this->indexExists('informations', 'informations_created_at_index')) {
                    $table->index('created_at', 'informations_created_at_index');
                }
            });
        }
    }

    public function down(): void
    {
        // You can optionally drop the indexes here if you want strict rollback behavior.
        // (Skipping for simplicity.)
    }

    // ---------- helpers (no Doctrine needed) ----------

    private function indexExists(string $table, string $indexName): bool
    {
        // Works on MySQL/MariaDB
        $schema = DB::getDatabaseName();

        return DB::table('information_schema.statistics')
            ->where('table_schema', $schema)
            ->where('table_name', $table)
            ->where('index_name', $indexName)
            ->exists();
    }

    private function tableHasColumn(string $table, string $column): bool
    {
        return Schema::hasColumn($table, $column);
    }
};
