<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\OfficeEmployee;

class ActiveInactiveOfficeEmployeeController extends Controller
{
    // GET /api/office-employees/activity-counts
    public function counts()
    {
        return response()->json([
            'active'   => OfficeEmployee::active()->count(),
            'inactive' => OfficeEmployee::inactive()->count(),
            'total'    => OfficeEmployee::count(),
        ]);
    }

    // GET /api/office-employees/active
    public function active(Request $request)
    {
        $perPage = (int) $request->integer('perPage', 15);
        $q       = trim((string) $request->get('search', ''));

        $query = OfficeEmployee::query()
            ->with(['resource:id,name,code_number,phone_number'])
            ->active();

        if ($q !== '') {
            $query->where(function ($qq) use ($q) {
                $qq->where('name', 'like', "%{$q}%")
                   ->orWhere('code_number', 'like', "%{$q}%")
                   ->orWhere('phone_number', 'like', "%{$q}%");
            });
        }

        return $query->paginate($perPage);
    }

    // GET /api/office-employees/inactive
    public function inactive(Request $request)
    {
        $perPage = (int) $request->integer('perPage', 15);
        $q       = trim((string) $request->get('search', ''));

        $query = OfficeEmployee::query()
            ->with(['resource:id,name,code_number,phone_number'])
            ->inactive();

        if ($q !== '') {
            $query->where(function ($qq) use ($q) {
                $qq->where('name', 'like', "%{$q}%")
                   ->orWhere('code_number', 'like', "%{$q}%")
                   ->orWhere('phone_number', 'like', "%{$q}%");
            });
        }

        return $query->paginate($perPage);
    }
}
