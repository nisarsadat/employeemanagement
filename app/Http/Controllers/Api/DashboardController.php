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
use App\Models\InformationCategory;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

use Morilog\Jalali\Jalalian;

class DashboardController extends Controller
{
    /**
     * GET /api/dashboard
     *
     * Query:
     *  - period=daily|weekly|monthly|yearly|all (default: yearly)
     *  - finance_period=daily|weekly|monthly|yearly|all (default: monthly)
     *
     * ✅ IMPORTANT:
     * - Everything is FORCED to Hijri Shamsi (Jalali)
     * - calendar param is ignored even if sent from frontend
     */
    public function index(Request $request): JsonResponse
    {
        // ✅ FORCE Hijri Shamsi only
        $calendar = 'shamsi';

        $period        = strtolower((string) $request->query('period', 'yearly'));
        $financePeriod = strtolower((string) $request->query('finance_period', 'monthly'));

        // Main filter (cards + formats + khayrat + etc.) => Shamsi-based
        [$from, $to] = $this->resolvePeriodRangeShamsi($period);

        // Finance filter (money + oil + employee tables) => Shamsi-based
        [$financeFrom, $financeTo] = $this->resolvePeriodRangeShamsi($financePeriod);
        $resourceDateCol = 'created_at';
        $resourceDateCol = $this->detectModelDateColumn(new Resource(), [
            'resource_saved_date',
            'saved_date',
            'created_at',
        ]);
        $informationDateCol = $this->detectModelDateColumn(new Information(), [
            'information_saved_date',
            'saved_date',
            'created_at',
        ]);

        $eventDateCol = $this->detectModelDateColumn(new Event(), [
            'event_date',
            'created_at',
        ]);

        $khayratDateCol = $this->detectModelDateColumn(new KhayratReport(), [
            'saved_date',
            'created_at',
        ]);
        /* -------------------------------------------------
        | ✅ EMPLOYEES SUMMARY (MAIN period)
        |-------------------------------------------------*/
        $employeesTotal = OfficeEmployee::query()
            ->when($from && $to, fn ($q) => $this->applyDateRange($q, 'created_at', $from, $to))
            ->count();

        /* Activity rule: administrative job type OR any active resource. */
        $employeesActive = OfficeEmployee::query()->active()->count();
        $employeesInactive = OfficeEmployee::query()->inactive()->count();

        /* -------------------------------------------------
        | ✅ EMPLOYEES BY JOB TYPE (chips) - MAIN period
        |-------------------------------------------------*/
        $employeesByJobType = OfficeEmployee::query()
            ->select('job_type', DB::raw('COUNT(*) as total'))
            ->when($from && $to, fn ($q) => $this->applyDateRange($q, 'created_at', $from, $to))
            ->groupBy('job_type')
            ->get()
            ->map(function ($row) {
                $type = trim((string) $row->job_type);
                if ($type === '') $type = 'نامعلوم';

                $type = str_replace(['ي', 'ى'], 'ی', $type);
                $type = str_replace('ك', 'ک', $type);

                $type = preg_replace('/\s+/', ' ', $type);
                $type = trim($type);

                $noSpace = str_replace(' ', '', $type);
                if (in_array($noSpace, ['اوپراتیفی', 'اوپراتیفي'])) $type = 'اوپراتیفی';
                if (in_array($noSpace, ['اداري', 'اداری'])) $type = 'اداري';

                return [
                    'type'  => $type,
                    'count' => (int) $row->total,
                ];
            })
            ->groupBy('type')
            ->map(fn ($group, $type) => [
                'type'  => $type,
                'count' => (int) $group->sum('count'),
            ])
            ->values()
            ->sortByDesc('count')
            ->values();

        /* -------------------------------------------------
        | ✅ EMPLOYEES TABLE (OLD) - FINANCE period (Shamsi-based range)
        |-------------------------------------------------*/

        $employeesOld = OfficeEmployee::query()
            ->select('id', 'name', 'nick_name', 'code_number', 'picture')
            ->withCount([
                'resources as resources_count' => fn ($q) => $this->applyDateRange($q, $resourceDateCol, $financeFrom, $financeTo),           
                'informations as informations_count' => fn ($q) => $this->applyDateRange($q, 'created_at', $financeFrom, $financeTo),

                // ✅ 1 active resource = 1 point
                'resources as active_resources_count' => fn ($q) => $this->applyDateRange($q, $resourceDateCol, $financeFrom, $financeTo)->where('activity', 'فعال'),
                'events as events_count' => fn ($q) => $this->applyDateRange($q, 'created_at', $financeFrom, $financeTo),
            ])
            ->orderByDesc('events_count')
            ->orderByDesc('resources_count')
            ->orderByDesc('informations_count')
            ->orderBy('name')
            ->get();

        $employeesForDashboardOld = $employeesOld
            ->filter(fn ($e) => ((int) $e->events_count + (int) $e->resources_count + (int) $e->informations_count) > 0)
            ->values();

        $employeesScoredOld = $employeesForDashboardOld->map(function ($e) {
            $events     = (int) $e->events_count;
            $resources  = (int) $e->resources_count;
            $infos      = (int) $e->informations_count;
            $activeResources = (int) $e->active_resources_count;

            // ✅ امتیاز: 1 active resource = 1, 1 info = 1, 2 events = 1
            $score = $activeResources
                + $infos
                + intdiv($events, 2);

          return [
            'id'                 => $e->id,
            'name'               => $e->name,
            'nick_name'          => $e->nick_name,
            'picture' => $e->picture ? Storage::url($e->picture) : null,
            'code_number'        => $e->code_number,
            'events_count'       => $events,
            'resources_count'    => $resources,
            'active_resources_count' => $activeResources,
            'informations_count' => $infos,
            'total'              => $score,
            'score'              => $score,
        ];
        });

        $employeesSortedOld = $employeesScoredOld->sortByDesc('total')->values();
        $maxTotalOld = (int) ($employeesSortedOld->max('total') ?? 0);

        /* ✅ درجه‌بندی by the points — fixed bands */
        $gradeOf = function (int $points) {
            if ($points >= 100) return 'عالی';
            if ($points >= 50)  return 'خوب';
            if ($points >= 20)  return 'متوسط';
            if ($points >= 1)   return 'کم';
            return 'خیلی کم';
        };

        $employeesRankedOld = $employeesSortedOld->map(function ($row, $idx) use ($gradeOf) {
            $row['rank']  = $idx + 1;
            $row['grade'] = $gradeOf((int) $row['total']);
            return $row;
        })->values();

        /* -------------------------------------------------
        | ✅ EMPLOYEES TABLE (NEW) - includes KHAYRAT - FINANCE period
        |-------------------------------------------------*/

        $employeesNew = OfficeEmployee::query()
        ->select('id', 'name', 'nick_name', 'code_number', 'picture')
        ->withCount([
            'resources as resources_count' => fn ($q) =>
                $this->applyDateRange($q, $resourceDateCol, $financeFrom, $financeTo),

            // ✅ 1 active resource = 1 point
            'resources as active_resources_count' => fn ($q) =>
                $this->applyDateRange($q, $resourceDateCol, $financeFrom, $financeTo)
                    ->where('activity', 'فعال'),

            'informations as informations_count' => fn ($q) =>
                $this->applyDateRange($q, $informationDateCol, $financeFrom, $financeTo),

            'events as events_count' => fn ($q) =>
                $this->applyDateRange($q, $eventDateCol, $financeFrom, $financeTo),

            'khayratReports as khayrat_reports_count' => fn ($q) =>
                $this->applyDateRange($q, $khayratDateCol, $financeFrom, $financeTo),

            // ✅ ترسره شوی ارزونې / ترسره شوی غونډې
            'securityAssessments as security_assessments_count' => fn ($q) =>
                $this->applyDateRange($q, 'security_assessments.created_at', $financeFrom, $financeTo),

            'meetings as meetings_count' => fn ($q) =>
                $this->applyDateRange($q, 'meetings.created_at', $financeFrom, $financeTo),
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
            $assessments = (int) $e->security_assessments_count;
            $meetingsPerformed = (int) $e->meetings_count;
            $activeResources = (int) $e->active_resources_count;

            // ✅ امتیاز: 1 active resource = 1, 1 info = 1, 2 events = 1, 20 khayrat = 1
            $score = $activeResources
                + $infos
                + intdiv($events, 2)
                + intdiv($khayrat, 20);

            return [
            'id'                    => $e->id,
            'name'                  => $e->name,
            'nick_name'             => $e->nick_name,
            'code_number'           => $e->code_number,
            'picture' => $e->picture ? Storage::url($e->picture) : null,
            'events_count'          => $events,
            'resources_count'       => $resources,
            'informations_count'    => $infos,
            'khayrat_reports_count' => $khayrat,
            'security_assessments_count' => $assessments,
            'meetings_count'        => $meetingsPerformed,
            'active_resources_count' => $activeResources,
            'total'                 => $score,
            'score'                 => $score,
        ];
        });

        $employeesSortedNew = $employeesScoredNew->sortByDesc('total')->values();
        $maxTotalNew = (int) ($employeesSortedNew->max('total') ?? 0);

        $employeesRankedNew = $employeesSortedNew->map(function ($row, $idx) use ($gradeOf) {
            $row['rank']  = $idx + 1;
            $row['grade'] = $gradeOf((int) $row['total']);
            return $row;
        })->values();

        /* -------------------------------------------------
        | ✅ RESOURCES TABLE - FINANCE period
        | information points come from the اطلاع کتگوری points,
        | each event = 1, each khayrat report = 1,
        | each day in the period with no khayrat report = -1
        |-------------------------------------------------*/
        $categoryPoints = InformationCategory::query()
            ->get(['information_category', 'points'])
            ->mapWithKeys(function ($category) {
                $name = trim((string) $category->information_category);

                return [$name => (int) $category->points];
            });

        $informationPointsByResource = [];
        Information::query()
            ->select('resource_id', 'information_category', DB::raw('COUNT(*) as total'))
            ->whereNotNull('resource_id')
            ->when($financeFrom && $financeTo, fn ($q) => $this->applyDateRange($q, $informationDateCol, $financeFrom, $financeTo))
            ->groupBy('resource_id', 'information_category')
            ->get()
            ->each(function ($row) use (&$informationPointsByResource, $categoryPoints) {
                $category = trim((string) $row->information_category);
                $points = (int) ($categoryPoints[$category] ?? 0);
                $resourceId = (int) $row->resource_id;
                $informationPointsByResource[$resourceId] = ($informationPointsByResource[$resourceId] ?? 0)
                    + ($points * (int) $row->total);
            });

        $periodDays = 0;
        if ($financeFrom && $financeTo) {
            $periodDays = (int) $financeFrom->copy()->startOfDay()->diffInDays($financeTo->copy()->startOfDay()) + 1;
        }

        $resourcesForRanking = Resource::query()
            ->select('id', 'name', 'nick_name', 'code_number', 'picture', 'activity')
            ->withCount([
                'informations as informations_count' => fn ($q) =>
                    $this->applyDateRange($q, $informationDateCol, $financeFrom, $financeTo),

                'events as events_count' => fn ($q) =>
                    $this->applyDateRange($q, $eventDateCol, $financeFrom, $financeTo),

                'khayratReports as khayrat_reports_count' => fn ($q) =>
                    $this->applyDateRange($q, $khayratDateCol, $financeFrom, $financeTo),
            ])
            ->get();

        $reportDaysByResource = KhayratReport::query()
            ->select('resource_id', DB::raw('COUNT(DISTINCT DATE(`'.$khayratDateCol.'`)) as days'))
            ->whereNotNull('resource_id')
            ->when($financeFrom && $financeTo, fn ($q) => $this->applyDateRange($q, $khayratDateCol, $financeFrom, $financeTo))
            ->groupBy('resource_id')
            ->pluck('days', 'resource_id');

        $resourcesWithActivity = $resourcesForRanking
            ->filter(function ($r) {
                return (
                    (int) $r->events_count +
                    (int) $r->informations_count +
                    (int) $r->khayrat_reports_count
                ) > 0;
            })
            ->values();

        $resourcesScored = $resourcesWithActivity->map(function ($r) use ($informationPointsByResource, $periodDays, $reportDaysByResource) {
            $events = (int) $r->events_count;
            $infos  = (int) $r->informations_count;
            $khayrat = (int) $r->khayrat_reports_count;
            $reportDays = (int) ($reportDaysByResource[$r->id] ?? 0);
            if ($periodDays > 0) {
                $reportDays = min($reportDays, $periodDays);
            }
            $missedDays = $periodDays > 0 ? max(0, $periodDays - $reportDays) : 0;
            $informationPoints = (int) ($informationPointsByResource[(int) $r->id] ?? 0);

            $score = $informationPoints + $events + $khayrat - $missedDays;

            $picture = null;
            if ($r->picture) {
                $picture = str_starts_with((string) $r->picture, 'http')
                    ? $r->picture
                    : url($r->picture);
            }

            return [
                'id'                    => $r->id,
                'name'                  => $r->name,
                'nick_name'             => $r->nick_name,
                'code_number'           => $r->code_number,
                'picture'               => $picture,
                'activity'              => $r->activity,
                'events_count'          => $events,
                'informations_count'    => $infos,
                'information_points'    => $informationPoints,
                'khayrat_reports_count' => $khayrat,
                'missed_report_days'    => $missedDays,
                'total'                 => $score,
                'score'                 => $score,
            ];
        });

        $resourcesSorted = $resourcesScored->sortByDesc('total')->values();
        $maxResourceScore = (int) ($resourcesSorted->max('total') ?? 0);

        $resourcesRanked = $resourcesSorted->map(function ($row, $idx) use ($gradeOf) {
            $row['rank']  = $idx + 1;
            $row['grade'] = $gradeOf((int) $row['total']);
            return $row;
        })->values();

        /* -------------------------------------------------
        | RESOURCES (MAIN period)
        |-------------------------------------------------*/
        $resourcesTotal = Resource::query()
            ->when($from && $to, fn ($q) => $this->applyDateRange($q, $resourceDateCol, $from, $to))
            ->count();

        $resourcesActive = Resource::query()
            ->where('activity', 'فعال')
            ->when($from && $to, fn ($q) => $this->applyDateRange($q, $resourceDateCol, $from, $to))
            ->count();

        $resourcesInactive = Resource::query()
            ->where('activity', 'غیر فعال')
            ->when($from && $to, fn ($q) => $this->applyDateRange($q, $resourceDateCol, $from, $to))
            ->count();

        $resourcesByHawza = Resource::query()
            ->select('hawza', DB::raw('COUNT(*) as total'))
            ->when($from && $to, fn ($q) => $this->applyDateRange($q, $resourceDateCol, $from, $to))
            ->groupBy('hawza')
            ->get()
            ->map(function ($row) {
                $h = trim((string) $row->hawza);
                if ($h === '') $h = 'نامعلوم';

                $h = str_replace(['ي', 'ى'], 'ی', $h);
                $h = str_replace('ك', 'ک', $h);
                $h = preg_replace('/\s+/', ' ', $h);
                $h = trim($h);

                return [
                    'hawza' => $h,
                    'count' => (int) $row->total,
                ];
            })
            ->groupBy('hawza')
            ->map(fn ($g, $hawza) => [
                'hawza' => $hawza,
                'count' => (int) $g->sum('count'),
            ])
            ->values()
            ->sortByDesc('count')
            ->values();

/* -------------------------------------------------
| INFORMATIONS (MAIN period) + dashboard stats
|-------------------------------------------------*/


$informationBase = Information::query()
    ->when($from && $to, fn ($q) => $this->applyDateRange($q, $informationDateCol, $from, $to));

$informationsTotal = (clone $informationBase)->count();

$infoCountSharOFasad = $this->informationTypeCount($informationBase, [
    'شر او فساد',
]);

$infoCountSaenBilFasad = $this->informationTypeCount($informationBase, [
    'ساعین بالفساد',
]);

$infoCountJasoosi = $this->informationTypeCount($informationBase, [
    'جاسوسی',
    'جاسوسي',
    'جواسیس',
]);

$informationsByJenayeeType = (clone $informationBase)
    ->select('jenayee_info_type', DB::raw('COUNT(*) as total'))
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

$informationsBySyasiType = (clone $informationBase)
    ->select('syasi_info_type', DB::raw('COUNT(*) as total'))
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
        | EVENTS (MAIN period) + types chips
        | Same date column as the event scores: event_date when it exists.
        |-------------------------------------------------*/
        $eventsTotal = Event::query()
            ->when($from && $to, fn ($q) => $this->applyDateRange($q, $eventDateCol, $from, $to))
            ->count();

       $eventsByType = Event::query()
            ->select('event_type', 'event_specific_type')
            ->when($from && $to, fn ($q) => $this->applyDateRange($q, $eventDateCol, $from, $to))
            ->get()
            ->map(function ($row) {
                $type = trim((string) ($row->event_type ?: $row->event_specific_type));

                if ($type === '') {
                    $type = 'نامعلوم';
                }

                // normalize Arabic/Persian chars
                $type = str_replace(['ي', 'ى'], 'ی', $type);
                $type = str_replace('ك', 'ک', $type);
                $type = preg_replace('/\s+/', ' ', $type);
                $type = trim($type);

                // normalize variants
                if (in_array($type, ['سیاسی', 'سیاسي', 'syasi'], true)) {
                    $type = 'سیاسي';
                }

                if (in_array($type, ['جنایی', 'جینایی', 'جنایي', 'jenayee', 'jinayee'], true)) {
                    $type = 'جنایي';
                } 
                if (in_array($type, ['الهی', 'الهي'], true)) {
                    $type = 'الهی';
                } 
                if (in_array($type, ['ترافیکي' , 'ترافیکي'], true)) {
                    $type = 'ترافیکي';
                }

                return [
                    'type' => $type,
                    'count' => 1,
                ];
            })
            ->filter(fn ($item) => in_array($item['type'], ['سیاسي', 'جنایي', 'الهی', 'ترافیکي'], true))
            ->groupBy('type')
            ->map(fn ($group, $type) => [
                'type'  => $type,
                'count' => (int) $group->sum('count'),
            ])
            ->values()
            ->sortByDesc('count')
            ->values();

        $allEventTypes = ['سیاسي', 'جنایي', 'الهی', 'ترافیکي'];

        $eventsByTypeFinal = collect($allEventTypes)->map(function ($type) use ($eventsByType) {
            $found = $eventsByType->firstWhere('type', $type);

            return [
                'type'  => $type,
                'count' => $found ? (int) $found['count'] : 0,
            ];
        })->values();

        $captiveDeliveriesTotal = CaptiveDelivery::query()
            ->when($from && $to, fn ($q) => $this->applyDateRange($q, 'created_at', $from, $to))
            ->count();

        /* -------------------------------------------------
        | MONEY TOTALS (FINANCE period)
        |-------------------------------------------------*/
        $incomeDateCol = Schema::hasColumn('incomes', 'date')
            ? 'date'
            : $this->detectModelDateColumn(new Income(), ['date', 'created_at']);
        $financeDateCol = Schema::hasColumn('finances', 'date')
            ? 'date'
            : $this->detectModelDateColumn(new Finance(), ['date', 'created_at']);

        $moneyIncomeTotal = (float) Income::query()
            ->when($financeFrom && $financeTo, fn ($q) => $this->applyDateRange($q, $incomeDateCol, $financeFrom, $financeTo))
            ->sum('amount');

        $moneyExpensedTotal = (float) Finance::query()
            ->when($financeFrom && $financeTo, function ($q) use ($financeFrom, $financeTo) {
                $q->whereBetween(DB::raw('COALESCE(`date`, `created_at`)'), [$financeFrom, $financeTo]);
            })
            ->sum('money_amount_number');


        $accountsTotalBalance = (float) Account::query()->sum('price');

        /* -------------------------------------------------
        | OIL TOTALS (FINANCE period)
        |-------------------------------------------------*/
        $incomeOilDateCol  = $this->detectModelDateColumn(new IncomeOil(),  ['debut', 'date', 'oil_date', 'created_at']);
        $expenseOilDateCol = Schema::hasColumn('expense_oils', 'date')
            ? 'date'
            : $this->detectModelDateColumn(new ExpenseOil(), ['date', 'created_at']);

        $oilIncomeTotal = (float) IncomeOil::query()
            ->when($financeFrom && $financeTo, fn ($q) => $this->applyDateRange($q, $incomeOilDateCol, $financeFrom, $financeTo))
            ->sum('oil_quantity');

        $oilExpensedTotal = (float) ExpenseOil::query()
            ->when($financeFrom && $financeTo, fn ($q) => $this->applyDateRange($q, $expenseOilDateCol, $financeFrom, $financeTo))
            ->sum('liter_in_number');

        $oilBalance = $oilIncomeTotal - $oilExpensedTotal;

        /* -------------------------------------------------
        | ✅ 56 FORMATS BY MAIN DISTRICT (MAIN period)
        |-------------------------------------------------*/
        $formatTables  = $this->formatsTables();
        $formatsByZone = $this->countFormatsByMainDistrictSafely($formatTables, $from, $to);

        /* -------------------------------------------------
        | ✅ KHAYRAT REPORTS (MAIN period)
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
        | ✅ MONTHLY BUCKETS (FORCED SHAMSI YEAR)
        |-------------------------------------------------*/
        $now = Carbon::now();
        $monthlyBuckets = $this->buildShamsiYearMonthBuckets($now);
        $resourcesMonthlyCounts = $this->monthlyCount(Resource::query(), $resourceDateCol, $monthlyBuckets);
        $eventsMonthlyCounts = $this->monthlyCount(Event::query(), $eventDateCol, $monthlyBuckets);
        $informationsMonthlyCounts  = $this->monthlyCount(Information::query(), $informationDateCol, $monthlyBuckets);
        $khayratMonthlyCounts = $this->monthlyCount(KhayratReport::query(), $khayratDateCol, $monthlyBuckets);

        $moneyIncomeMonthly  = $this->monthlySum(Income::query(), $incomeDateCol, 'amount', $monthlyBuckets);
        $moneyExpenseMonthly = $this->monthlySum(Finance::query(), $financeDateCol, 'money_amount_number', $monthlyBuckets);

        $oilIncomeMonthly    = $this->monthlySum(IncomeOil::query(), $incomeOilDateCol, 'oil_quantity', $monthlyBuckets);
        $oilExpenseMonthly   = $this->monthlySum(ExpenseOil::query(), $expenseOilDateCol, 'liter_in_number', $monthlyBuckets);

        // ✅ calendar meta always shamsi
        $calendarMeta = $this->shamsiCalendarMeta($now);

        return response()->json([
            'calendar' => 'shamsi',
            'calendar_meta' => $calendarMeta,

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
                'max_total'    => $maxTotalOld,
                'flat_sorted'  => $employeesRankedOld->values(),
                'ranked_total' => $employeesForDashboardOld->count(),
            ],

            'employees_new' => [
                'flat_sorted' => $employeesRankedNew->values(),
                'max_total'   => $maxTotalNew,
            ],

            'resources' => [
                'total'    => $resourcesTotal,
                'active'   => $resourcesActive,
                'inactive' => $resourcesInactive,
                'resources_by_hawza' => $resourcesByHawza,
                'max_total'   => $maxResourceScore,
                'flat_sorted' => $resourcesRanked,
            ],

            'resources_monthly_counts' => $resourcesMonthlyCounts,


            'informations' => [
                    'total' => $informationsTotal,
                    'jenayee_types' => $informationsByJenayeeType,
                    'syasi_types'   => $informationsBySyasiType,
                    'stats' => [
                        'shar'  => (int) $infoCountSharOFasad,
                        'saeen' => (int) $infoCountSaenBilFasad,
                        'spy'   => (int) $infoCountJasoosi,
                    ],
                ],
            'informations_monthly_counts' => $informationsMonthlyCounts,

            'events' => [
                'total' => $eventsTotal,
                'types' => $eventsByTypeFinal,
            ],
            'events_monthly_counts' => $eventsMonthlyCounts,

            'captive_deliveries' => [
                'total' => $captiveDeliveriesTotal,
            ],

            'money' => [
                'income_total'   => $moneyIncomeTotal,
                'expensed_total' => $moneyExpensedTotal,
                'balance'        => $accountsTotalBalance,
            ],
            'money_monthly' => [
                'income'  => $moneyIncomeMonthly,
                'expense' => $moneyExpenseMonthly,
            ],

            'oil' => [
                'income_total'   => $oilIncomeTotal,
                'expensed_total' => $oilExpensedTotal,
                'balance'        => $oilBalance,
            ],
            'oil_monthly' => [
                'income'  => $oilIncomeMonthly,
                'expense' => $oilExpenseMonthly,
            ],

            'formats_by_zone' => $formatsByZone,

            'khayrat' => [
                'total'   => $khayratReportsTotal,
                'by_zone' => $khayratByZone,
                'by_hawza'=> $khayratByHawza,
            ],
            'khayrat_monthly_counts' => $khayratMonthlyCounts,

            'monthly_buckets' => collect($monthlyBuckets)->map(function ($b) {
                return [
                    'key'   => $b['key'],     // e.g. 1404-11
                    'month' => $b['month'],   // 1..12
                    'label' => $b['label'],   // e.g. دلو
                    'from'  => $b['from']->toDateTimeString(),
                    'to'    => $b['to']->toDateTimeString(),
                ];
            })->values(),
        ]);
    }

    private function normalizeFaValue(string $value): string
{
    $value = trim($value);
    $value = str_replace(['ي', 'ى'], 'ی', $value);
    $value = str_replace('ك', 'ک', $value);
    $value = preg_replace('/\s+/', ' ', $value);
    return trim($value);
}

private function normalizedFaColumn(string $column): string
{
    return "TRIM(REPLACE(REPLACE(REPLACE($column,'ي','ی'),'ى','ی'),'ك','ک'))";
}

private function informationTypeCount($baseQuery, array $variants): int
{
    $normalizedVariants = collect($variants)
        ->map(fn ($v) => $this->normalizeFaValue((string) $v))
        ->filter()
        ->unique()
        ->values()
        ->all();

    return (clone $baseQuery)
        ->where(function ($q) use ($normalizedVariants) {
            $q->whereIn(DB::raw($this->normalizedFaColumn('syasi_info_type')), $normalizedVariants)
              ->orWhereIn(DB::raw($this->normalizedFaColumn('jenayee_info_type')), $normalizedVariants);
        })
        ->count();
}
    /* =====================================================
     | ✅ SHAMSI PERIOD RANGE (daily/weekly/monthly/yearly/all)
     |=====================================================*/
    private function resolvePeriodRangeShamsi(string $period): array
    {
        $now = Carbon::now();
        $jNow = Jalalian::fromCarbon($now);

        $jy = (int) $jNow->getYear();
        $jm = (int) $jNow->getMonth();
        $jd = (int) $jNow->getDay();

        return match ($period) {
            'all' => [null, null],

            // ✅ today (shamsi) -> gregorian Carbon range
            'daily' => [
                (new Jalalian($jy, $jm, $jd))->toCarbon()->startOfDay(),
                (new Jalalian($jy, $jm, $jd))->toCarbon()->endOfDay(),
            ],

            // ✅ last 7 shamsi days (today included)
            'weekly' => [
                (new Jalalian($jy, $jm, $jd))->subDays(6)->toCarbon()->startOfDay(),
                (new Jalalian($jy, $jm, $jd))->toCarbon()->endOfDay(),
            ],

            // ✅ last 30 shamsi days (today included)
            'monthly' => [
                (new Jalalian($jy, $jm, $jd))->subDays(29)->toCarbon()->startOfDay(),
                (new Jalalian($jy, $jm, $jd))->toCarbon()->endOfDay(),
            ],

            // ✅ full current shamsi year
            'yearly' => $this->shamsiYearRange($now),

            default => $this->shamsiYearRange($now),
        };
    }


    private function shamsiYearRange(Carbon $now): array
    {
        $j = Jalalian::fromCarbon($now);
        $jy = (int) $j->getYear();

        $start = (new Jalalian($jy, 1, 1))->toCarbon()->startOfDay();
        $nextStart = (new Jalalian($jy + 1, 1, 1))->toCarbon()->startOfDay();
        $end = $nextStart->copy()->subSecond();

        return [$start, $end];
    }

    /* =====================================================
     | ✅ MONTH BUCKETS FOR CURRENT SHAMSI YEAR (12 months)
     |=====================================================*/
    private function buildShamsiYearMonthBuckets(Carbon $now): array
    {
        $j = Jalalian::fromCarbon($now);
        $jy = (int) $j->getYear();

        $buckets = [];

        for ($m = 1; $m <= 12; $m++) {
            $mStart = (new Jalalian($jy, $m, 1))->toCarbon()->startOfDay();

            $ny = $jy;
            $nm = $m + 1;
            if ($nm === 13) { $nm = 1; $ny = $jy + 1; }

            $nextStart = (new Jalalian($ny, $nm, 1))->toCarbon()->startOfDay();
            $mEnd = $nextStart->copy()->subSecond();

            $buckets[] = [
                'from'  => $mStart,
                'to'    => $mEnd,
                'key'   => sprintf('%04d-%02d', $jy, $m),
                'month' => (int) $m,
                'label' => $this->shamsiMonthName($m),
            ];
        }

        return $buckets;
    }

    private function shamsiMonthName(int $month): string
    {
        $shamsi = ['حمل','ثور','جوزا','سرطان','اسد','سنبله','میزان','عقرب','قوس','جدی','دلو','حوت'];
        $idx = $month - 1;
        return ($idx >= 0 && $idx <= 11) ? $shamsi[$idx] : (string) $month;
    }

    private function shamsiCalendarMeta(Carbon $now): array
    {
        $j = Jalalian::fromCarbon($now);
        $y = (int) $j->getYear();
        $m = (int) $j->getMonth();
        $d = (int) $j->getDay();

        return [
            'year' => $y,
            'month' => $m,
            'day' => $d,
            'today_key' => sprintf('%04d-%02d-%02d', $y, $m, $d),
            'month_start_key' => sprintf('%04d-%02d-01', $y, $m),
            'month_key' => sprintf('%04d-%02d', $y, $m),
            'month_name' => $this->shamsiMonthName($m),
        ];
    }

    /* =====================================================
     | MONTH HELPERS
     |=====================================================*/
    private function monthlyCount($query, string $dateCol, array $buckets): array
    {
        return $this->monthlyAggregate($query, $dateCol, null, $buckets);
    }

    private function monthlySum($query, string $dateCol, string $sumCol, array $buckets): array
    {
        return $this->monthlyAggregate($query, $dateCol, $sumCol, $buckets);
    }

    /**
     * One grouped pass over the same inclusive ranges whereBetween used.
     */
    private function monthlyAggregate($query, string $dateCol, ?string $sumCol, array $buckets): array
    {
        if ($buckets === []) {
            return [];
        }

        $expressions = [];
        $bindings = [];
        foreach ($buckets as $i => $bucket) {
            $measure = $sumCol ? "`{$sumCol}`" : '1';
            $expressions[] = "SUM(CASE WHEN `{$dateCol}` BETWEEN ? AND ? THEN {$measure} ELSE 0 END) AS b{$i}";
            $bindings[] = $bucket['from']->toDateTimeString();
            $bindings[] = $bucket['to']->toDateTimeString();
        }

        $row = (clone $query)->selectRaw(implode(', ', $expressions), $bindings)->first();

        $out = [];
        foreach ($buckets as $i => $bucket) {
            $alias = 'b'.$i;
            $out[] = [
                'key'   => $bucket['key'],
                'month' => $bucket['month'],
                'label' => $bucket['label'],
                'value' => $sumCol ? (float) ($row->{$alias} ?? 0) : (int) ($row->{$alias} ?? 0),
            ];
        }

        return $out;
    }

    /* =====================================================
     | EXISTING HELPERS (unchanged)
     |=====================================================*/
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
        return [];
    }

    /* =====================================================
     | 56 FORMAT TABLES (unchanged)
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
        $noDateColumn = [];
        $labels = $this->formatLabels();

        $dateCandidates = [
            'created_at','debut','date','event_date','occurred_at','registered_at','reported_at','paid_at',
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
                $dateCol = Schema::hasColumn($table, $overrideCol)
                    ? $overrideCol
                    : $this->detectTableDateColumn($table, $dateCandidates);
            } else {
                $dateCol = $this->detectTableDateColumn($table, $dateCandidates);
            }

            if (!$dateCol) {
                $noDateColumn[] = $table;
            }

            $usedTables[] = $table;

            if (!preg_match('/^[A-Za-z0-9_]+$/', $districtCol) || !preg_match('/^[A-Za-z0-9_]+$/', $table)) {
                continue;
            }

            $part = DB::table($table)
                ->selectRaw("`{$districtCol}` as district_value, COUNT(*) as total, '{$table}' as format_table")
                ->when($dateCol && $from && $to, fn ($qq) => $qq->whereBetween($dateCol, [$from, $to]))
                ->groupBy($districtCol);

            $union = isset($union) ? $union->unionAll($part) : $part;
        }

        $rows = isset($union) ? $union->get() : collect();

        foreach ($rows as $r) {
            $district = is_string($r->district_value) ? trim($r->district_value) : '';
            if ($district === '') $district = 'نامعلوم';
            $table = (string) $r->format_table;

            if (!isset($districtMap[$district])) {
                $districtMap[$district] = [
                    'zone'      => $district,
                    'total'     => 0,
                    'breakdown' => [],
                ];
            }

            $count = (int) $r->total;
            $districtMap[$district]['total'] += $count;

            if (!isset($districtMap[$district]['breakdown'][$table])) {
                $districtMap[$district]['breakdown'][$table] = 0;
            }
            $districtMap[$district]['breakdown'][$table] += $count;
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
                'tables_without_date_col' => $noDateColumn,
                'strategy' => 'sql_group_by_count',
                'grouped_by' => 'main_district',
            ],
        ];
    }

    private function detectMainDistrictColumn(string $table): ?string
    {
        $candidates = [
            'main_district','district','district_name','now_district','activity_district','current_district','districts',
        ];

        foreach ($candidates as $col) {
            if (Schema::hasColumn($table, $col)) {
                return $col;
            }
        }

        return null;
    }
}
