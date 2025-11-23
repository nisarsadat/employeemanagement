<?php

namespace App\Http\Controllers\Api\fifthyformatfour;

use App\Http\Controllers\Controller;
use App\Http\Requests\fifthyformatfour\StoreGovernmentPlaceRequest;
use App\Http\Requests\fifthyformatfour\UpdateGovernmentPlaceRequest;
use App\Http\Resources\fifthyformatfour\GovernmentPlaceResource;
use App\Models\fifthyformatfour\GovernmentPlace;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class GovernmentPlaceController extends Controller
{
    /**
     * GET /government-places
     */
    public function index(Request $request)
    {
        $perPage = (int) $request->input('perPage', 10);
        $search  = trim((string) $request->input('search', ''));

        $q = GovernmentPlace::query()
            ->with(['resource:id,name,code_number,phone_number'])
            ->latest('id');

        if ($search !== '') {
            $q->where(function (Builder $w) use ($search) {
                $w->where('government_place_name', 'like', "%{$search}%")
                  ->orWhere('province', 'like', "%{$search}%")
                  ->orWhere('district', 'like', "%{$search}%")
                  ->orWhere('village', 'like', "%{$search}%");
            });
        }

        return GovernmentPlaceResource::collection($q->paginate($perPage));
    }

    /**
     * POST /government-places
     */
    public function store(StoreGovernmentPlaceRequest $request)
    {
        $data = $request->validated();

        $row = GovernmentPlace::create($data);
        $row->load(['resource:id,name,code_number,phone_number']);

        return GovernmentPlaceResource::make($row);
    }

    /**
     * GET /government-places/{governmentplace}
     */
    public function show(GovernmentPlace $governmentplace)
    {
        $governmentplace->load(['resource:id,name,code_number,phone_number']);

        return GovernmentPlaceResource::make($governmentplace);
    }

    /**
     * PUT /government-places/{governmentplace}
     */
    public function update(UpdateGovernmentPlaceRequest $request, GovernmentPlace $governmentplace)
    {
        $data = $request->validated();

        $governmentplace->update($data);
        $governmentplace->load(['resource:id,name,code_number,phone_number']);

        return GovernmentPlaceResource::make($governmentplace);
    }

    /**
     * DELETE /government-places/{governmentplace}
     */
    public function destroy(GovernmentPlace $governmentplace)
    {
        $governmentplace->delete();

        return response()->noContent();
    }

    /**
     * Optional bulk delete (if you need it)
     * POST /government-places/bulk-delete  with { "ids": [1,2,3] }
     */
    public function bulkDelete(Request $request)
    {
        $ids = (array) $request->input('ids', []);
        GovernmentPlace::destroy($ids);

        return response()->noContent();
    }
}
