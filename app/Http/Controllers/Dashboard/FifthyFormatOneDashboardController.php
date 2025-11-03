<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Models\fifthyformatone\DamagePlaces;
use App\Models\fifthyformatone\DamageRasanPlaces;
use App\Models\fifthyformatone\LostMujremeen;
use App\Models\fifthyformatone\Luchakano;
use App\Models\fifthyformatone\MashkokHomes;
use App\Models\fifthyformatone\MashkokShops;
use App\Models\fifthyformatone\Mawad;
use App\Models\fifthyformatone\NonResponsablePeople;
use App\Models\fifthyformatone\OldSoldiersHomes;

class FifthyFormatOneDashboardController extends Controller
{
    /** Map key => [model, zone_column] */
    protected array $formats = [
        'damage_places'          => [DamagePlaces::class,          'zone'],
        'damage_rasan_places'    => [DamageRasanPlaces::class,     'zone'],
        'lost_mujremeen'         => [LostMujremeen::class,         'zone'],
        'luchakano'              => [Luchakano::class,             'now_zone'],
        'mashkok_homes'          => [MashkokHomes::class,          'zone'],
        'mashkok_shops'          => [MashkokShops::class,          'zone'],
        'mawad'                  => [Mawad::class,                 'zone'],
        'non_responsable_people' => [NonResponsablePeople::class,  'now_zone'],
        'old_soldiers_homes'     => [OldSoldiersHomes::class,      'zone'],
    ];

    protected ?string $currentKey = null;

    /** Base Query with filters */
    protected function baseQuery(string $model, Request $request)
    {
        $q = $model::query()->with(['resource:id,name,code_number,phone_number']);

        if ($from = $request->get('from')) $q->whereDate('created_at', '>=', Carbon::parse($from));
        if ($to   = $request->get('to'))   $q->whereDate('created_at', '<=', Carbon::parse($to));

        if ($zone = $request->get('zone')) {
            $zoneField = $this->formats[$this->currentKey][1] ?? null;
            if ($zoneField) $q->where($zoneField, $zone);
        }

        if ($search = $request->get('search')) {
            $q->where(function ($qq) use ($search, $model) {
                foreach ($this->guessSearchableColumns($model) as $col) {
                    $qq->orWhere($col, 'like', "%{$search}%");
                }
            });
        }

        return $q;
    }

    /** Searchable fields for each model */
    protected function guessSearchableColumns(string $model): array
    {
        return [
            DamagePlaces::class => ['province','zone','district','village','specific_place','tahdid_type','tahdid_place','masool_name','masool_phone'],
            DamageRasanPlaces::class => ['province','zone','district','village','specific_place','tahdid_type','damage_place'],
            LostMujremeen::class => ['nickname','name','father_name','main_province','now_province','now_district','old_job','jorm','lost_reason','phone_number','zone','district'],
            Luchakano::class => ['nickname','name','father_name','main_province','now_province','now_district','now_specific_place','old_job','job','phone_number'],
            MashkokHomes::class => ['province','zone','district','village','specific_place','home_type','shak_symbols'],
            MashkokShops::class => ['province','zone','district','village','specific_place','market_name','name','father_name','shop_type'],
            Mawad::class => ['mawad_type','nickname','name','father_name','main_province','now_province','province','zone','district','specific_place','supplier'],
            NonResponsablePeople::class => ['nick_name','name','father_name','now_province','now_district','now_specific_place','supplier','phone_number','old_job','now_job'],
            OldSoldiersHomes::class => ['province','zone','district','village','specific_place','name','father_name','old_job','now_activity'],
        ][$model] ?? ['name'];
    }

    /** Label for UI */
    protected function labelFor(string $key): string
    {
        return match ($key) {
            'damage_places'          => 'د تاوان ځایونه',
            'damage_rasan_places'    => 'د تاوان رسېدو ځایونه',
            'lost_mujremeen'         => 'ورک مجرمین',
            'luchakano'              => 'لوچکان',
            'mashkok_homes'          => 'مشکوک کورونه',
            'mashkok_shops'          => 'مشکوک دوکانونه',
            'mawad'                  => 'مواد',
            'non_responsable_people' => 'غير مسؤل کسان',
            'old_soldiers_homes'     => 'د پخوانیو عسکرو کورونه',
            default                  => ucfirst($key),
        };
    }

    /* ------------------------ PUBLIC ENDPOINTS ------------------------ */

    public function summary(Request $request)
    {
        $cards = [];
        foreach ($this->formats as $key => [$model]) {
            $this->currentKey = $key;
            $cards[] = [
                'key' => $key,
                'label' => $this->labelFor($key),
                'total' => (int) $this->baseQuery($model, $request)->count(),
            ];
        }
        return response()->json(['cards' => $cards]);
    }

    public function timeseries(Request $request)
    {
        $gran = $this->normalizeGranularity($request->get('granularity', 'daily'));
        [$select, $format] = match ($gran) {
            'monthly' => ["DATE_FORMAT(created_at, '%Y-%m') as d", 'Y-m'],
            'yearly' => ["DATE_FORMAT(created_at, '%Y') as d", 'Y'],
            default => ["DATE(created_at) as d", 'Y-m-d'],
        };

        $series = [];
        foreach ($this->formats as $key => [$model]) {
            $this->currentKey = $key;
            $rows = $this->baseQuery($model, $request)
                ->selectRaw("$select, COUNT(*) as c")
                ->groupBy('d')
                ->orderBy('d')
                ->get();

            $series[] = [
                'key' => $key,
                'label' => $this->labelFor($key),
                'points' => $rows->map(fn($r) => [
                    'date' => Carbon::parse($r->d)->format($format),
                    'count' => (int) $r->c,
                ])->values(),
            ];
        }

        return response()->json(['series' => $series]);
    }

    public function topResources(Request $request)
    {
        $limit = (int) ($request->get('limit', 10));
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
                'key' => $key,
                'label' => $this->labelFor($key),
                'items' => $rows->map(function ($r) {
                    $res = $r->resource;
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

    public function recent(Request $request)
    {
        $limit = (int) ($request->get('limit', 10));
        $pool = [];

        foreach ($this->formats as $key => [$model]) {
            $this->currentKey = $key;
            $rows = $this->baseQuery($model, $request)->latest()->limit($limit)->get();

            foreach ($rows as $r) {
                $pool[] = [
                    'type' => $key,
                    'id' => $r->id,
                    'name' => $r->name ?? $r->nickname ?? null,
                    'zone' => $r->zone ?? $r->now_zone ?? $r->district ?? null,
                    'created_at' => $r->created_at,
                ];
            }
        }

        usort($pool, fn($a, $b) => strcmp((string)$b['created_at'], (string)$a['created_at']));
        return response()->json(['recent' => array_slice($pool, 0, $limit)]);
    }

    public function combined(Request $request)
    {
        $summary = $this->summary($request)->getData(true);
        $series = $this->timeseries($request)->getData(true);
        $buckets = $this->topResources($request)->getData(true);
        $recent = $this->recent($request)->getData(true);

        return response()->json([
            'cards'   => $summary['cards'] ?? [],
            'series'  => $series['series'] ?? [],
            'buckets' => $buckets['buckets'] ?? [],
            'recent'  => $recent['recent'] ?? [],
        ]);
    }

    protected function normalizeGranularity(string $g): string
    {
        $g = strtolower($g);
        return in_array($g, ['daily','monthly','yearly'], true) ? $g : 'daily';
    }
}
