<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OfficeEmployee;
use App\Models\Resource;
use App\Models\Information;
use App\Models\Event;
use App\Models\CaptiveDelivery;
use App\Models\Account;
use App\Models\Finance;
use App\Models\Income;
use App\Models\IncomeOil;
use App\Models\ExpenseOil;
use App\Models\KhayratReport;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * GET /api/dashboard?period=daily|weekly|monthly|yearly|all&finance_period=monthly|daily|weekly|yearly|all
     */
    public function index(Request $request): JsonResponse
    {
        /* -------------------------------------------------
         | ✅ PERIOD FILTERS
         |-------------------------------------------------*/
        $period        = strtolower((string) $request->query('period', 'yearly'));
        $financePeriod = strtolower((string) $request->query('finance_period', 'monthly'));

        // Main filter for cards and formats
        [$from, $to] = $this->resolvePeriodRange($period);

        // Separate filter for finance/oil
        [$financeFrom, $financeTo] = $this->resolvePeriodRange($financePeriod);

        /* -------------------------------------------------
        | EMPLOYEES SUMMARY (filtered by main period)
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
        | ✅ EMPLOYEES BY JOB TYPE (for chips)
        |-------------------------------------------------*/
        $employeesByJobType = OfficeEmployee::query()
            ->select('job_type', DB::raw('COUNT(*) as total'))
            ->when($from && $to, fn ($q) => $this->applyDateRange($q, 'created_at', $from, $to))
            ->groupBy('job_type')
            ->get()
            ->map(function ($row) {
                $type = trim((string) $row->job_type);
                if (empty($type)) $type = 'نامعلوم';

                // Normalize for display
                $type = str_replace(['ي', 'ى'], 'ی', $type);
                $type = str_replace('ك', 'ک', $type);

                return [
                    'type'  => $type,
                    'count' => (int) $row->total,
                ];
            })
            ->filter(function ($item) {
                // Only include recognized job types
                $normalized = str_replace(' ', '', $item['type']);
                return in_array($normalized, ['اداري', 'اوپراتیفی', 'اداری', 'اوپراتیفي']);
            })
            ->groupBy(function ($item) {
                // Group variants together
                $normalized = str_replace(' ', '', $item['type']);
                if (in_array($normalized, ['اوپراتیفی', 'اوپراتیفي'])) return 'اوپراتیفی';
                if (in_array($normalized, ['اداري', 'اداری'])) return 'اداري';
                return $item['type'];
            })
            ->map(function ($group) {
                return [
                    'type'  => $group->first()['type'],
                    'count' => $group->sum('count'),
                ];
            })
            ->values();

        /* -------------------------------------------------
        | EMPLOYEES WITH COUNTS (OLD METHOD - for old table view)
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

        $employeesForDashboard = $employees
            ->filter(function ($e) {
                return ((int) $e->events_count + (int) $e->resources_count + (int) $e->informations_count) > 0;
            })
            ->values();

        $employeesScored = $employeesForDashboard->map(function ($e) {
            $events     = (int) $e->events_count;
            $resources  = (int) $e->resources_count;
            $infos      = (int) $e->informations_count;

            return [
                'id'                 => $e->id,
                'name'               => $e->name,
                'code_number'        => $e->code_number,
                'events_count'       => $events,
                'resources_count'    => $resources,
                'informations_count' => $infos,
                'total'              => $events + $resources + $infos,
            ];
        });

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

        $employeesRankedOld = $employeesSorted->map(function ($row, $idx) use ($gradeOf) {
            $row['rank']  = $idx + 1;
            $row['grade'] = $gradeOf((int) $row['total']);
            return $row;
        })->values();

        /* -------------------------------------------------
        | EMPLOYEES WITH KHAYRAT REPORTS (NEW METHOD - for new table view)
        |-------------------------------------------------*/
        $employeesNew = OfficeEmployee::query()
            ->select('id', 'name', 'code_number')
            ->withCount([
                'resources as resources_count' => fn ($q) => $this->applyDateRange($q, 'created_at', $from, $to),
                'informations as informations_count' => fn ($q) => $this->applyDateRange($q, 'created_at', $from, $to),
                'events as events_count' => fn ($q) => $this->applyDateRange($q, 'created_at', $from, $to),

                // ✅ IMPORTANT FIX:
                // KhayratReport uses saved_date (date), not created_at.
                'khayratReports as khayrat_reports_count' => fn ($q) => $this->applyDateRange($q, 'saved_date', $from, $to),
            ])
            ->orderByDesc('events_count')
            ->orderByDesc('resources_count')
            ->orderByDesc('informations_count')
            ->orderByDesc('khayrat_reports_count')
            ->orderBy('name')
            ->get();

        $employeesForDashboardNew = $employeesNew
            ->filter(function ($e) {
                return (
                    (int) $e->events_count +
                    (int) $e->resources_count +
                    (int) $e->informations_count +
                    (int) $e->khayrat_reports_count
                ) > 0;
            })
            ->values();

        $employeesScoredNew = $employeesForDashboardNew->map(function ($e) {
            $events    = (int) $e->events_count;
            $resources = (int) $e->resources_count;
            $infos     = (int) $e->informations_count;
            $khayrat   = (int) $e->khayrat_reports_count;

            return [
                'id'                   => $e->id,
                'name'                 => $e->name,
                'code_number'          => $e->code_number,
                'events_count'         => $events,
                'resources_count'      => $resources,
                'informations_count'   => $infos,
                'khayrat_reports_count' => $khayrat,
                'total'                => $events + $resources + $infos + $khayrat,
            ];
        });

        $employeesSortedNew = $employeesScoredNew
            ->sortByDesc('events_count')
            ->sortByDesc('resources_count')
            ->sortByDesc('informations_count')
            ->sortByDesc('khayrat_reports_count')
            ->sortByDesc('total')
            ->values();

        $maxTotalNew = (int) ($employeesSortedNew->max('total') ?? 0);

        $employeesRankedNew = $employeesSortedNew->map(function ($row, $idx) use ($gradeOf) {
            $row['rank']  = $idx + 1;
            $row['grade'] = $gradeOf((int) $row['total']);
            return $row;
        })->values();

        /* -------------------------------------------------
         | RESOURCES (filtered by main period)
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
         | INFORMATIONS (filtered by main period)
         |-------------------------------------------------*/
        $informationsTotal = Information::query()
            ->when($from && $to, fn ($q) => $this->applyDateRange($q, 'created_at', $from, $to))
            ->count();

        // Jenayee types
        $informationsByJenayeeType = Information::query()
            ->select('jenayee_info_type', DB::raw('COUNT(*) as total'))
            ->when($from && $to, fn ($q) => $this->applyDateRange($q, 'created_at', $from, $to))
            ->groupBy('jenayee_info_type')
            ->get()
            ->map(function ($row) {
                $name = is_string($row->jenayee_info_type) ? trim($row->jenayee_info_type) : '';
                return [
                    'type'  => $name !== '' ? $name : 'نامعلوم',
                    'count' => (int) $row->total,
                ];
            })
            ->sortByDesc('count')
            ->values();

        // Syasi types
        $informationsBySyasiType = Information::query()
            ->select('syasi_info_type', DB::raw('COUNT(*) as total'))
            ->when($from && $to, fn ($q) => $this->applyDateRange($q, 'created_at', $from, $to))
            ->groupBy('syasi_info_type')
            ->get()
            ->map(function ($row) {
                $name = is_string($row->syasi_info_type) ? trim($row->syasi_info_type) : '';
                return [
                    'type'  => $name !== '' ? $name : 'نامعلوم',
                    'count' => (int) $row->total,
                ];
            })
            ->sortByDesc('count')
            ->values();

        /* -------------------------------------------------
         | EVENTS (filtered by main period)
         |-------------------------------------------------*/
        $eventsTotal = Event::query()
            ->when($from && $to, fn ($q) => $this->applyDateRange($q, 'created_at', $from, $to))
            ->count();

        // Event types breakdown (سیاسي، جنایي، الهی، ترافیکي)
        $eventsByType = Event::query()
            ->select(DB::raw('COALESCE(event_type, event_specific_type) as type'), DB::raw('COUNT(*) as total'))
            ->when($from && $to, fn ($q) => $this->applyDateRange($q, 'created_at', $from, $to))
            ->groupBy(DB::raw('COALESCE(event_type, event_specific_type)'))
            ->get()
            ->map(function ($row) {
                $type = trim((string) $row->type);
                if ($type === '') $type = 'نامعلوم';
                return [
                    'type' => $type,
                    'count' => (int) $row->total,
                ];
            })
            ->filter(function ($item) {
                return in_array($item['type'], ['سیاسي', 'جنایي', 'الهی', 'ترافیکي']);
            })
            ->sortByDesc('count')
            ->values();


        // Fill missing types with zero
        $allEventTypes = ['سیاسي', 'جنایي', 'الهی', 'ترافیکي'];
        $eventsByTypeFinal = collect($allEventTypes)->map(function ($type) use ($eventsByType) {
            $found = $eventsByType->firstWhere('type', $type);
            return [
                'type'  => $type,
                'count' => $found ? $found['count'] : 0,
            ];
        })->values();

        $captiveDeliveriesTotal = CaptiveDelivery::query()
            ->when($from && $to, fn ($q) => $this->applyDateRange($q, 'created_at', $from, $to))
            ->count();

        /* -------------------------------------------------
        | MONEY TOTALS (filtered by finance period)
        |-------------------------------------------------*/
        $incomeDateCol  = $this->detectModelDateColumn(new Income(),  ['debut', 'date', 'income_date', 'paid_at', 'created_at']);
        $financeDateCol = $this->detectModelDateColumn(new Finance(), ['debut', 'date', 'finance_date', 'created_at']);

        $moneyIncomeTotal = (float) Income::query()
            ->when($financeFrom && $financeTo, fn ($q) => $this->applyDateRange($q, $incomeDateCol, $financeFrom, $financeTo))
            ->sum('amount');

        $moneyExpensedTotal = (float) Finance::query()
            ->when($financeFrom && $financeTo, fn ($q) => $this->applyDateRange($q, $financeDateCol, $financeFrom, $financeTo))
            ->sum('money_amount_number');

        $accountsTotalBalance = (float) Account::query()->sum('price');

        /* -------------------------------------------------
        | OIL TOTALS (filtered by finance period)
        |-------------------------------------------------*/
        $incomeOilDateCol  = $this->detectModelDateColumn(new IncomeOil(),  ['debut', 'date', 'oil_date', 'created_at']);
        $expenseOilDateCol = $this->detectModelDateColumn(new ExpenseOil(), ['debut', 'date', 'oil_date', 'created_at']);

        $oilIncomeTotal = (float) IncomeOil::query()
            ->when($financeFrom && $financeTo, fn ($q) => $this->applyDateRange($q, $incomeOilDateCol, $financeFrom, $financeTo))
            ->sum('oil_quantity');

        $oilExpensedTotal = (float) ExpenseOil::query()
            ->when($financeFrom && $financeTo, fn ($q) => $this->applyDateRange($q, $expenseOilDateCol, $financeFrom, $financeTo))
            ->sum('liter_in_number');

        $oilBalance = $oilIncomeTotal - $oilExpensedTotal;

        /* -------------------------------------------------
         | FORMATS BY MAIN DISTRICT (filtered by main period)
         |-------------------------------------------------*/
        $formatTables  = $this->formatsTables();
        $formatsByZone = $this->countFormatsByMainDistrictSafely($formatTables, $from, $to);

        /* -------------------------------------------------
         | ✅ KHAYRAT REPORTS SUMMARY (filtered by main period)
         |-------------------------------------------------*/
        $khayratReportsTotal = KhayratReport::query()
            ->when($from && $to, fn ($q) => $this->applyDateRange($q, 'saved_date', $from, $to))
            ->count();

        $khayratByZone = KhayratReport::query()
            ->select('zone', DB::raw('COUNT(*) as total'))
            ->when($from && $to, fn ($q) => $this->applyDateRange($q, 'saved_date', $from, $to))
            ->groupBy('zone')
            ->get()
            ->map(function ($row) {
                $z = trim((string) $row->zone);
                return [
                    'zone'  => $z !== '' ? $z : 'نامعلوم',
                    'count' => (int) $row->total,
                ];
            })
            ->sortByDesc('count')
            ->values();

        $khayratByHawza = KhayratReport::query()
            ->select('hawza', DB::raw('COUNT(*) as total'))
            ->when($from && $to, fn ($q) => $this->applyDateRange($q, 'saved_date', $from, $to))
            ->groupBy('hawza')
            ->get()
            ->map(function ($row) {
                $h = trim((string) $row->hawza);
                return [
                    'hawza' => $h !== '' ? $h : 'نامعلوم',
                    'count' => (int) $row->total,
                ];
            })
            ->sortByDesc('count')
            ->values();

        /* -------------------------------------------------
         | RESPONSE
         |-------------------------------------------------*/
        return response()->json([
            'period' => [
                'key'  => $period,
                'from' => $from?->toDateTimeString(),
                'to'   => $to?->toDateTimeString(),
            ],
            'finance_period' => [
                'key'  => $financePeriod,
                'from' => $financeFrom?->toDateTimeString(),
                'to'   => $financeTo?->toDateTimeString(),
            ],

            'employees' => [
                'total'        => $employeesTotal,
                'active'       => $employeesActive,
                'inactive'     => $employeesInactive,
                'job_types'    => $employeesByJobType,
                'max_total'    => $maxTotal,
                'flat_sorted'  => $employeesRankedOld->values(),
                'ranked_total' => $employeesForDashboard->count(),
            ],

            'employees_new' => [
                'flat_sorted' => $employeesRankedNew->values(),
                'max_total'   => $maxTotalNew,
            ],

            'resources' => [
                'total'    => $resourcesTotal,
                'active'   => $resourcesActive,
                'inactive' => $resourcesInactive,
            ],

            'informations' => [
                'total' => $informationsTotal,
                'jenayee_types' => $informationsByJenayeeType,
                'syasi_types'   => $informationsBySyasiType,
            ],

            'events' => [
                'total' => $eventsTotal,
                'types' => $eventsByTypeFinal,
            ],

            'captive_deliveries' => [
                'total' => $captiveDeliveriesTotal,
            ],

            'money' => [
                'income_total'   => $moneyIncomeTotal,
                'expensed_total' => $moneyExpensedTotal,
                'balance'        => $accountsTotalBalance,
            ],

            'oil' => [
                'income_total'   => $oilIncomeTotal,
                'expensed_total' => $oilExpensedTotal,
                'balance'        => $oilBalance,
            ],

            // ✅ existing formats payload
            'formats_by_zone' => $formatsByZone,

            // ✅ new khayrat payload (optional for your frontend)
            'khayrat' => [
                'total' => $khayratReportsTotal,
                'by_zone' => $khayratByZone,
                'by_hawza' => $khayratByHawza,
            ],
        ]);
    }

    /* =====================================================
     | HELPER METHODS
     |=====================================================*/
    private function resolvePeriodRange(string $period): array
    {
        $now = Carbon::now();

        return match ($period) {
            'all'     => [null, null],
            'daily'   => [$now->copy()->subDay()->startOfDay(),     $now->copy()->endOfDay()],
            'weekly'  => [$now->copy()->subDays(7)->startOfDay(),   $now->copy()->endOfDay()],
            'monthly' => [$now->copy()->subDays(30)->startOfDay(),  $now->copy()->endOfDay()],
            default   => [$now->copy()->subDays(365)->startOfDay(), $now->copy()->endOfDay()],
        };
    }

    private function applyDateRange($query, string $col, ?Carbon $from, ?Carbon $to)
    {
        if (!$from || !$to) return $query;
        return $query->whereBetween($col, [$from, $to]);
    }

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

        return Schema::hasColumn($table, 'created_at')
            ? 'created_at'
            : ($candidates[0] ?? 'created_at');
    }

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

    private function formatDateOverrides(): array
    {
        // If any table uses a special date column, return: ['table_name' => 'date_col']
        return [];
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
            'super_markets' => 'سوپرمارکت‌ها',
        ];
    }

    private function countFormatsByMainDistrictSafely(array $tables, ?Carbon $from = null, ?Carbon $to = null): array
    {
        $districtMap = [];
        $usedTables = [];
        $missingTables = [];
        $noDistrictColumn = [];
        $noCreatedAtColumn = [];
        $labels = $this->formatLabels();

        $dateCandidates = [
            'created_at',
            'debut',
            'date',
            'event_date',
            'occurred_at',
            'registered_at',
            'reported_at',
            'paid_at',
        ];

        $overrides = $this->formatDateOverrides();

        foreach ($tables as $table) {
            if (!Schema::hasTable($table)) {
                $missingTables[] = $table;
                continue;
            }

            $districtCol = $this->detectMainDistrictColumn($table);
            if (!$districtCol) {
                $noDistrictColumn[] = $table;
                continue;
            }

            $dateCol = null;
            if (isset($overrides[$table]) && is_string($overrides[$table]) && $overrides[$table] !== '') {
                $overrideCol = $overrides[$table];
                if (Schema::hasColumn($table, $overrideCol)) {
                    $dateCol = $overrideCol;
                } else {
                    $dateCol = $this->detectTableDateColumn($table, $dateCandidates);
                }
            } else {
                $dateCol = $this->detectTableDateColumn($table, $dateCandidates);
            }

            if (!$dateCol) {
                $noCreatedAtColumn[] = $table;
            }

            $usedTables[] = $table;

            $q = DB::table($table)->select($districtCol);

            if ($dateCol && $from && $to) {
                $q->whereBetween($dateCol, [$from, $to]);
            }

            $q->cursor()->each(function ($row) use (&$districtMap, $table, $districtCol) {
                $district = is_string($row->{$districtCol}) ? trim($row->{$districtCol}) : '';

                if ($district === '') $district = 'نامعلوم';

                if (!isset($districtMap[$district])) {
                    $districtMap[$district] = [
                        'zone'      => $district,
                        'total'     => 0,
                        'breakdown' => [],
                    ];
                }

                $districtMap[$district]['total']++;

                if (!isset($districtMap[$district]['breakdown'][$table])) {
                    $districtMap[$district]['breakdown'][$table] = 0;
                }
                $districtMap[$district]['breakdown'][$table]++;
            });
        }

        $zones = array_values($districtMap);
        usort($zones, fn ($a, $b) => $b['total'] <=> $a['total']);

        foreach ($zones as &$z) {
            $items = [];
            foreach (($z['breakdown'] ?? []) as $table => $count) {
                $items[] = [
                    'key'   => $table,
                    'name'  => $labels[$table] ?? $table,
                    'count' => (int) $count,
                ];
            }
            usort($items, fn ($a, $b) => $b['count'] <=> $a['count']);
            $z['breakdown_items'] = $items;
        }
        unset($z);

        return [
            'zones' => $zones,
            'meta'  => [
                'used_tables' => $usedTables,
                'missing_tables' => $missingTables,
                'tables_without_zone' => $noDistrictColumn,
                'tables_without_created_at' => $noCreatedAtColumn,
                'strategy' => 'php_counting_no_group_by',
                'grouped_by' => 'main_district',
            ],
        ];
    }

    private function detectMainDistrictColumn(string $table): ?string
    {
        $candidates = [
            'main_district',
            'district',
            'district_name',
            'now_district',
            'activity_district',
            'current_district',
            'districts',
        ];

        foreach ($candidates as $col) {
            if (Schema::hasColumn($table, $col)) {
                return $col;
            }
        }

        return null;
    }
}
