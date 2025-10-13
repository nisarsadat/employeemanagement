<?php

namespace App\Http\Controllers\fifthyformatone;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\fifthyformatone\StoreOldSoldiersHomesRequest;
use App\Http\Requests\fifthyformatone\UpdateOldSoldiersHomesRequest;
use App\Http\Resources\fifthyformatone\OldSoldiersHomesResource;
use App\Models\fifthyformatone\OldSoldiersHomes;

class OldSoldiersHomesController extends Controller
{
    /** GET /old-soldiers-homes */
    public function index(Request $request)
    {
        // accept either perPage or itemsPerPage (frontend sometimes varies)
        $perPage = (int) ($request->input('perPage', $request->input('itemsPerPage', 10)));
        $search  = $request->input('search');

        $q = OldSoldiersHomes::query()
            ->with(['resource:id,name,code_number,phone_number']) // <- only what we need
            ->latest();

        if ($search) {
            $q->where(function ($w) use ($search) {
                $w->where('name', 'like', "%{$search}%")
                  ->orWhere('father_name', 'like', "%{$search}%")
                  ->orWhere('province', 'like', "%{$search}%")
                  ->orWhere('district', 'like', "%{$search}%")
                  ->orWhere('village', 'like', "%{$search}%")
                  ->orWhere('old_job', 'like', "%{$search}%")
                  ->orWhere('now_activity', 'like', "%{$search}%");
            });
        }

        return OldSoldiersHomesResource::collection($q->paginate($perPage));
    }

    /** POST /old-soldiers-homes */
    public function store(StoreOldSoldiersHomesRequest $request)
    {
        $data = $request->validated();

        // Expecting frontend to send resource_id; code/phone are read from relation
        $record = OldSoldiersHomes::create($data)->load(['resource:id,name,code_number,phone_number']);

        return OldSoldiersHomesResource::make($record);
    }

    /** GET /old-soldiers-homes/{oldSoldiersHome} */
    public function show(OldSoldiersHomes $oldSoldiersHome)
    {
        $oldSoldiersHome->load(['resource:id,name,code_number,phone_number']);
        return OldSoldiersHomesResource::make($oldSoldiersHome);
    }

    /** PUT /old-soldiers-homes/{oldSoldiersHome} */
    public function update(UpdateOldSoldiersHomesRequest $request, OldSoldiersHomes $oldSoldiersHome)
    {
        $oldSoldiersHome->update($request->validated());
        $oldSoldiersHome->load(['resource:id,name,code_number,phone_number']);

        return OldSoldiersHomesResource::make($oldSoldiersHome);
    }

    /** DELETE /old-soldiers-homes/{oldSoldiersHome} */
    public function destroy(OldSoldiersHomes $oldSoldiersHome)
    {
        $oldSoldiersHome->delete();
        return response()->noContent();
    }

    /** POST /old-soldiers-homes/bulk-delete */
    public function bulkDelete(Request $request)
    {
        $ids = $request->input('ids', []);
        OldSoldiersHomes::destroy($ids);
        return response()->noContent();
    }
}