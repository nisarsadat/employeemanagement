<?php

namespace App\Services\Dashboard\People\Charts;

use App\Models\OfficeEmployee;
use App\Services\Dashboard\Contracts\DashboardMetric;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;

class EmployeesByZone implements DashboardMetric
{
    public function calculate(CarbonInterface $from, CarbonInterface $to, array $filters = []): array
    {
        $q = OfficeEmployee::query()
            ->select(['zone', DB::raw('COUNT(*) as c')])
            ->groupBy('zone')
            ->orderByDesc('c');

        if (!empty($filters['status'])) $q->where('activity', $filters['status']);
        if (!empty($filters['hawza']))  $q->where('hawza', $filters['hawza']);
        if (!empty($filters['zone']))   $q->where('zone',  $filters['zone']);

        $rows = $q->limit(12)->get();

        return [
            'id'     => 'employees_by_zone',
            'title'  => ['fa' => 'کارمندان برحسب آمریت', 'en' => 'Employees by Zone'],
            'type'   => 'bar',
            'series' => [[
                'name' => 'employees',
                'data' => $rows->map(fn($r) => ['x' => $r->zone ?: '—', 'y' => (int)$r->c])->values(),
            ]],
        ];
    }
}
