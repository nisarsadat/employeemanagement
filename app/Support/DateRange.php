<?php

namespace App\Support;

use Illuminate\Support\Carbon; // Laravel's Carbon (extends nesbot/carbon)
final class DateRange
{
    public static function make(?string $range): array
    {
        $key = $range ?: 'last30d';
        $now = Carbon::now();

        return match ($key) {
            'today'     => [ $now->copy()->startOfDay(),  $now->copy()->endOfDay(),   'today'    ],
            'last7d'    => [ $now->copy()->subDays(6)->startOfDay(), $now->copy()->endOfDay(), 'last7d'   ],
            'thisMonth' => [ $now->copy()->startOfMonth(), $now->copy()->endOfMonth(), 'thisMonth'],
            'thisYear'  => [ $now->copy()->startOfYear(),  $now->copy()->endOfYear(),  'thisYear' ],
            default     => [ $now->copy()->subDays(29)->startOfDay(), $now->copy()->endOfDay(), 'last30d' ],
        };
    }
}
