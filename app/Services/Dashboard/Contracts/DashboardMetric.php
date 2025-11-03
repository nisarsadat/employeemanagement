<?php

namespace App\Services\Dashboard\Contracts;

use Carbon\CarbonInterface;

interface DashboardMetric
{
    public function calculate(CarbonInterface $from, CarbonInterface $to, array $filters = []): array;
}
