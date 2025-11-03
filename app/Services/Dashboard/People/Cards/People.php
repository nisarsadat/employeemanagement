<?php

namespace App\Services\Dashboard\People\Cards;

use App\Models\Resource;
use App\Services\Dashboard\Contracts\DashboardMetric;
use Carbon\Carbon;

class NewResourcesCard implements DashboardMetric
{
    public function calculate(Carbon $from, Carbon $to, array $filters = []): array
    {
        $q = Resource::query();

        // prefer resource_saved_date if present (casted to date in model), else created_at
        $q->where(function ($qq) use ($from, $to) {
            $qq->whereBetween('resource_saved_date', [$from->toDateString(), $to->toDateString()])
               ->orWhereBetween('created_at', [$from, $to]);
        });

        if (!empty($filters['zone']))  $q->where('now_province', $filters['zone']); // or 'zone' if you store there
        if (!empty($filters['hawza'])) $q->where('now_district', $filters['hawza']);

        return [
            'id'    => 'new_resources',
            'title' => ['fa' => 'منابع جدید', 'en' => 'New Resources'],
            'value' => (int) $q->count(),
        ];
    }
}
