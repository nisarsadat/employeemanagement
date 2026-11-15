<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

use App\Models\fifthyformatstow\Apartment;
use App\Models\fifthyformatstow\Bank;
use App\Models\fifthyformatstow\CarSalesCompany;
use App\Models\fifthyformatstow\HadoBusStop;
use App\Models\fifthyformatstow\Hosppital;
use App\Models\fifthyformatstow\Parking;
use App\Models\fifthyformatstow\RahnomaMamlat;
use App\Models\fifthyformatstow\School;
use App\Models\fifthyformatstow\TransportCompany;
use App\Models\fifthyformatstow\WorkShop;

class FifthyFormatsTwoDashboardController extends Controller
{
    /** key => [Model, zone_field] (adjust the zone field if any table uses a different one) */
    protected array $formats = [
        'apartment'          => [Apartment::class,         'zone'],
        'bank'               => [Bank::class,              'zone'],
        'car_sales_company'  => [CarSalesCompany::class,   'zone'],
        'hado_bus_stop'      => [HadoBusStop::class,       'zone'],
        'hosppital'          => [Hosppital::class,         'zone'],
        'parking'            => [Parking::class,           'zone'],
        'rahnoma_mamlat'     => [RahnomaMamlat::class,     'zone'],
        'school'             => [School::class,            'zone'],
        'transport_company'  => [TransportCompany::class,  'zone'],
        'workshop'           => [WorkShop::class,          'zone'],
    ];

    protected ?string $currentKey = null;

    /* ============================= CORE ============================= */

    protected function baseQuery(string $model, Request $request)
    {
        $q = $model::query()->with(['resource:id,name,code_number,phone_number']);

        // Dates (only if both the table has created_at and client doesn't force ignore)
        $ignoreDates = (int)$request->get('ignore_dates', 0) === 1;
        $table = (new $model)->getTable();
        $hasCreatedAt = Schema::hasColumn($table, 'created_at');

        if ($hasCreatedAt && !$ignoreDates) {
            if ($from = $request->get('from')) {
                $q->whereDate('created_at', '>=', Carbon::parse($from));
            }
            if ($to = $request->get('to')) {
                $q->whereDate('created_at', '<=', Carbon::parse($to));
            }
        }

        // Zone
        if (($zone = $request->get('zone')) !== null && $zone !== '') {
            $zoneField = $this->formats[$this->currentKey][1] ?? null;
            if ($zoneField) $q->where($zoneField, $zone);
        }

        // Search (broad OR across guessed columns)
        if (($search = $request->get('search')) !== null && $search !== '') {
            $q->where(function ($qq) use ($search, $model) {
                foreach ($this->guessSearchableColumns($model) as $col) {
                    $qq->orWhere($col, 'like', "%{$search}%");
                }
            });
        }

        return $q;
    }

    protected function guessSearchableColumns(string $model): array
    {
        return [
            Apartment::class => [
                'block_name','apartments_quantity','shahrak_name',
                'responsible_name','responsible_f_name','responsible_position','responsible_phone',
                'province','zone','district','village','specific_place','coordinates',
                'guard_quantity','note'
            ],
            Bank::class => [
                'bank_name','bank_type','bank_owner',
                'responsible_name','responsible_f_name','responsible_position','responsible_phone',
                'province','zone','district','village','specific_place','coordinates',
                'guard_quantity','note'
            ],
            CarSalesCompany::class => [
                'car_sales_company_name',
                'responsible_name','responsible_f_name','responsible_position','responsible_phone',
                'province','zone','district','village','specific_place','coordinates','note'
            ],
            HadoBusStop::class => [
                'name','hada_type',
                'responsible_name','responsible_f_name','responsible_position','responsible_phone',
                'province','zone','district','village','specific_place','street_number','coordinates','note'
            ],
            Hosppital::class => [
                'name','specific_past','hospita_type',
                'responsible_name','responsible_f_name','responsible_position','responsible_phone',
                'male_quantity','female_quantity','guard_quantity',
                'province','zone','district','village','specific_place','street_number','coordinates','note'
            ],
            Parking::class => [
                'parking_name','parking_type','parking_type_pe',
                'responsible_name','responsible_f_name','responsible_position','responsible_phone',
                'province','zone','district','village','specific_place','coordinates',
                'daily_cars_parking_quantity','note'
            ],
            RahnomaMamlat::class => [
                'name',
                'responsible_name','responsible_f_name','responsible_position','responsible_phone',
                'province','zone','district','village','specific_place','coordinates','note'
            ],
            School::class => [
                'school_name','school_type',
                'principle_name','principle_f_name','principle_phone',
                'male_teachers_quantity','female_teachers_quantity',
                'male_students_quantity','female_students_quantity',
                'province','zone','district','village','specific_place','street_number','coordinates','note'
            ],
            TransportCompany::class => [
                'transport_company_name','type',
                'responsible_name','responsible_f_name','responsible_position','responsible_phone',
                'province','zone','district','village','specific_place','coordinate','ways','note'
            ],
            WorkShop::class => [
                'workshop_name','workshop_type',
                'responsible_name','responsible_f_name','responsible_position','responsible_phone',
                'province','zone','district','village','specific_place','coordinates',
                'workers_quantity','note'
            ],
        ][$model] ?? ['id'];
    }

    protected function labelFor(string $key): string
    {
        return match ($key) {
            'apartment'          => 'اپارتمانونه',
            'bank'               => 'بانکونه',
            'car_sales_company'  => 'د موټرو د پلور شرکتونه',
            'hado_bus_stop'      => 'هادو بس تمځایونه',
            'hosppital'          => 'روغتونونه',
            'parking'            => 'پارکينګونه',
            'rahnoma_mamlat'     => 'رهنما معاملات',
            'school'             => 'ښوونځي',
            'transport_company'  => 'ترانسپورتي شرکتونه',
            'workshop'           => 'ورکشاپونه',
            default              => ucfirst($key),
        };
    }

    protected function displayName(object $row, string $key): ?string
    {
        return match ($key) {
            'apartment'         => $row->block_name ?? null,
            'bank'              => $row->bank_name ?? null,
            'car_sales_company' => $row->car_sales_company_name ?? $row->name ?? null,
            'hado_bus_stop'     => $row->name ?? null,
            'hosppital'         => $row->name ?? null,
            'parking'           => $row->parking_name ?? null,
            'rahnoma_mamlat'    => $row->name ?? null,
            'school'            => $row->school_name ?? null,
            'transport_company' => $row->transport_company_name ?? null,
            'workshop'          => $row->workshop_name ?? null,
            default             => $row->name ?? null,
        };
    }

    protected function normalizeGranularity(string $g): string
    {
        $g = strtolower($g);
        return in_array($g, ['daily','monthly','yearly'], true) ? $g : 'daily';
    }

    /** One count, or one date-bucket count, across every format. Filters stay on each table. */
    protected function mergedFormatRows(Request $request, ?string $dateExpr = null, bool $requireCreatedAt = false): array
    {
        $union = null;
        foreach ($this->formats as $key => [$model]) {
            if (!preg_match('/^[a-z0-9_]+$/', $key)) {
                continue;
            }
            $table = (new $model)->getTable();
            if ($requireCreatedAt && !Schema::hasColumn($table, 'created_at')) {
                continue;
            }
            $this->currentKey = $key;
            $part = $this->baseQuery($model, $request)->toBase();
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

    /* =========================== ENDPOINTS =========================== */

    public function summary(Request $request)
    {
        $totals = [];
        foreach ($this->mergedFormatRows($request) as $row) {
            $totals[(string) $row->format_key] = (int) $row->total;
        }

        $cards = [];
        foreach ($this->formats as $key => [$model]) {
            $cards[] = [
                'key'   => $key,
                'label' => $this->labelFor($key),
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
            'yearly'  => ["DATE_FORMAT(created_at, '%Y')",    'Y'],
            default   => ["DATE(created_at)",                  'Y-m-d'],
        };

        $grouped = [];
        foreach ($this->mergedFormatRows($request, $dateExpr, true) as $row) {
            $grouped[(string) $row->format_key][] = $row;
        }

        $series = [];
        foreach ($this->formats as $key => [$model]) {
            $table = (new $model)->getTable();
            if (!Schema::hasColumn($table, 'created_at')) {
                $series[] = ['key' => $key, 'label' => $this->labelFor($key), 'points' => []];
                continue;
            }

            $rows = $grouped[$key] ?? [];
            usort($rows, fn ($a, $b) => strcmp((string) $a->d, (string) $b->d));
            $series[] = [
                'key'    => $key,
                'label'  => $this->labelFor($key),
                'points' => array_map(fn ($r) => [
                    'date'  => Carbon::parse($r->d)->format($format),
                    'count' => (int) $r->c,
                ], $rows),
            ];
        }

        return response()->json(['series' => $series]);
    }

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
        $pool  = [];

        foreach ($this->formats as $key => [$model]) {
            $this->currentKey = $key;
            $q = $this->baseQuery($model, $request);

            $table = (new $model)->getTable();
            if (Schema::hasColumn($table, 'created_at')) $q->latest();
            else $q->orderByDesc('id');

            $rows = $q->limit($limit)->get();

            foreach ($rows as $r) {
                $pool[] = [
                    'type'       => $key,
                    'id'         => $r->id,
                    'name'       => $this->displayName($r, $key),
                    'zone'       => $r->zone ?? $r->district ?? null,
                    'created_at' => (string) ($r->created_at ?? ''),
                ];
            }
        }

        usort($pool, fn($a, $b) => strcmp((string)$b['created_at'], (string)$a['created_at']));
        return response()->json(['recent' => array_slice($pool, 0, $limit)]);
    }

    public function combined(Request $request)
    {
        $summary = $this->summary($request)->getData(true);
        $series  = $this->timeseries($request)->getData(true);
        $buckets = $this->topResources($request)->getData(true);
        $recent  = $this->recent($request)->getData(true);

        return response()->json([
            'cards'   => $summary['cards']   ?? [],
            'series'  => $series['series']   ?? [],
            'buckets' => $buckets['buckets'] ?? [],
            'recent'  => $recent['recent']   ?? [],
        ]);
    }

    /* ========================= DIAGNOSTICS ========================= */

    /** quick counts (no filters) + table info */
    public function debug(Request $request)
    {
        $out = [];
        foreach ($this->formats as $key => [$model]) {
            $table = (new $model)->getTable();
            $out[$key] = [
                'model' => $model,
                'table' => $table,
                'total_all' => (int) DB::table($table)->count(),
                'has_created_at' => Schema::hasColumn($table, 'created_at'),
            ];
        }
        return response()->json([
            'received_params' => $request->all(),
            'table_counts' => $out,
        ]);
    }

    /** totals with filters, min/max created_at, and 3 sample rows */
    public function diag(Request $request)
    {
        $result = [];
        foreach ($this->formats as $key => [$model]) {
            $this->currentKey = $key;
            $table = (new $model)->getTable();
            $hasCreatedAt = Schema::hasColumn($table, 'created_at');

            $all = (int) DB::table($table)->count();
            $filtered = (clone $this->baseQuery($model, $request))->count();

            $minCreated = $hasCreatedAt ? DB::table($table)->min('created_at') : null;
            $maxCreated = $hasCreatedAt ? DB::table($table)->max('created_at') : null;

            $sample = DB::table($table)->orderByDesc('id')->limit(3)->get();

            $result[$key] = [
                'model'           => $model,
                'table'           => $table,
                'has_created_at'  => $hasCreatedAt,
                'total_all'       => $all,
                'total_filtered'  => $filtered,
                'min_created_at'  => $minCreated,
                'max_created_at'  => $maxCreated,
                'sample_rows'     => $sample,
            ];
        }

        return response()->json([
            'received_params' => $request->all(),
            'diag' => $result,
        ]);
    }
}
