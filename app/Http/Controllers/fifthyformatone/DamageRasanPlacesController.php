<?php

namespace App\Http\Controllers\fifthyformatone;

use App\Http\Controllers\Controller;
use App\Http\Requests\fifthyformatone\StoreDamageRasanPlacesRequest;
use App\Http\Requests\fifthyformatone\UpdateDamageRasanPlacesRequest;
use App\Http\Resources\fifthyformatone\DamageRasanPlacesResource;
use App\Models\fifthyformatone\DamageRasanPlaces;
use Illuminate\Http\Request;

class DamageRasanPlacesController extends Controller
{
    /** GET /damage-rasan-places */
    public function index(Request $request)
    {
        $perPage = (int) $request->input('perPage', 10);
        $search  = $request->input('search');

        $q = DamageRasanPlaces::query()
            ->with(['resource:id,name,code_number,phone_number'])
            ->latest();

        if ($search) {
            $q->where(function ($w) use ($search) {
                $w->where('province', 'like', "%{$search}%")
                  ->orWhere('district', 'like', "%{$search}%")
                  ->orWhere('village', 'like', "%{$search}%")
                  ->orWhere('damage_place', 'like', "%{$search}%");
            });
        }

        return DamageRasanPlacesResource::collection($q->paginate($perPage));
    }

    /** POST /damage-rasan-places */
    public function store(StoreDamageRasanPlacesRequest $request)
    {
        $record = DamageRasanPlaces::create($request->validated())
            ->load(['resource:id,name,code_number,phone_number']);

        return DamageRasanPlacesResource::make($record);
    }

    /** GET /damage-rasan-places/{damage_rasan_place} */
    public function show(DamageRasanPlaces $damage_rasan_place)
    {
        $damage_rasan_place->load(['resource:id,name,code_number,phone_number']);
        return DamageRasanPlacesResource::make($damage_rasan_place);
    }

    /** PUT /damage-rasan-places/{damage_rasan_place} */
    public function update(UpdateDamageRasanPlacesRequest $request, DamageRasanPlaces $damage_rasan_place)
    {
        $damage_rasan_place->update($request->validated());
        $damage_rasan_place->load(['resource:id,name,code_number,phone_number']);
        return DamageRasanPlacesResource::make($damage_rasan_place);
    }

    /** DELETE /damage-rasan-places/{damage_rasan_place} */
    public function destroy(DamageRasanPlaces $damage_rasan_place)
    {
        $damage_rasan_place->delete();
        return response()->noContent();
    }

    /** POST /damage-rasan-places/bulk-delete  { "ids": [1,2,3] } */
    public function bulkDelete(Request $request)
    {
        DamageRasanPlaces::destroy($request->input('ids', []));
        return response()->noContent();
    }
}
