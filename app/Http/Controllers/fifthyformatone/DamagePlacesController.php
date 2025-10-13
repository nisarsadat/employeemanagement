<?php

namespace App\Http\Controllers\fifthyformatone;

use App\Http\Controllers\Controller;
use App\Http\Requests\fifthyformatone\StoreDamagePlacesRequest;
use App\Http\Requests\fifthyformatone\UpdateDamagePlacesRequest;
use App\Http\Resources\fifthyformatone\DamagePlacesResource;
use App\Models\fifthyformatone\DamagePlaces;
use Illuminate\Http\Request;

class DamagePlacesController extends Controller
{
    /** GET /damage-places */
    public function index(Request $request)
    {
        $perPage = (int) $request->input('perPage', 10);
        $search  = $request->input('search');

        $q = DamagePlaces::query()
            ->with(['resource:id,name,code_number,phone_number']) // ⬅️ explicit eager-load
            ->latest();

        if ($search) {
            $q->where(function ($w) use ($search) {
                $w->where('province', 'like', "%{$search}%")
                  ->orWhere('district', 'like', "%{$search}%")
                  ->orWhere('village', 'like', "%{$search}%")
                  ->orWhere('tahdid_type', 'like', "%{$search}%");
            });
        }

        return DamagePlacesResource::collection($q->paginate($perPage));
    }

    /** POST /damage-places */
    public function store(StoreDamagePlacesRequest $request)
    {
        $record = DamagePlaces::create($request->validated())
            ->load(['resource:id,name,code_number,phone_number']); // ⬅️ load before returning

        return DamagePlacesResource::make($record);
    }

    /** GET /damage-places/{damage_place} */
    public function show(DamagePlaces $damage_place)
    {
        $damage_place->load(['resource:id,name,code_number,phone_number']); // ⬅️ explicit
        return DamagePlacesResource::make($damage_place);
    }

    /** PUT /damage-places/{damage_place} */
    public function update(UpdateDamagePlacesRequest $request, DamagePlaces $damage_place)
    {
        $damage_place->update($request->validated());
        $damage_place->load(['resource:id,name,code_number,phone_number']); // ⬅️ explicit
        return DamagePlacesResource::make($damage_place);
    }

    /** DELETE /damage-places/{damage_place} */
    public function destroy(DamagePlaces $damage_place)
    {
        $damage_place->delete();
        return response()->noContent();
    }

    /** POST /damage-places/bulk-delete  { "ids": [1,2,3] } */
    public function bulkDelete(Request $request)
    {
        DamagePlaces::destroy($request->input('ids', []));
        return response()->noContent();
    }
}
