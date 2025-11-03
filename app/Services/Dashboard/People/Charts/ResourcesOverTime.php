<?php

namespace App\Services\Dashboard\People\Charts;

use App\Models\Resource;
use App\Services\Dashboard\Contracts\DashboardMetric;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;

class ResourcesOverTime implements DashboardMetric
{
    public function calculate(CarbonInterface $from, CarbonInterface $to, array $filters = []): array
    {
        $q = Resource::query()
            ->select([
                DB::raw("COALESCE(DATE(resource_saved_date), DATE(created_at)) as d"),
                DB::raw("COUNT(*) as c")
            ])
            ->where(function ($qq) use ($from, $to) {
                $qq->whereBetween('resource_saved_date', [$from->toDateString(), $to->toDateString()])
                   ->orWhereBetween('created_at', [$from, $to]);
            })
            ->groupBy('d')
            ->orderBy('d');

        if (!empty($filters['zone']))  $q->where('now_province', $filters['zone']);
        if (!empty($filters['hawza'])) $q->where('now_district', $filters['hawza']);

        $rows = $q->get();

        return [
            'id'     => 'resources_over_time',
            'title'  => ['fa' => 'روند منابع در زمان', 'en' => 'Resources Over Time'],
            'type'   => 'line',
            'series' => [[
                'name' => 'resources',
                'data' => $rows->map(fn($r) => ['x' => $r->d, 'y' => (int)$r->c])->values(),
            ]],
        ];
    }
}
