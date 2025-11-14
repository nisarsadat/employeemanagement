<?php
// app/Http/Controllers/Dashboard/FifthyFormatFourDashboardController.php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Schema;

/**
 * FF4 Dashboard API
 *
 * - /api/dashboard/ff4/combined   (zone, from, to, granularity=daily|monthly|yearly, search)
 * - /api/dashboard/ff4/breakdown  (format, field, + same filters)
 */
class FifthyFormatFourDashboardController extends Controller
{
    /** Map formats => [model, label, titleCol, provinceCol?, zoneCol?] */
    protected array $formats = [
        'checkpoint'              => [\App\Models\fifthyformatfour\Checkpoint::class,            'چیک پوینټ',                  'checkpoint_name',  'province',      null],
        'company'                 => [\App\Models\fifthyformatfour\Company::class,               'کمپنۍ',                        'com_name',         'province',      null],
        'complex'                 => [\App\Models\fifthyformatfour\Complex::class,               'کمپلکس',                       'complex_name',     'province',      'district'],
        'criminal_capture_place'  => [\App\Models\fifthyformatfour\CriminalCapturePlace::class,  'د جرم نیولو ځای',             'name',             'province',      'district'],
        'deplomatic_place'        => [\App\Models\fifthyformatfour\DeplomaticPlace::class,       'دیپلوماتیک ځای',             'deplomatic_place_name','province',   'district'],
        'donelostplace'           => [\App\Models\fifthyformatfour\Donelostplace::class,         'موندل/ورک شوي ځایونه',        'place_name',       'province',      'district'],
        'econemy_project'         => [\App\Models\fifthyformatfour\EconemyProject::class,        'اقتصادي پروژې',               'name',             'province',      'district'],
        'government_place'        => [\App\Models\fifthyformatfour\GovernmentPlace::class,       'حکومتي ځای',                  'government_place_name','province',   'district'],
        'highway'                 => [\App\Models\fifthyformatfour\Highway::class,               'سرک/شاهراه',                  'high_way_name',    'province',      'district'],
        'masjid'                  => [\App\Models\fifthyformatfour\Masjid::class,                'مسجد',                         'masjid_name',      'masjid_province', null],
        'park'                    => [\App\Models\fifthyformatfour\Park::class,                  'پارک',                         'park_name',        'province',      'district'],
        'pump'                    => [\App\Models\fifthyformatfour\Pump::class,                  'پمپ',                          'pump_name',        'province',      'district'],
        'shora'                   => [\App\Models\fifthyformatfour\Shora::class,                 'شورا',                         'shora_name',       'shora_province','shora_district'],
        'super_market'            => [\App\Models\fifthyformatfour\SuperMarket::class,           'سوپرمارکېټ',                   'supermarket_name', 'province',      'district'],
        'university'              => [\App\Models\fifthyformatfour\University::class,            'پوهنتون',                      'uni_name',         'province',      'district'],
        'wakilano'                => [\App\Models\fifthyformatfour\Wakilano::class,              'وکيلانو',                      'name',             'now_province',  'now_district'],
    ];

    public function combined(Request $request)
    {
        [$from, $to, $gran, $zone, $search] = $this->readFilters($request);

        // 1) CARDS (totals per format)
        $cards = [];
        foreach ($this->formats as $key => [$model, $label]) {
            $count = $this->baseQuery(new $model, $from, $to, $zone, $search)->count();
            $cards[] = ['key' => $key, 'label' => $label, 'total' => (int)$count];
        }

        // 2) SERIES (time series per format)
        $series = [];
        foreach ($this->formats as $key => [$model, $label]) {
            $points = $this->timeSeries(new $model, $from, $to, $gran, $zone, $search);
            $series[] = ['key' => $key, 'label' => $label, 'points' => $points];
        }

        // 3) BUCKETS (top resources per format)
        $buckets = [];
        foreach ($this->formats as $key => [$model, $label]) {
            $items = $this->topResources(new $model, 10, $from, $to, $zone, $search);
            $buckets[] = ['key' => $key, 'label' => $label, 'items' => $items];
        }

        // 4) ZONES
        $zones = [];
        foreach ($this->formats as $key => [$model, $label, , , $zoneCol]) {
            $items = $this->groupByColumn(new $model, $zoneCol, $from, $to, $zone, $search);
            if (!empty($items)) $zones[] = ['key' => $key, 'label' => $label, 'items' => $items];
        }

        // 5) PROVINCES
        $provinces = [];
        foreach ($this->formats as $key => [$model, $label, , $provinceCol]) {
            $items = $this->groupByColumn(new $model, $provinceCol, $from, $to, $zone, $search);
            if (!empty($items)) $provinces[] = ['key' => $key, 'label' => $label, 'items' => $items];
        }

        // 6) RECENT (mix latest 20)
        $recent = $this->recentMixed(20, $from, $to, $zone, $search);

        return response()->json(compact('cards','series','buckets','zones','provinces','recent'));
    }

    public function breakdown(Request $request)
    {
        [$from, $to, $gran, $zone, $search] = $this->readFilters($request);
        $format = $request->query('format');
        $field  = $request->query('field');

        if (!$format || !isset($this->formats[$format])) {
            return response()->json(['items' => []]);
        }
        [$model] = $this->formats[$format];

        $items = $this->groupByColumn(new $model, $field, $from, $to, $zone, $search);

        // normalize to {value, count}
        $normalized = array_map(function ($row) use ($field) {
            return [
                'value' => $row[$field] ?? null,
                'count' => (int)($row['count'] ?? 0),
            ];
        }, $items);

        return response()->json(['items' => $normalized]);
    }

    /* ------------------------ Helpers ------------------------ */

    protected function readFilters(Request $r): array
    {
        $from = trim((string)$r->query('from', ''));
        $to   = trim((string)$r->query('to', ''));
        $gran = in_array($r->query('granularity'), ['daily','monthly','yearly']) ? $r->query('granularity') : 'daily';
        $zone = trim((string)$r->query('zone', ''));
        $search = trim((string)$r->query('search', ''));
        return [$from, $to, $gran, $zone, $search];
    }

    protected function baseQuery($model, $from, $to, $zone, $search)
    {
        $q = $model::query();

        // generic date range
        if ($from) $q->whereDate('created_at', '>=', $from);
        if ($to)   $q->whereDate('created_at', '<=', $to);

        // zone (if exists)
        $table = $model->getTable();
        // try common zone columns
        foreach (['zone','now_zone','shora_district','district','now_district'] as $zcol) {
            if ($zone && Schema::hasColumn($table, $zcol)) {
                $q->where($zcol, 'like', "%$zone%");
                break;
            }
        }

        // simple search across a few guessed text columns
        if ($search) {
            $likeCols = $this->guessLikeColumns($table);
            if (!empty($likeCols)) {
                $q->where(function($qq) use ($likeCols, $search) {
                    foreach ($likeCols as $c) $qq->orWhere($c, 'like', "%$search%");
                });
            }
        }

        return $q;
    }

    protected function timeSeries($model, $from, $to, $gran, $zone, $search): array
    {
        $q = $this->baseQuery($model, $from, $to, $zone, $search);

        // MySQL DATE bucket
        $dateExpr = match ($gran) {
            'yearly'  => "DATE_FORMAT(created_at, '%Y')",
            'monthly' => "DATE_FORMAT(created_at, '%Y-%m')",
            default   => "DATE_FORMAT(created_at, '%Y-%m-%d')",
        };

        return $q->selectRaw("$dateExpr AS date, COUNT(*) AS cnt")
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(fn($r) => ['date' => $r->date, 'count' => (int)$r->cnt])
            ->all();
    }

    protected function topResources($model, int $limit, $from, $to, $zone, $search): array
    {
        $q = $this->baseQuery($model, $from, $to, $zone, $search);

        if (!Schema::hasColumn($model->getTable(), 'resource_id')) {
            return [];
        }

        // join resources minimal columns
        $rows = $q->selectRaw('resource_id, COUNT(*) AS cnt')
            ->whereNotNull('resource_id')
            ->groupBy('resource_id')
            ->orderByDesc('cnt')
            ->limit($limit)
            ->get();

        if ($rows->isEmpty()) return [];

        $ids = $rows->pluck('resource_id')->all();
        $res = DB::table('resources')
            ->select('id','name','code_number','phone_number')
            ->whereIn('id', $ids)
            ->get()
            ->keyBy('id');

        return $rows->map(function ($r) use ($res) {
            $R = $res->get($r->resource_id);
            return [
                'resource_id' => $r->resource_id,
                'count'       => (int)$r->cnt,
                'name'        => $R->name       ?? null,
                'code_number' => $R->code_number?? null,
                'phone'       => $R->phone_number ?? null,
            ];
        })->all();
    }

    protected function groupByColumn($model, ?string $col, $from, $to, $zone, $search): array
    {
        if (!$col) return [];
        $table = $model->getTable();
        if (!Schema::hasColumn($table, $col)) return [];

        $q = $this->baseQuery($model, $from, $to, $zone, $search);

        return $q->selectRaw("$col AS `$col`, COUNT(*) AS count")
            ->groupBy($col)
            ->orderByDesc('count')
            ->limit(20)
            ->get()
            ->map(fn($r) => [$col => $r->$col, 'count' => (int)$r->count])
            ->all();
    }

    protected function recentMixed(int $limit, $from, $to, $zone, $search): array
    {
        // pull a few recent per format then merge/sort
        $pack = [];
        foreach ($this->formats as $key => [$model, $label, $titleCol, $provCol, $zoneCol]) {
            $q = $this->baseQuery(new $model, $from, $to, $zone, $search)
                ->orderByDesc('id')
                ->limit(10);

            $rows = $q->get();
            $table = (new $model)->getTable();

            foreach ($rows as $r) {
                $title = $this->valueIfHas($r, $titleCol) ?? ($this->valueIfHas($r,'name') ?? "#{$r->id}");
                $pack[] = [
                    'key'       => $key,
                    'label'     => $label,
                    'title'     => $title,
                    'zone'      => $this->firstExisting($r, [$zoneCol,'zone','district','now_zone','now_district']),
                    'province'  => $this->firstExisting($r, [$provCol,'province','now_province','masjid_province']),
                    'created_at'=> optional($r->created_at)->format('Y-m-d'),
                    'extra'     => $this->compactExtras($r),
                ];
            }
        }

        // sort by created_at desc and cut to $limit
        usort($pack, fn($a,$b) => strcmp($b['created_at'] ?? '', $a['created_at'] ?? ''));
        return array_slice($pack, 0, $limit);
    }

    protected function guessLikeColumns(string $table): array
    {
        // Cheap guess list per table name; add more as needed
        $candidates = [
            'name','title','checkpoint_name','com_name','complex_name','government_place_name',
            'high_way_name','masjid_name','park_name','pump_name','shora_name','supermarket_name',
            'uni_name','place_name','deplomatic_place_name',
            'responsible_name','organ','district','shora_district','village','now_village',
        ];
        return array_values(array_filter($candidates, fn($c) => Schema::hasColumn($table, $c)));
    }

    protected function valueIfHas($model, $col)
    {
        return ($col && isset($model->$col)) ? $model->$col : null;
    }

    protected function firstExisting($model, array $cols)
    {
        foreach ($cols as $c) {
            if ($c && isset($model->$c) && $model->$c !== null && $model->$c !== '') return $model->$c;
        }
        return null;
    }

    protected function compactExtras($r): array
    {
        // Add a few commonly present fields to show in "extra" column
        $fields = ['organ','jorm_type','people_type','type','district','village','office','product_type'];
        $out = [];
        foreach ($fields as $f) {
            if (isset($r->$f) && $r->$f !== null && $r->$f !== '') $out[$f] = $r->$f;
        }
        return $out;
    }
}
