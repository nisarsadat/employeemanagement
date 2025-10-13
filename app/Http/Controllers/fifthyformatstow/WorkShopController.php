<?php

namespace App\Http\Controllers\fifthyformatstow;

use App\Http\Controllers\Controller;
use App\Http\Requests\fifthyformatstow\StoreWorkShopRequest;
use App\Http\Requests\fifthyformatstow\UpdateWorkShopRequest;
use App\Http\Resources\fifthyformatstow\WorkShopResource;
use App\Models\fifthyformatstow\WorkShop;
use Illuminate\Http\Request;

class WorkShopController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) $request->input('perPage', 10);
        $search  = $request->input('search');

        $q = WorkShop::query()
            ->with(['resource:id,name,code_number,phone_number'])
            ->latest();

        if ($search) {
            $q->where(function ($qq) use ($search) {
                $qq->where('workshop_name', 'like', "%{$search}%")
                   ->orWhere('workshop_type', 'like', "%{$search}%")
                   ->orWhere('responsible_name', 'like', "%{$search}%")
                   ->orWhere('responsible_phone', 'like', "%{$search}%")
                   ->orWhere('province', 'like', "%{$search}%")
                   ->orWhere('district', 'like', "%{$search}%");
            });
        }

        return WorkShopResource::collection($q->paginate($perPage));
    }

    public function store(StoreWorkShopRequest $request)
    {
        $workshop = WorkShop::create($request->validated())
            ->load(['resource:id,name,code_number,phone_number']);

        return WorkShopResource::make($workshop);
    }

    public function show(WorkShop $workShop)
    {
        $workShop->load(['resource:id,name,code_number,phone_number']);
        return WorkShopResource::make($workShop);
    }

    public function update(UpdateWorkShopRequest $request, WorkShop $workShop)
    {
        $workShop->update($request->validated());
        $workShop->load(['resource:id,name,code_number,phone_number']);

        return WorkShopResource::make($workShop);
    }

    public function destroy(WorkShop $workShop)
    {
        $workShop->delete();
        return response()->noContent();
    }

    public function bulkDelete(Request $request)
    {
        $ids = $request->input('ids', []);
        WorkShop::destroy($ids);
        return response()->noContent();
    }
}
