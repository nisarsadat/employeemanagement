<?php

namespace App\Support;

use Carbon\Carbon;
use Morilog\Jalali\Jalalian;
use Pharaonic\Hijri\Hijri;

class CalendarRange
{
    public static function normalize(?string $calendar): string
    {
        $c = strtolower(trim((string) $calendar));
        return in_array($c, ['miladi','shamsi','qamari']) ? $c : 'miladi';
    }

    public static function resolve(string $period, string $calendar): array
    {
        $calendar = self::normalize($calendar);
        $now = Carbon::now();

        if ($period === 'all') return [null, null];

        if ($period === 'daily')
            return [$now->copy()->startOfDay(), $now->copy()->endOfDay()];

        if ($period === 'weekly')
            return [$now->copy()->subDays(6)->startOfDay(), $now->copy()->endOfDay()];

        if ($period === 'monthly')
            return self::monthRange($calendar, $now);

        return self::yearRange($calendar, $now);
    }

    // -------------------------------------------------
    // MONTH RANGE
    // -------------------------------------------------
    public static function monthRange(string $calendar, Carbon $date): array
    {
        if ($calendar === 'miladi')
            return [$date->copy()->startOfMonth(), $date->copy()->endOfMonth()];

        if ($calendar === 'shamsi') {
            $j = Jalalian::fromCarbon($date);
            $start = Jalalian::fromFormat('Y-m-d', $j->getYear().'-'.$j->getMonth().'-01')->toCarbon()->startOfDay();
            $end   = $start->copy()->addMonth()->subSecond()->endOfDay();
            return [$start, $end];
        }

        // Qamari
        $h = Hijri::convertToHijri($date->year, $date->month, $date->day);
        $startG = Hijri::convertToGregorian($h['year'], $h['month'], 1);
        $start = Carbon::create($startG['year'],$startG['month'],$startG['day'])->startOfDay();

        $nextMonth = $h['month'] == 12 ? 1 : $h['month'] + 1;
        $nextYear  = $h['month'] == 12 ? $h['year'] + 1 : $h['year'];

        $nextG = Hijri::convertToGregorian($nextYear, $nextMonth, 1);
        $end = Carbon::create($nextG['year'],$nextG['month'],$nextG['day'])
            ->subSecond()
            ->endOfDay();

        return [$start, $end];
    }

    // -------------------------------------------------
    // YEAR RANGE
    // -------------------------------------------------
    public static function yearRange(string $calendar, Carbon $date): array
    {
        if ($calendar === 'miladi')
            return [$date->copy()->startOfYear(), $date->copy()->endOfYear()];

        if ($calendar === 'shamsi') {
            $j = Jalalian::fromCarbon($date);
            $start = Jalalian::fromFormat('Y-m-d', $j->getYear().'-01-01')->toCarbon()->startOfDay();
            $end   = $start->copy()->addYear()->subSecond()->endOfDay();
            return [$start, $end];
        }

        // Qamari
        $h = Hijri::convertToHijri($date->year, $date->month, $date->day);

        $startG = Hijri::convertToGregorian($h['year'],1,1);
        $start  = Carbon::create($startG['year'],$startG['month'],$startG['day'])->startOfDay();

        $nextG = Hijri::convertToGregorian($h['year']+1,1,1);
        $end   = Carbon::create($nextG['year'],$nextG['month'],$nextG['day'])
            ->subSecond()
            ->endOfDay();

        return [$start, $end];
    }
}
