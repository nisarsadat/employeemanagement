<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class DashboardDate
{
    public static function whereBetweenFlexible(Builder $q, ?string $dateCol, Carbon $from, Carbon $to): Builder
    {
        if ($dateCol) {
            return $q->where(function ($qq) use ($dateCol, $from, $to) {
                $qq->whereBetween($dateCol, [$from, $to])
                   ->orWhere(function ($qqq) use ($dateCol, $from, $to) {
                       $qqq->whereNull($dateCol)->whereBetween('created_at', [$from, $to]);
                   });
            });
        }

        return $q->whereBetween('created_at', [$from, $to]);
    }
}
