<?php

namespace App\Services\Dashboard\Contracts;

use Illuminate\Support\Carbon;

interface MetricCalculator
{
    /**
     * @param Carbon $from
     * @param Carbon $to
     * @param array  $filters
     * @return array  Normalized payload for a card/chart/table
     */
    public function calculate(Carbon $from, Carbon $to, array $filters = []): array;
}
