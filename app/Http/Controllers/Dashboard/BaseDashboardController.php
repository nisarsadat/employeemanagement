<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Support\DateRange;
use Carbon\Carbon;

abstract class BaseDashboardController extends Controller
{
    protected function parseRange($request): array
    {
        [$from, $to, $key] = DateRange::make($request->input('range'));
        return [$from, $to, $key];
    }

    protected function meta(string $rangeKey): array
    {
        return [
            'range'        => $rangeKey,
            'generated_at' => Carbon::now()->toDateTimeString(),
        ];
    }
}
