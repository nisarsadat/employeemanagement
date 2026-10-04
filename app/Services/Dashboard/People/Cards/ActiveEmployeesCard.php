<?php

namespace App\Services\Dashboard\People\Cards;

use App\Models\OfficeEmployee;
use App\Services\Dashboard\Contracts\DashboardMetric;
use Carbon\CarbonInterface;

class ActiveEmployeesCard implements DashboardMetric
{
    public function calculate(CarbonInterface $from, CarbonInterface $to, array $filters = []): array
    {
        $q = OfficeEmployee::query()->active();

        if (!empty($filters['zone']))  $q->where('zone',  $filters['zone']);
        if (!empty($filters['hawza'])) $q->where('hawza', $filters['hawza']);

        return [
            'id'    => 'active_employees',
            'title' => ['fa' => 'کارمندان فعال', 'en' => 'Active Employees'],
            'value' => (int) $q->count(),
        ];
    }
}
