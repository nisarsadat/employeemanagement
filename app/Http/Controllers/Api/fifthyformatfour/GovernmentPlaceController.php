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
    public function index(Request $request)
    {
        $query = GovernmentPlace::query()
            ->with(['resource:id,name,code_number,phone_number']);

        if ($s = trim((string) $request->query('search', ''))) {
            $query->where(function (Builder $q) use ($s) {
                $q->where('government_place_name','like',"%$s%")
                  ->orWhere('province','like',"%$s%")
                  ->orWhere('village','like',"%$s%");
            });
        }

        $perPage = $request->query('perPage');
        $page    = $request->query('page');

        $perPageInt = filter_var($perPage, FILTER_VALIDATE_INT, ['options'=>['min_range'=>1]]);
        $pageInt    = filter_var($page,    FILTER_VALIDATE_INT, ['options'=>['min_range'=>1]]);

        if ($perPageInt === false || $pageInt === false) {
            return GovernmentPlaceResource::collection($query->orderByDesc('id')->get());
        }

        $rows = $query->orderByDesc('id')->paginate($perPageInt, ['*'], 'page', $pageInt);
        return GovernmentPlaceResource::collection($rows);
    }

    public function store(StoreGovernmentPlaceRequest $request)
    {
        $row = GovernmentPlace::create($request->validated());
        $row->load(['resource:id,name,code_number,phone_number']);
        return new GovernmentPlaceResource($row);
    }

    public function show(GovernmentPlace $governmentplace)
    {
        $governmentplace->load(['resource:id,name,code_number,phone_number']);
        return new GovernmentPlaceResource($governmentplace);
    }

    public function update(UpdateGovernmentPlaceRequest $request, GovernmentPlace $governmentplace)
    {
        $governmentplace->update($request->validated());
        $governmentplace->load(['resource:id,name,code_number,phone_number']);
        return new GovernmentPlaceResource($governmentplace);
    }

    public function destroy(GovernmentPlace $governmentplace)
    {
        $governmentplace->delete();
        return response()->json(['message' => 'Deleted']);
    }
}
