<?php

namespace App\Services\Reports;

use App\Models\Meeting;
use App\Models\SecurityAssessment;
use Illuminate\Support\Carbon;

class FoulActivityRows
{
    /**
     * Meetings (by start date) and security assessments (by created date)
     * for a report window. Null bounds mean all time.
     * $endExclusive is the first moment after the window.
     */
    public static function between(?Carbon $start = null, ?Carbon $endExclusive = null): array
    {
        return [
            'meetings' => self::meetings($start, $endExclusive),
            'security_assessments' => self::assessments($start, $endExclusive),
        ];
    }

    private static function meetings(?Carbon $start, ?Carbon $endExclusive): array
    {
        $query = Meeting::query();

        if ($start) {
            $query->whereDate('start_date', '>=', $start->toDateString());
        }
        if ($endExclusive) {
            $query->whereDate('start_date', '<', $endExclusive->toDateString());
        }

        $total = (clone $query)->count();

        $rows = (clone $query)
            ->orderByDesc('start_date')
            ->orderByDesc('id')
            ->limit(100)
            ->get([
                'id',
                'meeting_title',
                'organ',
                'province',
                'district',
                'start_date',
            ])
            ->map(function (Meeting $row) {
                return [
                    'id' => $row->id,
                    'meeting_title' => $row->meeting_title,
                    'organ' => $row->organ,
                    'province' => $row->province,
                    'district' => $row->district,
                    'start_date' => optional($row->start_date)->toDateString(),
                ];
            })
            ->values();

        return [
            'total' => (int) $total,
            'rows' => $rows,
        ];
    }

    private static function assessments(?Carbon $start, ?Carbon $endExclusive): array
    {
        $query = SecurityAssessment::query();

        if ($start) {
            $query->whereDate('created_at', '>=', $start->toDateString());
        }
        if ($endExclusive) {
            $query->where('created_at', '<', $endExclusive);
        }

        $total = (clone $query)->count();

        $rows = (clone $query)
            ->orderByDesc('id')
            ->limit(100)
            ->get([
                'id',
                'place_name',
                'responsible_owner_name',
                'province',
                'district',
                'specific_place',
            ])
            ->map(function (SecurityAssessment $row) {
                return [
                    'id' => $row->id,
                    'place_name' => $row->place_name,
                    'responsible_owner_name' => $row->responsible_owner_name,
                    'province' => $row->province,
                    'district' => $row->district,
                    'specific_place' => $row->specific_place,
                ];
            })
            ->values();

        return [
            'total' => (int) $total,
            'rows' => $rows,
        ];
    }
}
