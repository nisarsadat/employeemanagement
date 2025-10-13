<?php

namespace App\Http\Controllers\fifthyformatstow;

use App\Http\Controllers\Controller;
use App\Http\Requests\fifthyformatstow\StoreRahnomaMamlatRequest;
use App\Http\Requests\fifthyformatstow\UpdateRahnomaMamlatRequest;
use App\Http\Resources\fifthyformatstow\RahnomaMamlatResource;
use App\Models\fifthyformatstow\RahnomaMamlat;
use Illuminate\Http\Request;

class RahnomaMamlatController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) $request->input('perPage', 10);
        $search  = $request->input('search');

        $q = RahnomaMamlat::query()
            ->with(['resource:id,name,code_number,phone_number'])
            ->latest();

        if ($search) {
            $q->where(function ($qq) use ($search) {
                $qq->where('name', 'like', "%{$search}%")
                   ->orWhere('responsible_name', 'like', "%{$search}%")
                   ->orWhere('responsible_phone', 'like', "%{$search}%")
                   ->orWhere('province', 'like', "%{$search}%")
                   ->orWhere('district', 'like', "%{$search}%");
            });
        }

        return RahnomaMamlatResource::collection($q->paginate($perPage));
    }

    public function store(StoreRahnomaMamlatRequest $request)
    {
        $rahnoma = RahnomaMamlat::create($request->validated())
            ->load(['resource:id,name,code_number,phone_number']);

        return RahnomaMamlatResource::make($rahnoma);
    }

    public function show(RahnomaMamlat $rahnomaMamlat)
    {
        $rahnomaMamlat->load(['resource:id,name,code_number,phone_number']);
        return RahnomaMamlatResource::make($rahnomaMamlat);
    }

    public function update(UpdateRahnomaMamlatRequest $request, RahnomaMamlat $rahnomaMamlat)
    {
        $rahnomaMamlat->update($request->validated());
        $rahnomaMamlat->load(['resource:id,name,code_number,phone_number']);

        return RahnomaMamlatResource::make($rahnomaMamlat);
    }

    public function destroy(RahnomaMamlat $rahnomaMamlat)
    {
        $rahnomaMamlat->delete();
        return response()->noContent();
    }

    public function bulkDelete(Request $request)
    {
        $ids = $request->input('ids', []);
        RahnomaMamlat::destroy($ids);
        return response()->noContent();
    }
}
