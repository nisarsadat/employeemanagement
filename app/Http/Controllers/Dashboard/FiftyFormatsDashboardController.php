<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

// 10 formats
use App\Models\fifthyFormats\Captive;
use App\Models\fifthyFormats\Clubs;
use App\Models\fifthyFormats\EntryExit;
use App\Models\fifthyFormats\Fasad;
use App\Models\fifthyFormats\Heist;
use App\Models\fifthyFormats\Mahajerat;
use App\Models\fifthyFormats\Mulkano;
use App\Models\fifthyFormats\SaeenFasad;
use App\Models\fifthyFormats\Sarafyano;
use App\Models\fifthyFormats\Soldier;

class FiftyFormatsDashboardController extends Controller
{
    /** Which key maps to which model and which "zone-like" column we should filter on */
    protected array $formats = [
        'captive'     => [Captive::class,     'now_zone'],
        'clubs'       => [Clubs::class,       'zone'],
        'entry_exit'  => [EntryExit::class,   'zone'],
        'fasad'       => [Fasad::class,       'activity_zone'],
        'heist'       => [Heist::class,       'now_zone'],
        'mahajerat'   => [Mahajerat::class,   'now_zone'],
        'mulkano'     => [Mulkano::class,     'now_zone'],
        'saeen_fasad' => [SaeenFasad::class,  'activity_place'],
        'sarafyano'   => [Sarafyano::class,   'zone'],
        'soldier'     => [Soldier::class,     'now_zone'],
    ];

    /** internal: which key are we filtering right now (for zone column lookup) */
    protected ?string $currentKey = null;

    /** Build a base filtered query */
    protected function baseQuery(string $model, Request $request)
    {
        /** @var \Illuminate\Database\Eloquent\Builder $q */
        $q = $model::query()->with([
            'resource' => fn($qr) => $qr->select('id','name','code_number','phone_number')
        ]);

        // dates
        if ($from = $request->get('from')) $q->whereDate('created_at', '>=', Carbon::parse($from));
        if ($to   = $request->get('to'))   $q->whereDate('created_at', '<=', Carbon::parse($to));

        // zone filter (if column exists)
        if ($zone = $request->get('zone')) {
            $zoneField = $this->formats[$this->currentKey][1] ?? null;
            if ($zoneField && \Schema::hasColumn((new $model)->getTable(), $zoneField)) {
                $q->where($zoneField, $zone);
            }
        }

        // simple search
        if ($search = $request->get('search')) {
            $q->where(function ($qq) use ($search, $model) {
                foreach ($this->guessSearchableColumns($model) as $col) {
                    $qq->orWhere($col, 'like', "%{$search}%");
                }
            });
        }

        return $q;
    }

    /** Guess searchable columns per model */
    protected function guessSearchableColumns(string $model): array
    {
        return [
            Captive::class     => ['name','nick_name','father_name','phone_number','now_province','now_district','specific_place'],
            Clubs::class       => ['name','club_name','club_type','father_name','phone_number','province','district','specific_place'],
            EntryExit::class   => ['name','entry_way','where_went','where_come','province','district','specific_place'],
            Fasad::class       => ['name','nick_name','father_name','activity_zone','phone_number','specific_place'],
            Heist::class       => ['name','father_name','now_job','old_job','phone_number','specific_place'],
            Mahajerat::class   => ['name','father_name','country','phone_number','specific_place'],
            Mulkano::class     => ['name','father_name','new_job','phone_number','specific_place','guzar'],
            SaeenFasad::class  => ['name','nick_name','activity_place','phone_number','specific_place'],
            Sarafyano::class   => ['name','sarafi_name','market_name','phone_number','province','district'],
            Soldier::class     => ['name','father_name','now_job','phone_number','specific_place'],
        ][$model] ?? ['name'];
    }

    /** Mini row for unified recent table */
    protected function packMini($row, string $type): array
    {
        $res = $row->relationLoaded('resource') ? $row->getRelation('resource') : null;

        $zoneGuess = $row->zone
            ?? $row->now_zone
            ?? $row->activity_zone
            ?? $row->activity_place
            ?? $row->district
            ?? $row->now_district
            ?? null;

        return [
            'type'           => $type,
            'id'             => $row->id,
            'name'           => $row->name,
            'created_at'     => $row->created_at,
            'zone'           => $zoneGuess,
            'resource_id'    => $row->resource_id,
            'resource_code'  => $res?->code_number,
            'resource_phone' => $res?->phone_number,
        ];
    }

    /* ─────────────────────  PUBLIC ENDPOINTS  ───────────────────── */

    /** 1) Cards: total counts per format (filtered) */
    public function summary(Request $request)
    {
        $cards = [];
        foreach ($this->formats as $key => [$model]) {
            $this->currentKey = $key;
            $count = (int) $this->baseQuery($model, $request)->count();
            $cards[] = ['key'=>$key, 'label'=>$this->labelFor($key), 'total'=>$count];
        }
        return response()->json(['cards' => $cards]);
    }

    /** 2) Time-series with granularity: daily | monthly | yearly */
    public function timeseries(Request $request)
    {
        $gran = $this->normalizeGranularity($request->get('granularity', 'daily'));

        // SQL date format per granularity
        [$select, $phpFormat] = match ($gran) {
            'monthly' => ["DATE_FORMAT(created_at, '%Y-%m') as d", 'Y-m'],
            'yearly'  => ["DATE_FORMAT(created_at, '%Y') as d",     'Y'],
            default   => ["DATE(created_at) as d",                   'Y-m-d'],
        };

        $series = [];
        foreach ($this->formats as $key => [$model]) {
            $this->currentKey = $key;

            $rows = $this->baseQuery($model, $request)
                ->selectRaw("$select, COUNT(*) as c")
                ->groupBy('d')
                ->orderBy('d')
                ->get();

            $points = $rows->map(fn($r) => [
                'date'  => Carbon::parse($r->d)->format($phpFormat),
                'count' => (int) $r->c,
            ])->values();

            $series[] = [
                'key'    => $key,
                'label'  => $this->labelFor($key),
                'points' => $points,
            ];
        }

        return response()->json(['series' => $series]);
    }

    /** 3) Top resources, optionally per one format key */
    public function topResources(Request $request)
    {
        $limit  = (int) ($request->get('limit', 10));
        $format = $request->get('format');

        $resourceStats = [];
        $iterate = $format && isset($this->formats[$format])
            ? [$format => $this->formats[$format]]
            : $this->formats;

        foreach ($iterate as $key => [$model]) {
            $this->currentKey = $key;

            $rows = $this->baseQuery($model, $request)
                ->selectRaw('resource_id, COUNT(*) as c')
                ->whereNotNull('resource_id')
                ->groupBy('resource_id')
                ->orderByDesc('c')
                ->with('resource:id,name,code_number,phone_number')
                ->limit($limit)
                ->get();

            $resourceStats[] = [
                'key'   => $key,
                'label' => $this->labelFor($key),
                'items' => $rows->map(function ($r) {
                    $res = $r->relationLoaded('resource') ? $r->getRelation('resource') : null;
                    return [
                        'resource_id'  => $r->resource_id,
                        'count'        => (int) $r->c,
                        'name'         => $res?->name,
                        'code_number'  => $res?->code_number,
                        'phone_number' => $res?->phone_number,
                    ];
                })->values(),
            ];
        }

        return response()->json(['buckets' => $resourceStats]);
    }

    /** 4) Recent: last N across all formats, merged and sorted */
    public function recent(Request $request)
    {
        $limit = (int) ($request->get('limit', 10));
        $pool  = [];

        foreach ($this->formats as $key => [$model]) {
            $this->currentKey = $key;
            $rows = $this->baseQuery($model, $request)->latest()->limit($limit)->get();
            foreach ($rows as $r) $pool[] = $this->packMini($r, $key);
        }

        usort($pool, fn($a,$b) => strcmp((string)$b['created_at'], (string)$a['created_at']));
        $pool = array_slice($pool, 0, $limit);

        return response()->json(['items' => $pool]);
    }

    /** 5) Combined: everything in one response (for fewer round-trips) */
    public function combined(Request $request)
    {
        // cards
        $cards = [];
        foreach ($this->formats as $key => [$model]) {
            $this->currentKey = $key;
            $cards[] = ['key'=>$key, 'label'=>$this->labelFor($key), 'total'=>(int)$this->baseQuery($model, $request)->count()];
        }

        // series
        $seriesResp = $this->timeseries($request)->getData(true);

        // top resources (per-format)
        $bucketsResp = $this->topResources($request)->getData(true);

        // recent
        $recentResp = $this->recent($request)->getData(true);

        return response()->json([
            'cards'   => $cards,
            'series'  => $seriesResp['series']   ?? [],
            'buckets' => $bucketsResp['buckets'] ?? [],
            'recent'  => $recentResp['items']    ?? [],
        ]);
    }

    /* ─────────────────────  helpers  ───────────────────── */

    protected function normalizeGranularity(string $g): string
    {
        $g = strtolower($g);
        return in_array($g, ['daily','monthly','yearly'], true) ? $g : 'daily';
    }

    protected function labelFor(string $key): string
    {
        return match ($key) {
            'captive'      => 'وسپارل شوي بندیان',
            'clubs'        => 'کلپونه',
            'entry_exit'   => 'داخل/خروج',
            'fasad'        => 'فساد',
            'heist'        => 'د غلا پېښې',
            'mahajerat'    => 'مهاجرتونه',
            'mulkano'      => 'ملکانو',
            'saeen_fasad'  => 'ساین فساد',
            'sarafyano'    => 'صرافیانو',
            'soldier'      => 'سرتیري',
            default        => ucfirst($key),
        };
    }
}
