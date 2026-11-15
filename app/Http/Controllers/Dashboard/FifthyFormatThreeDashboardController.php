<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

// === Your 10 models (fifthyformatsthree) ===
use App\Models\fifthyformatsthree\Badmashano;
use App\Models\fifthyformatsthree\EmaratOffice;
use App\Models\fifthyformatsthree\Hostel;
use App\Models\fifthyformatsthree\Hotel;
use App\Models\fifthyformatsthree\Madrasa;
use App\Models\fifthyformatsthree\MashkokPeople;
use App\Models\fifthyformatsthree\Mujahedino;
use App\Models\fifthyformatsthree\OldJahadi;
use App\Models\fifthyformatsthree\Sarayano;
use App\Models\fifthyformatsthree\Tujaran;

class FifthyFormatThreeDashboardController extends Controller
{
    /**
     * key => [
     *   'model'      => FQCN,
     *   'zone'       => 'zone' | 'now_zone' | null,
     *   'label'      => string(Pashto/Dari),
     *   'searchable' => [...],
     *   'province'   => 'province' | 'now_province' | null, // for topProvinces()
     *   'breakdowns' => ['colA','colB', ...],               // for /breakdown
     * ]
     */
    protected array $formats = [
        'badmashano' => [
            'model'      => Badmashano::class,
            'zone'       => 'now_zone',
            'label'      => 'بدماشانو',
            'searchable' => ['nickname','name','father_name','now_province','now_zone','now_district','old_job','job','supplier','phone_number','jorm_type','history_jorm','note'],
            'province'   => 'now_province',
            'breakdowns' => ['jorm_type','job','old_job','supplier'],
        ],
        'emarat_office' => [
            'model'      => EmaratOffice::class,
            'zone'       => 'zone',
            'label'      => 'دفتر امارت',
            'searchable' => ['province','zone','district','village','specific_place','home_type','responsible_office','tahdid_mutawajeh','note'],
            'province'   => 'province',
            'breakdowns' => ['home_type','responsible_office','tahdid_mutawajeh'],
        ],
        'hostel' => [
            'model'      => Hostel::class,
            'zone'       => 'zone',
            'label'      => 'هاستل',
            'searchable' => ['hostel_name','hostel_type','people_type','responsible_name','responsible_f_name','responsible_position','responsible_phone','province','zone','district','village','specific_place','note'],
            'province'   => 'province',
            'breakdowns' => ['hostel_type','people_type'],
        ],
        'hotel' => [
            'model'      => Hotel::class,
            'zone'       => 'zone',
            'label'      => 'هوټل',
            'searchable' => ['hotel_name','type','responsible_name','responsible_f_name','responsible_position','responsible_phone','province','zone','district','village','specific_place','employee_quantity','note'],
            'province'   => 'province',
            'breakdowns' => ['type'],
        ],
        'madrasa' => [
            'model'      => Madrasa::class,
            'zone'       => 'zone',
            'label'      => 'مدرسه',
            'searchable' => ['name','madrasa_type','responsible_name','responsible_f_name','responsible_position','responsible_phone','responsible_main_province','tarafdari_type_responsible','mazhab','province','zone','district','village','specific_place','note'],
            'province'   => 'province',
            'breakdowns' => ['madrasa_type','mazhab','tarafdari_type_responsible'],
        ],
        'mashkok_people' => [
            'model'      => MashkokPeople::class,
            'zone'       => 'now_zone',
            'label'      => 'مشکوک خلک',
            'searchable' => ['name','father_name','now_province','now_zone','now_districts','now_specific_place','old_job','now_job','phone_number','mushkokyat_points','note'],
            'province'   => 'now_province',
            'breakdowns' => ['now_job','old_job'],
        ],
        'mujahedino' => [
            'model'      => Mujahedino::class,
            'zone'       => 'now_zone',
            'label'      => 'مجاهدینو',
            'searchable' => ['nickname','name','father_name','now_province','now_zone','now_district','organ','duty_type','now_job','phone_number','note'],
            'province'   => 'now_province',
            'breakdowns' => ['organ','duty_type','now_job'],
        ],
        'old_jahadi' => [
            'model'      => OldJahadi::class,
            'zone'       => 'now_zone',
            'label'      => 'پخواني جهادي',
            'searchable' => ['nick_name','name','father_name','now_province','now_zone','now_district','old_job','now_job','employee_quantity','supplier','tanzim','note'],
            'province'   => 'now_province',
            'breakdowns' => ['tanzim','now_job','old_job','supplier'],
        ],
        'sarayano' => [
            'model'      => Sarayano::class,
            'zone'       => 'zone',
            'label'      => 'سرایانو',
            'searchable' => ['saray_name','saray_type','responsible_name','responsible_f_name','responsible_position','responsible_phone','province','zone','district','village','specific_place','employee_quantity','note'],
            'province'   => 'province',
            'breakdowns' => ['saray_type'],
        ],
        'tujaran' => [
            'model'      => Tujaran::class,
            'zone'       => 'now_zone',
            'label'      => 'تجاران',
            'searchable' => ['name','father_name','now_province','now_zone','now_district','organ','old_job','now_job','phone_number','people_type','note'],
            'province'   => 'now_province',
            'breakdowns' => ['organ','now_job','people_type'],
        ],
    ];

    protected function baseQuery(array $cfg, Request $request)
    {
        /** @var class-string $model */
        $model = $cfg['model'];
        $zoneField = $cfg['zone'] ?? null;
        $searchable = $cfg['searchable'] ?? ['name'];

        $q = $model::query()->with(['resource:id,name,code_number,phone_number']);

        if ($from = $request->get('from')) $q->whereDate('created_at', '>=', Carbon::parse($from));
        if ($to   = $request->get('to'))   $q->whereDate('created_at', '<=', Carbon::parse($to));

        if ($zone = $request->get('zone')) {
            if ($zoneField) $q->where($zoneField, $zone);
        }

        if ($search = $request->get('search')) {
            $q->where(function ($qq) use ($search, $searchable) {
                foreach ($searchable as $col) {
                    $qq->orWhere($col, 'like', "%{$search}%");
                }
            });
        }

        return $q;
    }

    protected function normalizeGranularity(string $g): string
    {
        $g = strtolower($g);
        return in_array($g, ['daily','monthly','yearly'], true) ? $g : 'daily';
    }

    /** One count, or one date-bucket count, across every format. Filters stay on each table. */
    protected function mergedFormatRows(Request $request, ?string $dateExpr = null): array
    {
        $union = null;
        foreach ($this->formats as $key => $cfg) {
            if (!preg_match('/^[a-z0-9_]+$/', $key)) {
                continue;
            }
            $part = $this->baseQuery($cfg, $request)->toBase();
            $part->columns = [];
            if ($dateExpr === null) {
                $part->selectRaw("'{$key}' as format_key, COUNT(*) as total");
            } else {
                $part->selectRaw("'{$key}' as format_key, {$dateExpr} as d, COUNT(*) as c");
                $part->groupBy('d');
            }
            $union = $union ? $union->unionAll($part) : $part;
        }

        return $union ? $union->get()->all() : [];
    }

    // --------------------------- ENDPOINTS ---------------------------

    public function summary(Request $request)
    {
        $totals = [];
        foreach ($this->mergedFormatRows($request) as $row) {
            $totals[(string) $row->format_key] = (int) $row->total;
        }

        $cards = [];
        foreach ($this->formats as $key => $cfg) {
            $cards[] = [
                'key'   => $key,
                'label' => $cfg['label'] ?? ucfirst($key),
                'total' => $totals[$key] ?? 0,
            ];
        }
        return response()->json(['cards' => $cards]);
    }

    public function timeseries(Request $request)
    {
        $gran = $this->normalizeGranularity($request->get('granularity', 'daily'));
        [$dateExpr, $format] = match ($gran) {
            'monthly' => ["DATE_FORMAT(created_at, '%Y-%m')", 'Y-m'],
            'yearly'  => ["DATE_FORMAT(created_at, '%Y')", 'Y'],
            default   => ["DATE(created_at)", 'Y-m-d'],
        };

        $grouped = [];
        foreach ($this->mergedFormatRows($request, $dateExpr) as $row) {
            $grouped[(string) $row->format_key][] = $row;
        }

        $series = [];
        foreach ($this->formats as $key => $cfg) {
            $rows = $grouped[$key] ?? [];
            usort($rows, fn ($a, $b) => strcmp((string) $a->d, (string) $b->d));
            $series[] = [
                'key'    => $key,
                'label'  => $cfg['label'] ?? ucfirst($key),
                'points' => array_map(fn ($r) => [
                    'date'  => Carbon::parse($r->d)->format($format),
                    'count' => (int) $r->c,
                ], $rows),
            ];
        }

        return response()->json(['series' => $series]);
    }

    /** Top reporters/resources, optionally limited to one format via ?format=key */
    public function topResources(Request $request)
    {
        $limit  = (int) ($request->get('limit', 10));
        $format = $request->get('format');

        $resourceStats = [];
        $iterate = ($format && isset($this->formats[$format]))
            ? [$format => $this->formats[$format]]
            : $this->formats;

        foreach ($iterate as $key => $cfg) {
            $label = $cfg['label'] ?? ucfirst($key);
            $rows = $this->baseQuery($cfg, $request)
                ->selectRaw('resource_id, COUNT(*) as c')
                ->whereNotNull('resource_id')
                ->groupBy('resource_id')
                ->orderByDesc('c')
                ->with('resource:id,name,code_number,phone_number')
                ->limit($limit)
                ->get();

            $resourceStats[] = [
                'key'   => $key,
                'label' => $label,
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

    /** NEW: Top zones per format */
    public function topZones(Request $request)
    {
        $limit  = (int) ($request->get('limit', 10));
        $format = $request->get('format');

        $out = [];
        $iterate = ($format && isset($this->formats[$format]))
            ? [$format => $this->formats[$format]]
            : $this->formats;

        foreach ($iterate as $key => $cfg) {
            $zoneField = $cfg['zone'] ?? null;
            if (!$zoneField) continue;

            $label = $cfg['label'] ?? ucfirst($key);

            $rows = $this->baseQuery($cfg, $request)
                ->selectRaw("$zoneField as z, COUNT(*) as c")
                ->whereNotNull($zoneField)
                ->groupBy('z')
                ->orderByDesc('c')
                ->limit($limit)
                ->get();

            $out[] = [
                'key'   => $key,
                'label' => $label,
                'items' => $rows->map(fn($r) => ['zone' => $r->z, 'count' => (int) $r->c])->values(),
            ];
        }

        return response()->json(['zones' => $out]);
    }

    /** NEW: Top provinces per format */
    public function topProvinces(Request $request)
    {
        $limit  = (int) ($request->get('limit', 10));
        $format = $request->get('format');

        $out = [];
        $iterate = ($format && isset($this->formats[$format]))
            ? [$format => $this->formats[$format]]
            : $this->formats;

        foreach ($iterate as $key => $cfg) {
            $provField = $cfg['province'] ?? null;
            if (!$provField) continue;

            $label = $cfg['label'] ?? ucfirst($key);

            $rows = $this->baseQuery($cfg, $request)
                ->selectRaw("$provField as p, COUNT(*) as c")
                ->whereNotNull($provField)
                ->groupBy('p')
                ->orderByDesc('c')
                ->limit($limit)
                ->get();

            $out[] = [
                'key'   => $key,
                'label' => $label,
                'items' => $rows->map(fn($r) => ['province' => $r->p, 'count' => (int) $r->c])->values(),
            ];
        }

        return response()->json(['provinces' => $out]);
    }

    /**
     * NEW: Generic breakdown endpoint
     * /breakdown?format=badmashano&field=jorm_type
     */
    public function breakdown(Request $request)
    {
        $format = $request->get('format');
        $field  = $request->get('field');

        if (!$format || !isset($this->formats[$format])) {
            return response()->json(['message' => 'Invalid format'], 422);
        }
        $cfg = $this->formats[$format];
        if (!$field || !in_array($field, $cfg['breakdowns'] ?? [], true)) {
            return response()->json(['message' => 'Invalid field'], 422);
        }

        $rows = $this->baseQuery($cfg, $request)
            ->selectRaw("$field as v, COUNT(*) as c")
            ->groupBy('v')
            ->orderByDesc('c')
            ->get();

        return response()->json([
            'key'   => $format,
            'field' => $field,
            'items' => $rows->map(fn($r) => ['value' => $r->v, 'count' => (int) $r->c])->values(),
        ]);
    }

    /** Expanded recent list */
    public function recent(Request $request)
    {
        $limit = (int) ($request->get('limit', 12));
        $pool  = [];

        foreach ($this->formats as $key => $cfg) {
            $label = $cfg['label'] ?? ucfirst($key);
            $rows = $this->baseQuery($cfg, $request)->latest()->limit($limit)->get();

            foreach ($rows as $r) {
                $pool[] = [
                    'type'       => $key,
                    'label'      => $label,
                    'id'         => $r->id,
                    // try best-guess name/title per model
                    'title'      => $r->name ?? $r->nickname ?? $r->hotel_name ?? $r->hostel_name ?? $r->saray_name ?? null,
                    'zone'       => $r->zone ?? $r->now_zone ?? $r->district ?? null,
                    'province'   => $r->province ?? $r->now_province ?? null,
                    'extra'      => [
                        // show a few meaningful fields if present:
                        'people_type' => $r->people_type ?? null,
                        'jorm_type'   => $r->jorm_type ?? null,
                        'organ'       => $r->organ ?? null,
                        'type'        => $r->type ?? $r->hostel_type ?? $r->saray_type ?? null,
                    ],
                    'created_at' => $r->created_at,
                ];
            }
        }

        usort($pool, fn($a, $b) => strcmp((string)$b['created_at'], (string)$a['created_at']));
        return response()->json(['recent' => array_slice($pool, 0, $limit)]);
    }

    /** One-shot aggregator like your FF1 “combined” but richer */
    public function combined(Request $request)
    {
        $summary   = $this->summary($request)->getData(true);
        $series    = $this->timeseries($request)->getData(true);
        $resources = $this->topResources($request)->getData(true);
        $zones     = $this->topZones($request)->getData(true);
        $provinces = $this->topProvinces($request)->getData(true);
        $recent    = $this->recent($request)->getData(true);

        return response()->json([
            'cards'     => $summary['cards']     ?? [],
            'series'    => $series['series']     ?? [],
            'buckets'   => $resources['buckets'] ?? [],
            'zones'     => $zones['zones']       ?? [],
            'provinces' => $provinces['provinces'] ?? [],
            'recent'    => $recent['recent']     ?? [],
        ]);
    }
}
