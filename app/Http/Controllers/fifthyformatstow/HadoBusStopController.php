<?php

namespace App\Http\Controllers\fifthyformatstow;

use App\Http\Controllers\Controller;
use App\Http\Requests\fifthyformatstow\StoreHadoBusStopRequest;
use App\Http\Requests\fifthyformatstow\UpdateHadoBusStopRequest;
use App\Http\Resources\fifthyformatstow\HadoBusStopResource;
use App\Models\fifthyformatstow\HadoBusStop;
use Illuminate\Http\Request;

class HadoBusStopController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) $request->input('perPage', 10);
        $search  = $request->input('search');

        $q = HadoBusStop::query()
            ->with(['resource:id,name,code_number,phone_number'])
            ->latest();

        if ($search) {
            $q->where(function ($qq) use ($search) {
                $qq->where('name', 'like', "%{$search}%")
                   ->orWhere('hada_type', 'like', "%{$search}%")
                   ->orWhere('responsible_name', 'like', "%{$search}%")
                   ->orWhere('responsible_phone', 'like', "%{$search}%")
                   ->orWhere('province', 'like', "%{$search}%")
                   ->orWhere('district', 'like', "%{$search}%");
            });
        }

        return HadoBusStopResource::collection($q->paginate($perPage));
    }

    public function store(StoreHadoBusStopRequest $request)
    {
        $stop = HadoBusStop::create($request->validated())
            ->load(['resource:id,name,code_number,phone_number']);

        return HadoBusStopResource::make($stop);
    }

    public function show(HadoBusStop $hadoBusStop)
    {
        $hadoBusStop->load(['resource:id,name,code_number,phone_number']);
        return HadoBusStopResource::make($hadoBusStop);
    }

    public function update(UpdateHadoBusStopRequest $request, HadoBusStop $hadoBusStop)
    {
        $hadoBusStop->update($request->validated());
        $hadoBusStop->load(['resource:id,name,code_number,phone_number']);

        return HadoBusStopResource::make($hadoBusStop);
    }

    public function destroy(HadoBusStop $hadoBusStop)
    {
        $hadoBusStop->delete();
        return response()->noContent();
    }

    public function bulkDelete(Request $request)
    {
        $ids = $request->input('ids', []);
        HadoBusStop::destroy($ids);
        return response()->noContent();
    }
}
