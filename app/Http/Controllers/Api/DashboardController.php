<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OfficeEmployee;
use App\Models\Resource;
use App\Models\Information;
use App\Models\Event;
use App\Models\CaptiveDelivery;

// ✅ ADDED (ONLY FOR NEW TOTALS)
use App\Models\Finance;
use App\Models\Income;
use App\Models\IncomeOil;
use App\Models\ExpenseOil;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    /**
     * GET /api/dashboard
     */
    public function index(): JsonResponse
    {
        /* -------------------------------------------------
         | EMPLOYEES  ✅ (MODIFIED ONLY THIS PART)
         |-------------------------------------------------*/
        $employees = OfficeEmployee::query()
            ->select('id', 'name', 'code_number')
            ->withCount(['resources', 'informations', 'events'])
            ->orderByDesc('events_count')
            ->orderByDesc('resources_count')
            ->orderByDesc('informations_count')
            ->orderBy('name')
            ->get();

        // ✅ Score = events + resources + informations
        $employeesScored = $employees->map(function ($e) {
            $events = (int) $e->events_count;
            $resources = (int) $e->resources_count;
            $infos = (int) $e->informations_count;

            return [
                'id' => $e->id,
                'name' => $e->name,
                'code_number' => $e->code_number,
                'events_count' => $events,
                'resources_count' => $resources,
                'informations_count' => $infos,
                'total' => $events + $resources + $infos,
            ];
        });

        // ✅ Ranking after scoring
        $employeesSorted = $employeesScored
            ->sortByDesc('events_count')
            ->sortByDesc('resources_count')
            ->sortByDesc('informations_count')
            ->sortByDesc('total')
            ->values();

        $maxTotal = (int) ($employeesSorted->max('total') ?? 0);

        $gradeOf = function (int $total) use ($maxTotal) {
            if ($maxTotal <= 0) return 'نامعلوم';

            $p = ($total / $maxTotal) * 100;

            if ($p >= 80) return 'عالی';
            if ($p >= 60) return 'خوب';
            if ($p >= 40) return 'متوسط';
            if ($p >= 20) return 'کم';
            return 'خیلی کم';
        };

        $employeesRanked = $employeesSorted->map(function ($row, $idx) use ($gradeOf) {
            $row['rank'] = $idx + 1;
            $row['grade'] = $gradeOf((int) $row['total']);
            return $row;
        })->values();

        // ✅ Grouping by (events|resources|infos) as before, but upgraded
        $employeesGrouped = collect($employeesRanked)
            ->groupBy(fn ($e) => $e['events_count'] . '|' . $e['resources_count'] . '|' . $e['informations_count'])
            ->map(function ($group, $key) {
                [$events, $resources, $informations] = explode('|', $key);

                return [
                    'events_count'       => (int) $events,
                    'resources_count'    => (int) $resources,
                    'informations_count' => (int) $informations,
                    'employees_count'    => $group->count(),
                    'employees'          => $group->map(fn ($e) => [
                        'id'          => $e['id'],
                        'name'        => $e['name'],
                        'code_number' => $e['code_number'],
                        'rank'        => $e['rank'],
                        'grade'       => $e['grade'],
                        'total'       => $e['total'],
                    ])->values(),
                ];
            })
            ->values();

        /* -------------------------------------------------
         | RESOURCES
         |-------------------------------------------------*/
        $resourcesTotal    = Resource::count();
        $resourcesActive   = Resource::where('activity', 'فعال')->count();
        $resourcesInactive = Resource::where('activity', 'غیر فعال')->count();

        /* -------------------------------------------------
         | INFORMATIONS / EVENTS / CAPTIVE
         |-------------------------------------------------*/
        $informationsTotal      = Information::count();
        $eventsTotal            = Event::count();
        $captiveDeliveriesTotal = CaptiveDelivery::count();

        /* -------------------------------------------------
         | ✅ NEW: MONEY TOTALS (Income / Expense / Balance)
         |-------------------------------------------------*/
        $moneyIncomeTotal = (float) (Income::query()->sum('amount') ?? 0);
        $moneyExpensedTotal = (float) (Finance::query()->sum('money_amount_number') ?? 0);
        $moneyBalance = $moneyIncomeTotal - $moneyExpensedTotal;

        /* -------------------------------------------------
         | ✅ NEW: OIL TOTALS (Income / Expense / Balance)
         |-------------------------------------------------*/
        $oilIncomeTotal = (float) (IncomeOil::query()->sum('oil_quantity') ?? 0);
        $oilExpensedTotal = (float) (ExpenseOil::query()->sum('oil_quantity') ?? 0);
        $oilBalance = $oilIncomeTotal - $oilExpensedTotal;

        /* -------------------------------------------------
         | FORMATS & ZONES
         |-------------------------------------------------*/
        $formatTables = $this->formatsTables();
        $formatsByZone = $this->countFormatsByZoneSafely($formatTables);

        /* -------------------------------------------------
         | RESPONSE
         |-------------------------------------------------*/
        return response()->json([
            'employees' => [
                'total'        => $employees->count(),
                'max_total'    => $maxTotal,
                'grouped'      => $employeesGrouped,
                'flat_sorted'  => $employeesRanked->values(),
            ],

            'resources' => [
                'total'    => $resourcesTotal,
                'active'   => $resourcesActive,
                'inactive' => $resourcesInactive,
            ],

            'informations' => [
                'total' => $informationsTotal,
            ],

            'events' => [
                'total' => $eventsTotal,
            ],

            'captive_deliveries' => [
                'total' => $captiveDeliveriesTotal,
            ],

            // ✅ ADDED (ONLY)
            'money' => [
                'income_total'  => $moneyIncomeTotal,
                'expensed_total'=> $moneyExpensedTotal,
                'balance'       => $moneyBalance,
            ],

            // ✅ ADDED (ONLY)
            'oil' => [
                'income_total'  => $oilIncomeTotal,
                'expensed_total'=> $oilExpensedTotal,
                'balance'       => $oilBalance,
            ],

            'formats_by_zone' => $formatsByZone,
        ]);
    }

    /* =====================================================
     | FORMAT TABLES
     |=====================================================*/
    private function formatsTables(): array
    {
        return [
            'fasads', 'saeen_fasads', 'heists', 'captives', 'mulkanos',
            'entry_exits', 'sarafyanos', 'clubs', 'mahajerats', 'soldiers',
            'mawads', 'lost_mujremeens', 'old_soldiers_homes', 'robbers',
            'non_responsable_people', 'mashkok_homes', 'damage_places',
            'damage_rasan_places', 'mashkok_shops', 'luchakanos',
            'schools', 'hado_bus_stops', 'transport_companies',
            'car_sales_companies', 'parkings', 'banks', 'apartments',
            'rahnoma_mamlats', 'hosppitals', 'work_shops',
            'badmashanos', 'emarat_offices', 'hostels', 'hotels',
            'madrasas', 'mashkok_people', 'mujahedinos', 'old_jahadis',
            'sarayanos', 'tujarans', 'checkpoints', 'highways',
            'donelostplaces', 'econemy_projects',
            'criminal_capture_places', 'pumps', 'parks', 'complexes',
            'universities', 'wakilanos',
        ];
    }

    /* =====================================================
     | ZONE COUNTING (NO SQL GROUP BY)
     |=====================================================*/
    private function countFormatsByZoneSafely(array $tables): array
    {
        $zoneMap = [];
        $usedTables = [];
        $missingTables = [];
        $noZoneColumn = [];

        foreach ($tables as $table) {
            if (!Schema::hasTable($table)) {
                $missingTables[] = $table;
                continue;
            }

            $zoneCol = $this->detectZoneColumn($table);
            if (!$zoneCol) {
                $noZoneColumn[] = $table;
                continue;
            }

            $usedTables[] = $table;

            DB::table($table)
                ->select($zoneCol)
                ->cursor()
                ->each(function ($row) use (&$zoneMap, $table, $zoneCol) {
                    $zone = is_string($row->{$zoneCol})
                        ? trim($row->{$zoneCol})
                        : '';

                    if ($zone === '') {
                        $zone = 'نامعلوم';
                    }

                    if (!isset($zoneMap[$zone])) {
                        $zoneMap[$zone] = [
                            'zone'      => $zone,
                            'total'     => 0,
                            'breakdown' => [],
                        ];
                    }

                    $zoneMap[$zone]['total']++;

                    if (!isset($zoneMap[$zone]['breakdown'][$table])) {
                        $zoneMap[$zone]['breakdown'][$table] = 0;
                    }
                    $zoneMap[$zone]['breakdown'][$table]++;
                });
        }

        $zones = array_values($zoneMap);
        usort($zones, fn ($a, $b) => $b['total'] <=> $a['total']);

        return [
            'zones' => $zones,
            'meta'  => [
                'used_tables' => $usedTables,
                'missing_tables' => $missingTables,
                'tables_without_zone' => $noZoneColumn,
                'strategy' => 'php_counting_no_group_by',
            ],
        ];
    }

    private function detectZoneColumn(string $table): ?string
    {
        foreach (['now_zone', 'activity_zone', 'zone'] as $col) {
            if (Schema::hasColumn($table, $col)) {
                return $col;
            }
        }
        return null;
    }
}
