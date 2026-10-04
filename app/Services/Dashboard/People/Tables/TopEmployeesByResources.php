<?php

namespace App\Services\Dashboard\People\Tables;

use App\Models\OfficeEmployee;
use App\Models\Resource;
use App\Services\Dashboard\Contracts\DashboardMetric;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;

class TopEmployeesByResources implements DashboardMetric
{
    public function calculate(CarbonInterface $from, CarbonInterface $to, array $filters = []): array
    {
        $res = Resource::query()
            ->select(['office_employee_id', DB::raw("COUNT(*) as res_count")])
            ->where(function ($q) use ($from, $to) {
                $q->whereBetween('resource_saved_date', [$from->toDateString(), $to->toDateString()])
                  ->orWhereBetween('created_at', [$from, $to]);
            })
            ->groupBy('office_employee_id');

        $q = OfficeEmployee::query()
            ->joinSub($res, 'rc', fn($join) => $join->on('rc.office_employee_id', '=', 'office_employees.id'))
            ->select(['office_employees.id','office_employees.name','office_employees.zone','office_employees.hawza','rc.res_count'])
            ->orderByDesc('rc.res_count');

        if (!empty($filters['zone']))  $q->where('office_employees.zone',  $filters['zone']);
        if (!empty($filters['hawza'])) $q->where('office_employees.hawza', $filters['hawza']);

        $rows = $q->limit(10)->get();

        return [
            'id'      => 'top_employees_by_resources',
            'title'   => ['fa' => 'کارمندان با بیشترین منبع', 'en' => 'Top Employees by Resources'],
            'headers' => ['اسم', 'آمریت', 'حوزه', 'شمیر منابع'],
            'rows'    => $rows->map(fn($r) => [$r->name, $r->zone, $r->hawza, (int)$r->res_count])->toArray(),
            'total'   => $rows->count(),
        ];
    }
}
