<?php

namespace App\Services\Dashboard\People\Tables;

use App\Models\CaptiveDelivery;
use App\Services\Dashboard\Contracts\DashboardMetric;
use Carbon\CarbonInterface;

class LatestCaptiveDeliveries implements DashboardMetric
{
    public function calculate(CarbonInterface $from, CarbonInterface $to, array $filters = []): array
    {
        $q = CaptiveDelivery::query()->whereBetween('created_at', [$from, $to]);

        if (!empty($filters['zone']))  $q->where('now_province', $filters['zone']);
        if (!empty($filters['hawza'])) $q->where('now_district', $filters['hawza']);

        $rows = $q->orderByDesc('created_at')->limit(10)->get([
            'name','now_province','now_district','captive_date','suspect_type','created_at'
        ]);

        return [
            'id'      => 'latest_captive_deliveries',
            'title'   => ['fa' => 'آخرین تسلیم‌شده‌ها', 'en' => 'Latest Captive Deliveries'],
            'headers' => ['اسم', 'ولایت', 'ولسوالی', 'نیټه', 'ډول', 'جوړښت'],
            'rows'    => $rows->map(fn($r) => [
                $r->name, $r->now_province, $r->now_district,
                $r->captive_date ?: $r->created_at?->toDateString(),
                $r->suspect_type,
                optional($r->created_at)->toDateTimeString(),
            ])->toArray(),
            'total'   => $rows->count(),
        ];
    }
}
