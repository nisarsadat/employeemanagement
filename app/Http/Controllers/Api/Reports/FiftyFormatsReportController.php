<?php

namespace App\Http\Controllers\Api\Reports;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class FiftyFormatsReportController extends Controller
{
    /**
     * Config per format:
     *  - key: what frontend will send in "formats" (comma list)
     *  - table: DB table name
     *  - province_column / zone_column: for province_zone grouping
     *  - created_column: for date filters
     *  - resource_column: for resource grouping
     *
     * You can later update province/zone columns per table if they differ,
     * but we protect with Schema::hasColumn so it won't crash.
     */
    protected array $formatConfig = [

        // ───── Main Fifty Formats (10) ─────
        'fasads' => [
            'table'           => 'fasads',
            'province_column' => 'now_province', // from your Fasad model
            'zone_column'     => 'now_zone',
            'created_column'  => 'created_at',
            'resource_column' => 'resource_id',
        ],
        'saeen_fasads' => [
            'table'           => 'saeen_fasads',
            'province_column' => 'province',
            'zone_column'     => 'zone',
            'created_column'  => 'created_at',
            'resource_column' => 'resource_id',
        ],
        'heists' => [
            'table'           => 'heists',
            'province_column' => 'province',
            'zone_column'     => 'zone',
            'created_column'  => 'created_at',
            'resource_column' => 'resource_id',
        ],
        'captives' => [
            'table'           => 'captives',
            'province_column' => 'province',
            'zone_column'     => 'zone',
            'created_column'  => 'created_at',
            'resource_column' => 'resource_id',
        ],
        'mulkanos' => [
            'table'           => 'mulkanos',
            'province_column' => 'now_province', // from your migration
            'zone_column'     => 'now_zone',
            'created_column'  => 'created_at',
            'resource_column' => 'resource_id',
        ],
        'entry_exits' => [
            'table'           => 'entry_exits',
            'province_column' => 'province',
            'zone_column'     => 'zone',
            'created_column'  => 'created_at',
            'resource_column' => 'resource_id',
        ],
        'sarafyanos' => [
            'table'           => 'sarafyanos',
            'province_column' => 'province',
            'zone_column'     => 'zone',
            'created_column'  => 'created_at',
            'resource_column' => 'resource_id',
        ],
        'clubs' => [
            'table'           => 'clubs',
            'province_column' => 'province',
            'zone_column'     => 'zone',
            'created_column'  => 'created_at',
            'resource_column' => 'resource_id',
        ],
        'mahajerats' => [
            'table'           => 'mahajerats',
            'province_column' => 'province',
            'zone_column'     => 'zone',
            'created_column'  => 'created_at',
            'resource_column' => 'resource_id',
        ],
        'soldiers' => [
            'table'           => 'soldiers',
            'province_column' => 'province',
            'zone_column'     => 'zone',
            'created_column'  => 'created_at',
            'resource_column' => 'resource_id',
        ],

        // ───── Fifty Format One (10) ─────
        'mawads' => [
            'table'           => 'mawads',
            'province_column' => 'province',
            'zone_column'     => 'zone',
            'created_column'  => 'created_at',
            'resource_column' => 'resource_id',
        ],
        'lost_mujremeens' => [
            'table'           => 'lost_mujremeens',
            'province_column' => 'province',
            'zone_column'     => 'zone',
            'created_column'  => 'created_at',
            'resource_column' => 'resource_id',
        ],
        'old_soldiers_homes' => [
            'table'           => 'old_soldiers_homes',
            'province_column' => 'province',
            'zone_column'     => 'zone',
            'created_column'  => 'created_at',
            'resource_column' => 'resource_id',
        ],
        'robbers' => [
            'table'           => 'robbers',
            'province_column' => 'province',
            'zone_column'     => 'zone',
            'created_column'  => 'created_at',
            'resource_column' => 'resource_id',
        ],
        'non_responsable_people' => [
            'table'           => 'non_responsable_people',
            'province_column' => 'province',
            'zone_column'     => 'zone',
            'created_column'  => 'created_at',
            'resource_column' => 'resource_id',
        ],
        'mashkok_homes' => [
            'table'           => 'mashkok_homes',
            'province_column' => 'province',
            'zone_column'     => 'zone',
            'created_column'  => 'created_at',
            'resource_column' => 'resource_id',
        ],
        'damage_places' => [
            'table'           => 'damage_places',
            'province_column' => 'province',
            'zone_column'     => 'zone',
            'created_column'  => 'created_at',
            'resource_column' => 'resource_id',
        ],
        'damage_rasan_places' => [
            'table'           => 'damage_rasan_places',
            'province_column' => 'province',
            'zone_column'     => 'zone',
            'created_column'  => 'created_at',
            'resource_column' => 'resource_id',
        ],
        'mashkok_shops' => [
            'table'           => 'mashkok_shops',
            'province_column' => 'province',
            'zone_column'     => 'zone',
            'created_column'  => 'created_at',
            'resource_column' => 'resource_id',
        ],
        'luchakanos' => [
            'table'           => 'luchakanos',
            'province_column' => 'province',
            'zone_column'     => 'zone',
            'created_column'  => 'created_at',
            'resource_column' => 'resource_id',
        ],

        // ───── Fifty Format Two (10) ─────
        'schools' => [
            'table'           => 'schools',
            'province_column' => 'province',
            'zone_column'     => 'zone',
            'created_column'  => 'created_at',
            'resource_column' => 'resource_id',
        ],
        'hado_bus_stops' => [
            'table'           => 'hado_bus_stops',
            'province_column' => 'province',
            'zone_column'     => 'zone',
            'created_column'  => 'created_at',
            'resource_column' => 'resource_id',
        ],
        'transport_companies' => [
            'table'           => 'transport_companies',
            'province_column' => 'province',
            'zone_column'     => 'zone',
            'created_column'  => 'created_at',
            'resource_column' => 'resource_id',
        ],
        'car_sales_companies' => [
            'table'           => 'car_sales_companies',
            'province_column' => 'province',
            'zone_column'     => 'zone',
            'created_column'  => 'created_at',
            'resource_column' => 'resource_id',
        ],
        'parkings' => [
            'table'           => 'parkings',
            'province_column' => 'province',
            'zone_column'     => 'zone',
            'created_column'  => 'created_at',
            'resource_column' => 'resource_id',
        ],
        'banks' => [
            'table'           => 'banks',
            'province_column' => 'province',
            'zone_column'     => 'zone',
            'created_column'  => 'created_at',
            'resource_column' => 'resource_id',
        ],
        'apartments' => [
            'table'           => 'apartments',
            'province_column' => 'province', // from your migration
            'zone_column'     => 'zone',
            'created_column'  => 'created_at',
            'resource_column' => 'resource_id',
        ],
        'rahnoma_mamlat' => [
            'table'           => 'rahnoma_mamlat',
            'province_column' => 'province',
            'zone_column'     => 'zone',
            'created_column'  => 'created_at',
            'resource_column' => 'resource_id',
        ],
        'hosppitals' => [
            'table'           => 'hosppitals',
            'province_column' => 'province',
            'zone_column'     => 'zone',
            'created_column'  => 'created_at',
            'resource_column' => 'resource_id',
        ],
        'work_shops' => [
            'table'           => 'work_shops',
            'province_column' => 'province',
            'zone_column'     => 'zone',
            'created_column'  => 'created_at',
            'resource_column' => 'resource_id',
        ],

        // ───── Fifty Format Three (10) ─────
        'badmashanos' => [
            'table'           => 'badmashanos',
            'province_column' => 'province',
            'zone_column'     => 'zone',
            'created_column'  => 'created_at',
            'resource_column' => 'resource_id',
        ],
        'emarat_offices' => [
            'table'           => 'emarat_offices',
            'province_column' => 'province',
            'zone_column'     => 'zone',
            'created_column'  => 'created_at',
            'resource_column' => 'resource_id',
        ],
        'hostels' => [
            'table'           => 'hostels',
            'province_column' => 'province',
            'zone_column'     => 'zone',
            'created_column'  => 'created_at',
            'resource_column' => 'resource_id',
        ],
        'hotels' => [
            'table'           => 'hotels',
            'province_column' => 'province',
            'zone_column'     => 'zone',
            'created_column'  => 'created_at',
            'resource_column' => 'resource_id',
        ],
        'madrasas' => [
            'table'           => 'madrasas',
            'province_column' => 'province',
            'zone_column'     => 'zone',
            'created_column'  => 'created_at',
            'resource_column' => 'resource_id',
        ],
        'mashkok_people' => [
            'table'           => 'mashkok_people',
            'province_column' => 'province',
            'zone_column'     => 'zone',
            'created_column'  => 'created_at',
            'resource_column' => 'resource_id',
        ],
        'mujahedinos' => [
            'table'           => 'mujahedinos',
            'province_column' => 'province',
            'zone_column'     => 'zone',
            'created_column'  => 'created_at',
            'resource_column' => 'resource_id',
        ],
        'old_jahadis' => [
            'table'           => 'old_jahadis',
            'province_column' => 'province',
            'zone_column'     => 'zone',
            'created_column'  => 'created_at',
            'resource_column' => 'resource_id',
        ],
        'sarayanos' => [
            'table'           => 'sarayanos',
            'province_column' => 'province',
            'zone_column'     => 'zone',
            'created_column'  => 'created_at',
            'resource_column' => 'resource_id',
        ],
        'tujarans' => [
            'table'           => 'tujarans',
            'province_column' => 'province',
            'zone_column'     => 'zone',
            'created_column'  => 'created_at',
            'resource_column' => 'resource_id',
        ],

        // ───── Fifty Format Four (16) ─────
        'checkpoints' => [
            'table'           => 'checkpoints',
            'province_column' => 'province',
            'zone_column'     => 'zone',
            'created_column'  => 'created_at',
            'resource_column' => 'resource_id',
        ],
        'masjids' => [
            'table'           => 'masjids',
            'province_column' => 'province',
            'zone_column'     => 'zone',
            'created_column'  => 'created_at',
            'resource_column' => 'resource_id',
        ],
        'highways' => [
            'table'           => 'highways',
            'province_column' => 'province', // from your migration
            'zone_column'     => 'zone',
            'created_column'  => 'created_at',
            'resource_column' => 'resource_id',
        ],
        'companies' => [
            'table'           => 'companies',
            'province_column' => 'province',
            'zone_column'     => 'zone',
            'created_column'  => 'created_at',
            'resource_column' => 'resource_id',
        ],
        'done_lost_places' => [
            'table'           => 'done_lost_places',
            'province_column' => 'province',
            'zone_column'     => 'zone',
            'created_column'  => 'created_at',
            'resource_column' => 'resource_id',
        ],
        'econemy_projects' => [
            'table'           => 'econemy_projects',
            'province_column' => 'province',
            'zone_column'     => 'zone',
            'created_column'  => 'created_at',
            'resource_column' => 'resource_id',
        ],
        'criminal_capture_places' => [
            'table'           => 'criminal_capture_places',
            'province_column' => 'province',
            'zone_column'     => 'zone',
            'created_column'  => 'created_at',
            'resource_column' => 'resource_id',
        ],
        'pumps' => [
            'table'           => 'pumps',
            'province_column' => 'province',
            'zone_column'     => 'zone',
            'created_column'  => 'created_at',
            'resource_column' => 'resource_id',
        ],
        'shoras' => [
            'table'           => 'shoras',
            'province_column' => 'province',
            'zone_column'     => 'zone',
            'created_column'  => 'created_at',
            'resource_column' => 'resource_id',
        ],
        'government_places' => [
            'table'           => 'government_places',
            'province_column' => 'province',
            'zone_column'     => 'zone',
            'created_column'  => 'created_at',
            'resource_column' => 'resource_id',
        ],
        'super_markets' => [
            'table'           => 'super_markets',
            'province_column' => 'province',
            'zone_column'     => 'zone',
            'created_column'  => 'created_at',
            'resource_column' => 'resource_id',
        ],
        'parks' => [
            'table'           => 'parks',
            'province_column' => 'province',
            'zone_column'     => 'zone',
            'created_column'  => 'created_at',
            'resource_column' => 'resource_id',
        ],
        'complexes' => [
            'table'           => 'complexes',
            'province_column' => 'province',
            'zone_column'     => 'zone',
            'created_column'  => 'created_at',
            'resource_column' => 'resource_id',
        ],
        'deplomatic_places' => [
            'table'           => 'deplomatic_places',
            'province_column' => 'province',
            'zone_column'     => 'zone',
            'created_column'  => 'created_at',
            'resource_column' => 'resource_id',
        ],
        'universities' => [
            'table'           => 'universities',
            'province_column' => 'province',
            'zone_column'     => 'zone',
            'created_column'  => 'created_at',
            'resource_column' => 'resource_id',
        ],
        'wakilanos' => [
            'table'           => 'wakilanos',
            'province_column' => 'province',
            'zone_column'     => 'zone',
            'created_column'  => 'created_at',
            'resource_column' => 'resource_id',
        ],
    ];

    /**
     * GET /api/reports/fifty-formats
     *
     * Query params:
     *  - group: 'province_zone' | 'resource'
     *  - period / granularity: 'daily' | 'weekly' | 'monthly' | 'yearly'
     *  - from, to (YYYY-MM-DD) – optional
     *  - ignoreDates: 1 | 0
     *  - formats: 'fasads,apartments,...'
     *  - search: optional
     *  - zone: optional
     */
    public function index(Request $request)
    {
        $group = $request->input('group', 'province_zone');

        // accept both "period" and "granularity" like your dashboard repo
        $period = $request->input(
            'period',
            $request->input('granularity', 'monthly')
        );

        $ignoreDates = (int) $request->input('ignoreDates', 0);
        $search      = $request->input('search');
        $zoneFilter  = $request->input('zone');

        $formats = $this->normalizeFormats($request->input('formats'));

        [$from, $to] = $ignoreDates
            ? [null, null]
            : $this->resolveDateRange($request, $period);

        if ($group === 'resource') {
            $result = $this->buildByResource($formats, $from, $to, $search, $zoneFilter);
        } else {
            $result = $this->buildByProvinceZone($formats, $from, $to, $search, $zoneFilter);
        }

        return response()->json([
            'data' => array_values($result['rows']),
            'meta' => [
                'group'       => $group,
                'period'      => $period,
                'ignoreDates' => $ignoreDates,
                'from'        => $from?->toDateString(),
                'to'          => $to?->toDateString(),
                'formats'     => $formats,
                'search'      => $search,
                'zone'        => $zoneFilter,
                'chart'       => array_values($result['chart']),
                'summary'     => $result['summary'],
            ],
        ]);
    }

    // ───────────────────── helpers ─────────────────────

    protected function normalizeFormats(?string $formats): array
    {
        if (!$formats) {
            return array_keys($this->formatConfig);
        }

        $list = collect(explode(',', $formats))
            ->map(fn($f) => trim($f))
            ->filter()
            ->values()
            ->all();

        if (empty($list)) {
            return array_keys($this->formatConfig);
        }

        return array_values(array_intersect($list, array_keys($this->formatConfig)));
    }

    protected function resolveDateRange(Request $request, string $period): array
    {
        $from = $request->input('from');
        $to   = $request->input('to');

        if ($from || $to) {
            $fromC = $from ? Carbon::parse($from)->startOfDay() : null;
            $toC   = $to ? Carbon::parse($to)->endOfDay()   : null;
            return [$fromC, $toC];
        }

        $now = now();

        return match ($period) {
            'daily'   => [$now->copy()->startOfDay(),   $now->copy()->endOfDay()],
            'weekly'  => [$now->copy()->startOfWeek(),  $now->copy()->endOfWeek()],
            'yearly'  => [$now->copy()->startOfYear(),  $now->copy()->endOfYear()],
            default   => [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()],
        };
    }

    /**
     * province+zone aggregation across all selected formats
     */
    protected function buildByProvinceZone(
        array $formats,
        ?Carbon $from,
        ?Carbon $to,
        ?string $search,
        ?string $zoneFilter
    ): array {
        $rows = [];

        $summary = [];
        foreach (array_keys($this->formatConfig) as $key) {
            $summary[$key] = 0;
        }
        $summary['overall'] = 0;

        foreach ($formats as $fmtKey) {
            $cfg = $this->formatConfig[$fmtKey] ?? null;
            if (!$cfg) continue;

            $table       = $cfg['table'];
            $provinceCol = $cfg['province_column'];
            $zoneCol     = $cfg['zone_column'];
            $createdCol  = $cfg['created_column'] ?? 'created_at';

            if (!Schema::hasTable($table)) continue;
            // if columns are missing, skip this table safely
            if (!Schema::hasColumn($table, $provinceCol) || !Schema::hasColumn($table, $zoneCol)) {
                continue;
            }
            if (!Schema::hasColumn($table, $createdCol)) {
                // if no created_column, try created_at, else skip
                if ($createdCol !== 'created_at' && Schema::hasColumn($table, 'created_at')) {
                    $createdCol = 'created_at';
                } else {
                    continue;
                }
            }

            $q = DB::table($table)
                ->selectRaw("$provinceCol as province, $zoneCol as zone, COUNT(*) as total");

            if ($from) {
                $q->where($createdCol, '>=', $from);
            }
            if ($to) {
                $q->where($createdCol, '<=', $to);
            }

            if ($zoneFilter) {
                $q->where($zoneCol, $zoneFilter);
            }

            if ($search) {
                $q->where(function ($sub) use ($search, $provinceCol, $zoneCol) {
                    $sub->where($provinceCol, 'like', "%{$search}%")
                        ->orWhere($zoneCol, 'like', "%{$search}%");
                });
            }

            $q->groupBy($provinceCol, $zoneCol);

            $data = $q->get();

            foreach ($data as $row) {
                $p = $row->province ?? '---';
                $z = $row->zone ?? '---';

                $rowKey = $p . '|' . $z;

                if (!isset($rows[$rowKey])) {
                    $rows[$rowKey] = [
                        'province' => $p,
                        'zone'     => $z,
                        // initialize all formats to 0
                    ];
                    foreach (array_keys($this->formatConfig) as $key) {
                        $rows[$rowKey][$key] = 0;
                    }
                    $rows[$rowKey]['total'] = 0;
                }

                $count = (int) $row->total;

                $rows[$rowKey][$fmtKey] += $count;
                $rows[$rowKey]['total'] += $count;

                $summary[$fmtKey]   = ($summary[$fmtKey] ?? 0) + $count;
                $summary['overall'] += $count;
            }
        }

        // chart by province/zone
        $chart = [];
        foreach ($rows as $r) {
            $label = trim(($r['province'] ?? '') . ' / ' . ($r['zone'] ?? ''));
            if ($label === '/') $label = '---';
            $chart[] = [
                'label' => $label,
                'total' => (int) $r['total'],
            ];
        }

        return [
            'rows'    => $rows,
            'chart'   => $chart,
            'summary' => $summary,
        ];
    }

    /**
     * resource aggregation across all selected formats
     */
    protected function buildByResource(
        array $formats,
        ?Carbon $from,
        ?Carbon $to,
        ?string $search,
        ?string $zoneFilter
    ): array {
        $rows = [];

        $summary = [];
        foreach (array_keys($this->formatConfig) as $key) {
            $summary[$key] = 0;
        }
        $summary['overall'] = 0;

        foreach ($formats as $fmtKey) {
            $cfg = $this->formatConfig[$fmtKey] ?? null;
            if (!$cfg) continue;

            $table          = $cfg['table'];
            $createdCol     = $cfg['created_column'] ?? 'created_at';
            $resourceColumn = $cfg['resource_column'] ?? 'resource_id';
            $zoneCol        = $cfg['zone_column'];

            if (!Schema::hasTable($table)) continue;
            if (!Schema::hasColumn($table, $resourceColumn)) {
                continue;
            }
            if (!Schema::hasColumn($table, $createdCol)) {
                if ($createdCol !== 'created_at' && Schema::hasColumn($table, 'created_at')) {
                    $createdCol = 'created_at';
                } else {
                    continue;
                }
            }

            $q = DB::table($table)
                ->leftJoin('resources', 'resources.id', '=', $table . '.' . $resourceColumn)
                ->selectRaw("$table.$resourceColumn as resource_id, resources.name as resource_name, COUNT(*) as total");

            if ($from) {
                $q->where($table . '.' . $createdCol, '>=', $from);
            }
            if ($to) {
                $q->where($table . '.' . $createdCol, '<=', $to);
            }

            if ($zoneFilter && $zoneCol && Schema::hasColumn($table, $zoneCol)) {
                $q->where($table . '.' . $zoneCol, $zoneFilter);
            }

            if ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('resources.name', 'like', "%{$search}%");
                });
            }

            $q->groupBy($table . '.' . $resourceColumn, 'resources.name');

            $data = $q->get();

            foreach ($data as $row) {
                $rid   = $row->resource_id ?? 0;
                $rname = $row->resource_name ?? '---';

                $rowKey = (string) $rid;

                if (!isset($rows[$rowKey])) {
                    $rows[$rowKey] = [
                        'resource_id'   => $rid,
                        'resource_name' => $rname,
                    ];
                    foreach (array_keys($this->formatConfig) as $key) {
                        $rows[$rowKey][$key] = 0;
                    }
                    $rows[$rowKey]['total'] = 0;
                }

                $count = (int) $row->total;

                $rows[$rowKey][$fmtKey] += $count;
                $rows[$rowKey]['total'] += $count;

                $summary[$fmtKey]   = ($summary[$fmtKey] ?? 0) + $count;
                $summary['overall'] += $count;
            }
        }

        // chart by resource
        $chart = [];
        foreach ($rows as $r) {
            $label = $r['resource_name'] ?: '---';
            $chart[] = [
                'label' => $label,
                'total' => (int) $r['total'],
            ];
        }

        return [
            'rows'    => $rows,
            'chart'   => $chart,
            'summary' => $summary,
        ];
    }
}
