<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Information;
use App\Models\OfficeEmployee;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class InfoEventDashboardController extends Controller
{
    /** Collect and normalize filters from query string */
    protected function filters(Request $r): array
    {
        $from = $r->filled('from') ? Carbon::parse($r->get('from'))->startOfDay() : null;
        $to   = $r->filled('to')   ? Carbon::parse($r->get('to'))->endOfDay()   : null;

        return [
            'from'        => $from,
            'to'          => $to,
            'zone'        => trim((string)$r->get('zone', '')),
            'hawza'       => trim((string)$r->get('hawza', '')),
            'resource_id' => $r->integer('resource_id') ?: null,
            'employee_id' => $r->integer('employee_id') ?: null,
            'group_by'    => in_array($r->get('group_by'), ['day','week','month']) ? $r->get('group_by') : 'month',
            'limit'       => max(1, min(100, (int)$r->get('limit', 10))),
        ];
    }

    /** Apply filters to Events query */
    protected function applyEventFilters($q, array $f)
    {
        // event_date is stored as string in your migration; we still can compare with whereDate
        if ($f['from']) $q->whereDate('event_date', '>=', $f['from']->toDateString());
        if ($f['to'])   $q->whereDate('event_date', '<=', $f['to']->toDateString());

        if ($f['zone'] !== '')  $q->where('event_zone', $f['zone']);
        if ($f['hawza'] !== '') $q->where('event_hawza', $f['hawza']);

        if ($f['resource_id']) $q->where('resource_id', $f['resource_id']);
        if ($f['employee_id']) $q->where('office_employee_id', $f['employee_id']);

        return $q;
    }

    /** Apply filters to Information query */
    protected function applyInformationFilters($q, array $f)
    {
        if ($f['from']) $q->whereDate('information_saved_date', '>=', $f['from']->toDateString());
        if ($f['to'])   $q->whereDate('information_saved_date', '<=', $f['to']->toDateString());

        // zone mapping (use whichever you treat as zone)
        if ($f['zone'] !== '') {
            $q->where(function($qq) use ($f) {
                $qq->where('now_province', $f['zone'])
                   ->orWhere('main_province', $f['zone']);
            });
        }

        // hawza mapping example (adjust if you store differently)
        if ($f['hawza'] !== '') {
            $q->where('now_district', $f['hawza']);
        }

        if ($f['resource_id']) $q->where('resource_id', $f['resource_id']);
        if ($f['employee_id']) $q->where('office_employee_id', $f['employee_id']);

        return $q;
    }

    /** KPI cards */
    public function cards(Request $r)
    {
        $f = $this->filters($r);

        $eventsQ = $this->applyEventFilters(Event::query(), $f);
        $infoQ   = $this->applyInformationFilters(Information::query(), $f);

        $totalEvents      = (clone $eventsQ)->count();
        $totalInformation = (clone $infoQ)->count();

        $startMonth = Carbon::now()->startOfMonth()->toDateString();
        $endMonth   = Carbon::now()->endOfMonth()->toDateString();

        $eventsThisMonth = (clone $eventsQ)
            ->whereBetween(DB::raw('DATE(event_date)'), [$startMonth, $endMonth])
            ->count();

        $infoThisMonth = (clone $infoQ)
            ->whereBetween(DB::raw('DATE(information_saved_date)'), [$startMonth, $endMonth])
            ->count();

        $eventEmployeeCount = (clone $eventsQ)->whereNotNull('office_employee_id')->distinct()->count('office_employee_id');
        $infoEmployeeCount  = (clone $infoQ)->whereNotNull('office_employee_id')->distinct()->count('office_employee_id');

        return response()->json([
            'cards' => [
                ['key' => 'total_events',           'title' => 'ټول پېښې',                 'value' => $totalEvents],
                ['key' => 'total_information',      'title' => 'ټول مالومات',              'value' => $totalInformation],
                ['key' => 'events_this_month',      'title' => 'پېښې (سږ میاشت)',           'value' => $eventsThisMonth],
                ['key' => 'information_this_month', 'title' => 'مالومات (سږ میاشت)',        'value' => $infoThisMonth],
                ['key' => 'employees_in_events',    'title' => 'فعاله کارکوونکي (پېښې)',    'value' => $eventEmployeeCount],
                ['key' => 'employees_in_info',      'title' => 'فعاله کارکوونکي (مالومات)', 'value' => $infoEmployeeCount],
            ],
        ]);
    }

    /** SQL bucket expr + PHP label format */
    protected function bucketExpr(string $col, string $groupBy): array
    {
        switch ($groupBy) {
            case 'day':
                return ["DATE($col)", 'Y-m-d'];
            case 'week':
                // ISO week (Mon-based); we just echo the key back as label
                return ["CONCAT(YEARWEEK($col, 3))", 'o-\WW'];
            case 'month':
            default:
                return ["DATE_FORMAT($col, '%Y-%m-01')", 'Y-m-01'];
        }
    }

    /** Chart: events over time */
    public function eventsOverTime(Request $r)
    {
        $f = $this->filters($r);
        [$bucketSql, $phpFormat] = $this->bucketExpr('event_date', $f['group_by']);

        $rows = $this->applyEventFilters(Event::query(), $f)
            ->selectRaw("$bucketSql AS bucket, COUNT(*) AS total")
            ->whereNotNull('event_date')
            ->groupBy('bucket')
            ->orderBy('bucket')
            ->get();

        $data = $rows->map(function ($row) use ($phpFormat) {
            $date = Carbon::parse($row->bucket);
            return [
                'bucket' => $row->bucket,
                'label'  => $date->format($phpFormat),
                'total'  => (int)$row->total,
            ];
        });

        return response()->json(['series' => $data]);
    }

    /** Chart: information over time */
    public function informationOverTime(Request $r)
    {
        $f = $this->filters($r);
        [$bucketSql, $phpFormat] = $this->bucketExpr('information_saved_date', $f['group_by']);

        $rows = $this->applyInformationFilters(Information::query(), $f)
            ->selectRaw("$bucketSql AS bucket, COUNT(*) AS total")
            ->whereNotNull('information_saved_date')
            ->groupBy('bucket')
            ->orderBy('bucket')
            ->get();

        $data = $rows->map(function ($row) use ($phpFormat) {
            $date = Carbon::parse($row->bucket);
            return [
                'bucket' => $row->bucket,
                'label'  => $date->format($phpFormat),
                'total'  => (int)$row->total,
            ];
        });

        return response()->json(['series' => $data]);
    }

    /** Chart: employees by zone (events) */
    public function employeesByZone(Request $r)
    {
        $f = $this->filters($r);

        $rows = $this->applyEventFilters(Event::query(), $f)
            ->selectRaw('event_zone AS zone, COUNT(DISTINCT office_employee_id) AS employees')
            ->whereNotNull('event_zone')
            ->groupBy('zone')
            ->orderByDesc('employees')
            ->limit(20)
            ->get();

        return response()->json(['rows' => $rows]);
    }

    /** Table: top employees by number of resources involved (events) */
    public function topEmployeesByResources(Request $r)
    {
        $f = $this->filters($r);

        // First aggregate by employee id
        $rowsBase = $this->applyEventFilters(Event::query(), $f)
            ->whereNotNull('office_employee_id')
            ->selectRaw('office_employee_id, COUNT(DISTINCT resource_id) AS resources_count, COUNT(*) AS events_count')
            ->groupBy('office_employee_id')
            ->orderByDesc('resources_count')
            ->orderByDesc('events_count')
            ->limit(20)
            ->get();

        // Load employees (we only need id; display_name accessor will format the name)
        $empMap = OfficeEmployee::whereIn('id', $rowsBase->pluck('office_employee_id')->all())
            ->get(['id'])
            ->keyBy('id');

        $rows = $rowsBase->map(function ($row) use ($empMap) {
            $emp = $empMap->get($row->office_employee_id);
            return [
                'office_employee_id' => (int)$row->office_employee_id,
                'employee_name'      => $emp?->display_name,
                'resources_count'    => (int)$row->resources_count,
                'events_count'       => (int)$row->events_count,
            ];
        });

        return response()->json(['rows' => $rows]);
    }

    /** Table: recent events */
    public function recentEvents(Request $r)
    {
        $f = $this->filters($r);

        $rows = $this->applyEventFilters(
                Event::query()->with([
                    'resource:id,name',
                    'officeEmployee:id', // rely on display_name accessor
                ]), $f
            )
            ->orderByRaw('CASE WHEN event_date IS NULL THEN 1 ELSE 0 END, event_date DESC, id DESC')
            ->limit($f['limit'])
            ->get([
                'id','resource_id','office_employee_id','event_type','event_specific_type',
                'event_date','event_hour','event_zone','event_hawza','event_specific_place','tafseel'
            ])
            ->map(function ($e) {
                return [
                    'id'                  => $e->id,
                    'resource'            => $e->resource?->name,
                    'employee'            => $e->officeEmployee?->display_name,
                    'event_type'          => $e->event_type,
                    'event_specific_type' => $e->event_specific_type,
                    'event_date'          => $e->event_date ? Carbon::parse($e->event_date)->toDateString() : null,
                    'event_hour'          => $e->event_hour,
                    'event_zone'          => $e->event_zone,
                    'event_hawza'         => $e->event_hawza,
                    'place'               => $e->event_specific_place,
                    'tafseel'             => str($e->tafseel)->limit(120),
                ];
            });

        return response()->json(['rows' => $rows]);
    }

    /** Table: recent information */
    public function recentInformation(Request $r)
    {
        $f = $this->filters($r);

        $rows = $this->applyInformationFilters(
                Information::query()->with([
                    'resource:id,name',
                    'officeEmployee:id', // rely on display_name accessor
                ]), $f
            )
            ->orderByRaw('CASE WHEN information_saved_date IS NULL THEN 1 ELSE 0 END, information_saved_date DESC, id DESC')
            ->limit($f['limit'])
            ->get([
                'id','resource_id','office_employee_id','name','nick_name','information_saved_date','information_degree',
                'now_province','now_district','tafseel'
            ])
            ->map(function ($i) {
                return [
                    'id'         => $i->id,
                    'resource'   => $i->resource?->name,
                    'employee'   => $i->officeEmployee?->display_name,
                    'name'       => $i->name,
                    'nick_name'  => $i->nick_name,
                    'saved_date' => $i->information_saved_date ? Carbon::parse($i->information_saved_date)->toDateString() : null,
                    'degree'     => $i->information_degree,
                    'province'   => $i->now_province,
                    'district'   => $i->now_district,
                    'tafseel'    => str($i->tafseel)->limit(120),
                ];
            });

        return response()->json(['rows' => $rows]);
    }
}
