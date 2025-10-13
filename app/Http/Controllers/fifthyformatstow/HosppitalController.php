<?php

namespace App\Http\Controllers\fifthyformatstow;

use App\Http\Controllers\Controller;
use App\Http\Requests\fifthyformatstow\StoreHosppitalRequest;
use App\Http\Requests\fifthyformatstow\UpdateHosppitalRequest;
use App\Http\Resources\fifthyformatstow\HosppitalResource;
use App\Models\fifthyformatstow\Hosppital;
use Illuminate\Http\Request;

class HosppitalController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) $request->input('perPage', 10);
        $search  = $request->input('search');

        $q = Hosppital::query()
            ->with(['resource:id,name,code_number,phone_number'])
            ->latest();

        if ($search) {
            $q->where(function ($qq) use ($search) {
                $qq->where('name', 'like', "%{$search}%")
                   ->orWhere('hospita_type', 'like', "%{$search}%")
                   ->orWhere('responsible_name', 'like', "%{$search}%")
                   ->orWhere('responsible_phone', 'like', "%{$search}%")
                   ->orWhere('province', 'like', "%{$search}%")
                   ->orWhere('district', 'like', "%{$search}%");
            });
        }

        return HosppitalResource::collection($q->paginate($perPage));
    }

    public function store(StoreHosppitalRequest $request)
    {
        $hospital = Hosppital::create($request->validated())
            ->load(['resource:id,name,code_number,phone_number']);

        return HosppitalResource::make($hospital);
    }

    public function show(Hosppital $hosppital)
    {
        $hosppital->load(['resource:id,name,code_number,phone_number']);
        return HosppitalResource::make($hosppital);
    }

    public function update(UpdateHosppitalRequest $request, Hosppital $hosppital)
    {
        $hosppital->update($request->validated());
        $hosppital->load(['resource:id,name,code_number,phone_number']);

        return HosppitalResource::make($hosppital);
    }

    public function destroy(Hosppital $hosppital)
    {
        $hosppital->delete();
        return response()->noContent();
    }

    public function bulkDelete(Request $request)
    {
        $ids = $request->input('ids', []);
        Hosppital::destroy($ids);
        return response()->noContent();
    }
}
