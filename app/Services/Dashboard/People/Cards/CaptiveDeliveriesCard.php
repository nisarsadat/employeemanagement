<?php

namespace App\Services\Dashboard\People\Cards;

use App\Models\CaptiveDelivery;
use App\Services\Dashboard\Contracts\DashboardMetric;
use Carbon\CarbonInterface;

class CaptiveDeliveriesCard implements DashboardMetric
{
    public function calculate(CarbonInterface $from, CarbonInterface $to, array $filters = []): array
    {
        $q = CaptiveDelivery::query()->whereBetween('created_at', [$from, $to]);

        if (!empty($filters['zone']))  $q->where('now_province', $filters['zone']);
        if (!empty($filters['hawza'])) $q->where('now_district', $filters['hawza']);

        return [
            'id'    => 'captive_deliveries',
            'title' => ['fa' => 'بندیان تسلیم‌شده', 'en' => 'Captive Deliveries'],
            'value' => (int) $q->count(),
        ];
    }
}
