<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Information;
use Carbon\Carbon;
use Illuminate\Http\Request;

class InformationReportController extends Controller
{
    public function activityReport(Request $request)
    {
        $period      = $request->input('period', 'all'); // daily|weekly|monthly|yearly|custom|all
        $fromInput   = $request->input('from_date');
        $toInput     = $request->input('to_date');
        $ignoreDates = (int) $request->input('ignore_dates', 0) === 1;

        $search          = $request->input('search');
        $provinceAddr    = $request->input('province_addr');   // Province_addr field
        $mainProvince    = $request->input('main_province');
        $nowProvince     = $request->input('now_province');
        $syasiType       = $request->input('syasi_info_type');
        $jenayeeType     = $request->input('jenayee_info_type');
        $infoDegree      = $request->input('information_degree');
        $officeEmployeeId = $request->input('office_employee_id');
        $resourceId      = $request->input('resource_id');

        // ------------------------------------------------------------------
        // Resolve date range based on period / custom range
        // ------------------------------------------------------------------
        [$dateFrom, $dateTo] = $this->resolveDateRange(
            $period,
            $fromInput,
            $toInput,
            $ignoreDates
        );

        $query = Information::query()
            ->with([
                'officeEmployee:id,name,code_number,activity',
                'resource:id,name,code_number',
            ]);

        // ------------------------------------------------------------------
        // Filters
        // ------------------------------------------------------------------
        if ($search) {
            $like = '%' . $search . '%';
            $query->where(function ($q) use ($like) {
                $q->where('name', 'like', $like)
                    ->orWhere('nick_name', 'like', $like)
                    ->orWhere('father_name', 'like', $like)
                    ->orWhere('grand_father_name', 'like', $like)
                    ->orWhere('Province_addr', 'like', $like)
                    ->orWhere('main_province', 'like', $like)
                    ->orWhere('main_districts', 'like', $like)
                    ->orWhere('main_village', 'like', $like)
                    ->orWhere('now_province', 'like', $like)
                    ->orWhere('now_district', 'like', $like)
                    ->orWhere('now_village', 'like', $like)
                    ->orWhere('syasi_info_type', 'like', $like)
                    ->orWhere('jenayee_info_type', 'like', $like)
                    ->orWhere('information_degree', 'like', $like)
                    ->orWhere('tafseel', 'like', $like)
                    ->orWhere('manager_comment', 'like', $like)
                    ->orWhere('lead_drirection', 'like', $like)
                    ->orWhere('talks', 'like', $like)
                    ->orWhere('note', 'like', $like);
            });
        }

        if ($provinceAddr) {
            $query->where('Province_addr', 'like', '%' . $provinceAddr . '%');
        }

        if ($mainProvince) {
            $query->where('main_province', 'like', '%' . $mainProvince . '%');
        }

        if ($nowProvince) {
            $query->where('now_province', 'like', '%' . $nowProvince . '%');
        }

        if ($syasiType) {
            $query->where('syasi_info_type', 'like', '%' . $syasiType . '%');
        }

        if ($jenayeeType) {
            $query->where('jenayee_info_type', 'like', '%' . $jenayeeType . '%');
        }

        if ($infoDegree) {
            $query->where('information_degree', 'like', '%' . $infoDegree . '%');
        }

        if ($officeEmployeeId) {
            $query->where('office_employee_id', $officeEmployeeId);
        }

        if ($resourceId) {
            $query->where('resource_id', $resourceId);
        }

        if (!$ignoreDates && $dateFrom && $dateTo) {
            $query->whereBetween('information_saved_date', [$dateFrom, $dateTo]);
        } elseif (!$ignoreDates && $dateFrom) {
            $query->whereDate('information_saved_date', '>=', $dateFrom);
        } elseif (!$ignoreDates && $dateTo) {
            $query->whereDate('information_saved_date', '<=', $dateTo);
        }

        $rows = $query
            ->orderBy('Province_addr')
            ->orderByDesc('information_saved_date')
            ->orderByDesc('id')
            ->get();

        $today = Carbon::today();

        $rows = $rows->map(function (Information $info) use ($today) {
            $savedDate = $info->information_saved_date
                ? Carbon::parse($info->information_saved_date)
                : null;

            $daysSince = $savedDate
                ? $savedDate->diffInDays($today)
                : null;

            return [
                'id'                       => $info->id,
                'name'                     => $info->name,
                'nick_name'                => $info->nick_name,
                'father_name'              => $info->father_name,
                'grand_father_name'        => $info->grand_father_name,
                'Province_addr'            => $info->Province_addr,
                'main_province'            => $info->main_province,
                'main_districts'           => $info->main_districts,
                'main_village'             => $info->main_village,
                'now_province'             => $info->now_province,
                'now_district'             => $info->now_district,
                'now_village'              => $info->now_village,
                'syasi_info_type'          => $info->syasi_info_type,
                'jenayee_info_type'        => $info->jenayee_info_type,
                'information_saved_date'   => optional($savedDate)->toDateString(),
                'information_saved_hour'   => $info->information_saved_hour,
                'information_degree'       => $info->information_degree,
                'tafseel'                  => $info->tafseel,
                'manager_comment'          => $info->manager_comment,
                'lead_drirection'          => $info->lead_drirection,
                'talks'                    => $info->talks,
                'note'                     => $info->note,
                'days_since_saved'         => $daysSince,

                'office_employee'          => $info->officeEmployee ? [
                    'id'          => $info->officeEmployee->id,
                    'name'        => $info->officeEmployee->name,
                    'code_number' => $info->officeEmployee->code_number,
                    'activity'    => $info->officeEmployee->activity,
                ] : null,

                'resource'                 => $info->resource ? [
                    'id'          => $info->resource->id,
                    'name'        => $info->resource->name,
                    'code_number' => $info->resource->code_number,
                ] : null,
            ];
        });

        // ------------------------------------------------------------------
        // Global totals
        // ------------------------------------------------------------------
        $totalCount        = $rows->count();
        $syasiCount        = $rows->whereNotNull('syasi_info_type')->count();
        $jenayeeCount      = $rows->whereNotNull('jenayee_info_type')->count();
        $withManager       = $rows->whereNotNull('manager_comment')->count();
        $withLeadDirection = $rows->whereNotNull('lead_drirection')->count();
        $withTalks         = $rows->whereNotNull('talks')->count();

        // ------------------------------------------------------------------
        // Group by Province_addr
        // ------------------------------------------------------------------
        $grouped = $rows->groupBy(function ($item) {
            return $item['Province_addr'] ?? '---';
        })->map(function ($items, $province) {
            $itemsCollection = collect($items);

            return [
                'province_addr' => $province,
                'totals'        => [
                    'informations'        => $itemsCollection->count(),
                    'with_syasi'          => $itemsCollection->whereNotNull('syasi_info_type')->count(),
                    'with_jenayee'        => $itemsCollection->whereNotNull('jenayee_info_type')->count(),
                    'with_manager_comment'=> $itemsCollection->whereNotNull('manager_comment')->count(),
                    'with_lead_direction' => $itemsCollection->whereNotNull('lead_drirection')->count(),
                ],
                'informations'  => $itemsCollection->values()->all(),
            ];
        })->values();

        $meta = [
            'period'   => $period,
            'date_from'=> $dateFrom ? $dateFrom->toDateString() : null,
            'date_to'  => $dateTo ? $dateTo->toDateString() : null,
            'totals'   => [
                'informations'        => $totalCount,
                'with_syasi'          => $syasiCount,
                'with_jenayee'        => $jenayeeCount,
                'with_manager_comment'=> $withManager,
                'with_lead_direction' => $withLeadDirection,
                'with_talks'          => $withTalks,
            ],
            'filters'  => [
                'province_addr'   => $provinceAddr,
                'main_province'   => $mainProvince,
                'now_province'    => $nowProvince,
                'syasi_info_type' => $syasiType,
                'jenayee_info_type'=> $jenayeeType,
                'information_degree'=> $infoDegree,
                'office_employee_id'=> $officeEmployeeId,
                'resource_id'     => $resourceId,
                'search'          => $search,
            ],
        ];

        return response()->json([
            'data' => $grouped,
            'meta' => $meta,
        ]);
    }

    /**
     * Resolve date range based on period and optional custom from/to.
     */
    protected function resolveDateRange(string $period, ?string $from, ?string $to, bool $ignore): array
    {
        if ($ignore || $period === 'all') {
            return [null, null];
        }

        $today = Carbon::today();
        $start = null;
        $end   = null;

        if ($period === 'custom') {
            $start = $from ? Carbon::parse($from)->startOfDay() : null;
            $end   = $to ? Carbon::parse($to)->endOfDay() : null;

            return [$start, $end];
        }

        switch ($period) {
            case 'daily':
                $start = $today->copy()->startOfDay();
                $end   = $today->copy()->endOfDay();
                break;

            case 'weekly':
                $start = $today->copy()->startOfWeek();
                $end   = $today->copy()->endOfWeek();
                break;

            case 'monthly':
                $start = $today->copy()->startOfMonth();
                $end   = $today->copy()->endOfMonth();
                break;

            case 'yearly':
                $start = $today->copy()->startOfYear();
                $end   = $today->copy()->endOfYear();
                break;

            default:
                // fallback: no restriction
                return [null, null];
        }

        return [$start, $end];
    }
}
