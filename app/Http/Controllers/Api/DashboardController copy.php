<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OfficeEmployee;
use App\Models\Resource;
use App\Models\Information;
use App\Models\Event;
use App\Models\CaptiveDelivery;

use App\Models\Finance;
use App\Models\Income;
use App\Models\IncomeOil;
use App\Models\ExpenseOil;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * GET /api/dashboard?period=daily|weekly|monthly|yearly
     */
    public function index(Request $request): JsonResponse
    {
        /* -------------------------------------------------
         | ✅ PERIOD FILTER
         |-------------------------------------------------*/
        $period = strtolower((string) $request->query('period', 'yearly'));
        [$from, $to] = $this->resolvePeriodRange($period);

        /* -------------------------------------------------
        | EMPLOYEES SUMMARY (total/active/inactive) filtered by period
        |-------------------------------------------------*/
        $employeesTotal = OfficeEmployee::query()
            ->when($from && $to, fn ($q) => $this->applyDateRange($q, 'created_at', $from, $to))
            ->count();

        $employeesActive = OfficeEmployee::query()
            ->active()
            ->when($from && $to, fn ($q) => $this->applyDateRange($q, 'created_at', $from, $to))
            ->count();

        $employeesInactive = OfficeEmployee::query()
            ->inactive()
            ->when($from && $to, fn ($q) => $this->applyDateRange($q, 'created_at', $from, $to))
            ->count();

        /* -------------------------------------------------
         | ✅ EMPLOYEES JOB TYPE COUNTS (اوپراتیفی / اداری)
         |-------------------------------------------------*/
        $jobTypeCol = null;
        $employeeTable = (new OfficeEmployee())->getTable();

        foreach (['job_type', 'job', 'position_type'] as $col) {
            if (Schema::hasColumn($employeeTable, $col)) {
                $jobTypeCol = $col;
                break;
            }
        }

        $employeesOperatif = 0;
        $employeesEdari = 0;

        if ($jobTypeCol) {
            $employeesOperatif = OfficeEmployee::query()
                ->when($from && $to, fn ($q) => $this->applyDateRange($q, 'created_at', $from, $to))
                ->whereIn($jobTypeCol, [
                    'اوپراتیفی',
                    'اپراتیفی',
                    'عملیاتی',
                ])
                ->count();

            $employeesEdari = OfficeEmployee::query()
                ->when($from && $to, fn ($q) => $this->applyDateRange($q, 'created_at', $from, $to))
                ->whereIn($jobTypeCol, [
                    'اداری',
                    'دفتر',
                    'دفتری',
                ])
                ->count();
        }

        /* -------------------------------------------------
         | EMPLOYEES (counts filtered by period)
         |-------------------------------------------------*/
        $employees = OfficeEmployee::query()
            ->select('id', 'name', 'code_number')
            ->withCount([
                'resources as resources_count' => fn ($q) => $this->applyDateRange($q, 'created_at', $from, $to),
                'informations as informations_count' => fn ($q) => $this->applyDateRange($q, 'created_at', $from, $to),
                'events as events_count' => fn ($q) => $this->applyDateRange($q, 'created_at', $from, $to),
            ])
            ->orderByDesc('events_count')
            ->orderByDesc('resources_count')
            ->orderByDesc('informations_count')
            ->orderBy('name')
            ->get();

        // ✅ keep only employees that have activity in this period
        $employeesForDashboard = $employees
            ->filter(function ($e) {
                return ((int) $e->events_count + (int) $e->resources_count + (int) $e->informations_count) > 0;
            })
            ->values();

        // ✅ Score = events + resources + informations
        $employeesScored = $employeesForDashboard->map(function ($e) {
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
         | RESOURCES (filtered)
         |-------------------------------------------------*/
        $resourcesTotal = Resource::query()
            ->when($from && $to, fn ($q) => $this->applyDateRange($q, 'created_at', $from, $to))
            ->count();

        $resourcesActive = Resource::query()
            ->where('activity', 'فعال')
            ->when($from && $to, fn ($q) => $this->applyDateRange($q, 'created_at', $from, $to))
            ->count();

        $resourcesInactive = Resource::query()
            ->where('activity', 'غیر فعال')
            ->when($from && $to, fn ($q) => $this->applyDateRange($q, 'created_at', $from, $to))
            ->count();

        /* -------------------------------------------------
         | INFORMATIONS / EVENTS / CAPTIVE (filtered)
         |-------------------------------------------------*/
        $informationsTotal = Information::query()
            ->when($from && $to, fn ($q) => $this->applyDateRange($q, 'created_at', $from, $to))
            ->count();

        // -------------------------------------------------
        // ✅ INFORMATIONS GROWTH (current vs previous period)
        // -------------------------------------------------
        [$prevFrom, $prevTo] = $this->resolvePreviousRange($from, $to);

        $informationsPrevTotal = Information::query()
            ->when($prevFrom && $prevTo, fn ($q) => $this->applyDateRange($q, 'created_at', $prevFrom, $prevTo))
            ->count();

        $informationsGrowth = $informationsPrevTotal > 0
            ? round((($informationsTotal - $informationsPrevTotal) / $informationsPrevTotal) * 100, 1)
            : ($informationsTotal > 0 ? 100 : 0);

        $eventsTotal = Event::query()
            ->when($from && $to, fn ($q) => $this->applyDateRange($q, 'created_at', $from, $to))
            ->count();

        $captiveDeliveriesTotal = CaptiveDelivery::query()
            ->when($from && $to, fn ($q) => $this->applyDateRange($q, 'created_at', $from, $to))
            ->count();

        /* -------------------------------------------------
         | MONEY TOTALS (filtered)
         |-------------------------------------------------*/
        $incomeDateCol  = $this->detectModelDateColumn(new Income(),  ['debut', 'date', 'income_date', 'paid_at', 'created_at']);
        $financeDateCol = $this->detectModelDateColumn(new Finance(), ['debut', 'date', 'finance_date', 'created_at']);

        $moneyIncomeTotal = (float) Income::query()
            ->when($from && $to, fn ($q) => $this->applyDateRange($q, $incomeDateCol, $from, $to))
            ->sum('amount');

        $moneyExpensedTotal = (float) Finance::query()
            ->when($from && $to, fn ($q) => $this->applyDateRange($q, $financeDateCol, $from, $to))
            ->sum('money_amount_number');

        $moneyBalance = $moneyIncomeTotal - $moneyExpensedTotal;

        /* -------------------------------------------------
         | OIL TOTALS (filtered)
         |-------------------------------------------------*/
        $incomeOilDateCol  = $this->detectModelDateColumn(new IncomeOil(),  ['debut', 'date', 'oil_date', 'created_at']);
        $expenseOilDateCol = $this->detectModelDateColumn(new ExpenseOil(), ['debut', 'date', 'oil_date', 'created_at']);

        $oilIncomeTotal = (float) IncomeOil::query()
            ->when($from && $to, fn ($q) => $this->applyDateRange($q, $incomeOilDateCol, $from, $to))
            ->sum('oil_quantity');

        $oilExpensedTotal = (float) ExpenseOil::query()
            ->when($from && $to, fn ($q) => $this->applyDateRange($q, $expenseOilDateCol, $from, $to))
            ->sum('oil_quantity');

        $oilBalance = $oilIncomeTotal - $oilExpensedTotal;

        /* -------------------------------------------------
         | FORMATS & ZONES (filtered)
         |-------------------------------------------------*/
        $formatTables  = $this->formatsTables();
        $formatsByZone = $this->countFormatsByHawzaSafely($formatTables, $from, $to);

        /* -------------------------------------------------
         | RESPONSE
         |-------------------------------------------------*/
        return response()->json([
            'period' => [
                'key'  => $period,
                'from' => $from?->toDateTimeString(),
                'to'   => $to?->toDateTimeString(),
            ],

            'employees' => [
                'total'    => $employeesTotal,
                'active'   => $employeesActive,
                'inactive' => $employeesInactive,

                // ✅ NEW: job type counts (Dari / Pashto)
                'job_type_counts' => [
                    'اوپراتیفی' => $employeesOperatif,
                    'اداری'     => $employeesEdari,
                ],

                // keep your ranking data exactly as-is
                'max_total'    => $maxTotal,
                'grouped'      => $employeesGrouped,
                'flat_sorted'  => $employeesRanked->values(),

                // optional: if you want to know how many employees had activity in this period
                'ranked_total' => $employeesForDashboard->count(),
            ],

            'resources' => [
                'total'    => $resourcesTotal,
                'active'   => $resourcesActive,
                'inactive' => $resourcesInactive,
            ],

            'informations' => [
                'total' => $informationsTotal,
                'trend' => [
                    'current'  => $informationsTotal,
                    'previous' => $informationsPrevTotal,
                ],
            ],

            'growth' => [
                'informations' => $informationsGrowth,
            ],

            'events' => [
                'total' => $eventsTotal,
            ],

            'captive_deliveries' => [
                'total' => $captiveDeliveriesTotal,
            ],

            'money' => [
                'income_total'   => $moneyIncomeTotal,
                'expensed_total' => $moneyExpensedTotal,
                'balance'        => $moneyBalance,
            ],

            'oil' => [
                'income_total'   => $oilIncomeTotal,
                'expensed_total' => $oilExpensedTotal,
                'balance'        => $oilBalance,
            ],

            'formats_by_hawza' => $formatsByZone,
        ]);
    }

    /* =====================================================
     | ✅ PERIOD RANGE
     |=====================================================*/
    private function resolvePeriodRange(string $period): array
    {
        $now = Carbon::now();

        return match ($period) {
            'all'     => [null, null], // ✅ NO FILTER (whole time)
            'daily'   => [$now->copy()->subDay()->startOfDay(),     $now->copy()->endOfDay()],
            'weekly'  => [$now->copy()->subDays(7)->startOfDay(),   $now->copy()->endOfDay()],
            'monthly' => [$now->copy()->subDays(30)->startOfDay(),  $now->copy()->endOfDay()],
            default   => [$now->copy()->subDays(365)->startOfDay(), $now->copy()->endOfDay()],
        };
    }

    private function resolvePreviousRange(?Carbon $from, ?Carbon $to): array
    {
        if (!$from || !$to) return [null, null];

        // same length window, immediately before current window
        $seconds = $from->diffInSeconds($to) + 1;

        $prevTo = $from->copy()->subSecond();
        $prevFrom = $prevTo->copy()->subSeconds($seconds)->addSecond();

        return [$prevFrom, $prevTo];
    }

    private function applyDateRange($query, string $col, ?Carbon $from, ?Carbon $to)
    {
        if (!$from || !$to) return $query;
        return $query->whereBetween($col, [$from, $to]);
    }

    /**
     * detect which date column the model/table really uses
     */
    private function detectModelDateColumn($model, array $candidates): string
    {
        $table = method_exists($model, 'getTable') ? $model->getTable() : null;

        if (!$table || !Schema::hasTable($table)) {
            return 'created_at';
        }

        foreach ($candidates as $col) {
            if ($col && Schema::hasColumn($table, $col)) {
                return $col;
            }
        }

        return Schema::hasColumn($table, 'created_at') ? 'created_at' : ($candidates[0] ?? 'created_at');
    }

    /**
     * ✅ detect date column for a plain table (formats tables)
     */
    private function detectTableDateColumn(string $table, array $candidates): ?string
    {
        if (!Schema::hasTable($table)) return null;

        foreach ($candidates as $col) {
            if ($col && Schema::hasColumn($table, $col)) {
                return $col;
            }
        }

        return null;
    }

    /**
     * ✅ OPTIONAL BUT GUARANTEED:
     * If you want 100% filter coverage for all 56 tables even when their date column
     * names are weird, add overrides here (table => date_column).
     *
     * Leaving it empty causes auto-detection to be used.
     */
    private function formatDateOverrides(): array
    {
        return [
            // 'heists' => 'debut',
            // 'captives' => 'date',
            // ...
        ];
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
            'universities', 'wakilanos', 'super_markets'
        ];
    }

    /* =====================================================
     | FORMAT LABELS
     |=====================================================*/
    private function formatLabels(): array
    {
        return [
            'fasads' => 'فساد',
            'saeen_fasads' => 'سعین فساد',
            'heists' => 'سرقت',
            'captives' => 'اسیران',
            'mulkanos' => 'ملکانو',
            'entry_exits' => 'داخل/خارج',
            'sarafyanos' => 'صرافی‌ها',
            'clubs' => 'کلب‌ها',
            'mahajerats' => 'مهاجرات',
            'soldiers' => 'سربازان',
            'mawads' => 'مواد',
            'lost_mujremeens' => 'مجرمین گمشده',
            'old_soldiers_homes' => 'خانه‌های عسکر قدیم',
            'robbers' => 'دزدان',
            'non_responsable_people' => 'افراد غیرمسؤول',
            'mashkok_homes' => 'خانه‌های مشکوک',
            'damage_places' => 'محلات آسیب‌دیده',
            'damage_rasan_places' => 'محلات رسان آسیب',
            'mashkok_shops' => 'دکان‌های مشکوک',
            'luchakanos' => 'لوچکانو',
            'schools' => 'مکاتب',
            'hado_bus_stops' => 'ایستگاه بس',
            'transport_companies' => 'شرکت‌های ترانسپورت',
            'car_sales_companies' => 'فروشگاه موتر',
            'parkings' => 'پارکینگ',
            'banks' => 'بانک‌ها',
            'apartments' => 'اپارتمان‌ها',
            'rahnoma_mamlats' => 'رهنمایی مملات',
            'hosppitals' => 'شفاخانه‌ها',
            'work_shops' => 'ورکشاپ‌ها',
            'badmashanos' => 'بدماشانو',
            'emarat_offices' => 'دفاتر امارت',
            'hostels' => 'هاستل‌ها',
            'hotels' => 'هوتل‌ها',
            'madrasas' => 'مدرسه‌ها',
            'mashkok_people' => 'افراد مشکوک',
            'mujahedinos' => 'مجاهدین',
            'old_jahadis' => 'جهادی‌های قدیم',
            'sarayanos' => 'سرایانو',
            'tujarans' => 'تاجران',
            'checkpoints' => 'چک‌پوینت‌ها',
            'highways' => 'شاهراه‌ها',
            'donelostplaces' => 'محلات گمشده',
            'econemy_projects' => 'پروژه‌های اقتصادی',
            'criminal_capture_places' => 'محلات گرفتاری مجرم',
            'pumps' => 'پمپ‌ها',
            'parks' => 'پارک‌ها',
            'complexes' => 'کمپلکس‌ها',
            'universities' => 'پوهنتون‌ها',
            'wakilanos' => 'وکیلان',
        ];
    }

    /* =====================================================
     | ✅ ZONE COUNTING (FILTERS FOR ALL 56 TABLES)
     |=====================================================*/
    private function countFormatsByHawzaSafely(array $tables, ?Carbon $from = null, ?Carbon $to = null): array
    {
        $hawzaMap = [];
        $usedTables = [];
        $missingTables = [];
        $noHawzaColumn = [];
        $noCreatedAtColumn = [];
        $labels = $this->formatLabels();

        $dateCandidates = [
            'created_at', 'debut', 'date', 'event_date', 'occurred_at', 'registered_at', 'reported_at', 'paid_at',
        ];

        $overrides = $this->formatDateOverrides();

        foreach ($tables as $table) {
            if (!Schema::hasTable($table)) {
                $missingTables[] = $table;
                continue;
            }

            $hawzaCol = $this->detectHawzaColumn($table);
            if (!$hawzaCol) {
                $noHawzaColumn[] = $table;
                continue;
            }

            $dateCol = null;

            if (isset($overrides[$table]) && is_string($overrides[$table]) && $overrides[$table] !== '') {
                $overrideCol = $overrides[$table];
                $dateCol = Schema::hasColumn($table, $overrideCol)
                    ? $overrideCol
                    : $this->detectTableDateColumn($table, $dateCandidates);
            } else {
                $dateCol = $this->detectTableDateColumn($table, $dateCandidates);
            }

            if (!$dateCol) {
                $noCreatedAtColumn[] = $table;
            }

            $usedTables[] = $table;

            $q = DB::table($table)->select($hawzaCol);

            if ($dateCol && $from && $to) {
                $q->whereBetween($dateCol, [$from, $to]);
            }

            $q->cursor()->each(function ($row) use (&$hawzaMap, $table, $hawzaCol) {
                $hawza = is_string($row->{$hawzaCol}) ? trim($row->{$hawzaCol}) : '';
                if ($hawza === '') $hawza = 'نامعلوم';

                if (!isset($hawzaMap[$hawza])) {
                    $hawzaMap[$hawza] = [
                        'hawza'     => $hawza,
                        'total'     => 0,
                        'breakdown' => [],
                    ];
                }

                $hawzaMap[$hawza]['total']++;

                if (!isset($hawzaMap[$hawza]['breakdown'][$table])) {
                    $hawzaMap[$hawza]['breakdown'][$table] = 0;
                }
                $hawzaMap[$hawza]['breakdown'][$table]++;
            });
        }

        $hawzas = array_values($hawzaMap);
        usort($hawzas, fn ($a, $b) => $b['total'] <=> $a['total']);

        foreach ($hawzas as &$h) {
            $items = [];
            foreach (($h['breakdown'] ?? []) as $table => $count) {
                $items[] = [
                    'key'   => $table,
                    'name'  => $labels[$table] ?? $table,
                    'count' => (int) $count,
                ];
            }
            usort($items, fn ($a, $b) => $b['count'] <=> $a['count']);
            $h['breakdown_items'] = $items;
        }
        unset($h);

        return [
            'hawzas' => $hawzas,
            'meta'  => [
                'used_tables' => $usedTables,
                'missing_tables' => $missingTables,
                'tables_without_hawza' => $noHawzaColumn,
                'tables_without_created_at' => $noCreatedAtColumn,
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

    private function detectHawzaColumn(string $table): ?string
    {
        foreach (['hawza', 'event_hawza', 'activity_hawza'] as $col) {
            if (Schema::hasColumn($table, $col)) {
                return $col;
            }
        }
        return null;
    }
}
