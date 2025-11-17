<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OfficeEmployee;
use Illuminate\Http\Request;

class ActiveInactiveOfficeEmployeeController extends Controller
{
    /**
     * GET /api/office-employees/activity-counts
     */
    public function counts()
    {
        return response()->json([
            'active'   => OfficeEmployee::active()->count(),
            'inactive' => OfficeEmployee::inactive()->count(),
            'total'    => OfficeEmployee::count(),
        ]);
    }

    /**
     * GET /api/office-employees/active
     */
    public function active(Request $request)
    {
        $perPageParam = $request->input('perPage', 15);   // from Vue repo.perPage
        $search       = trim((string) $request->input('search', ''));

        $query = OfficeEmployee::query()
            ->active()
            ->search($search) // uses your scopeSearch in the model
            ->with([
                'resource:id,name,code_number,phone_number',
            ])
            ->withCount([
                'events',
                'resources',
                'informations',
                'documents',
            ])
            ->orderByDesc('id');

        // support "all" = no pagination
        if ($perPageParam === 'all' || (int) $perPageParam === 0) {
            $items = $query->get();

            return response()->json([
                'data'  => $items,
                'total' => $items->count(),
            ]);
        }

        $perPage = is_numeric($perPageParam) ? (int) $perPageParam : 15;

        return $query->paginate($perPage);
    }

    /**
     * GET /api/office-employees/inactive
     */
    public function inactive(Request $request)
    {
        $perPageParam = $request->input('perPage', 15);
        $search       = trim((string) $request->input('search', ''));

        $query = OfficeEmployee::query()
            ->inactive()
            ->search($search)
            ->with([
                'resource:id,name,code_number,phone_number',
            ])
            ->withCount([
                'events',
                'resources',
                'informations',
                'documents',
            ])
            ->orderByDesc('id');

        if ($perPageParam === 'all' || (int) $perPageParam === 0) {
            $items = $query->get();

            return response()->json([
                'data'  => $items,
                'total' => $items->count(),
            ]);
        }

        $perPage = is_numeric($perPageParam) ? (int) $perPageParam : 15;

        return $query->paginate($perPage);
    }
}
