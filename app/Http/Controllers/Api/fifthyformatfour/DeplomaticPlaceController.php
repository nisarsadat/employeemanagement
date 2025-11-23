<?php

namespace App\Http\Controllers\Api\fifthyformatfour;

use App\Http\Controllers\Controller;
use App\Http\Requests\fifthyformatfour\StoreDeplomaticPlaceRequest;
use App\Http\Requests\fifthyformatfour\UpdateDeplomaticPlaceRequest;
use App\Http\Resources\fifthyformatfour\DeplomaticPlaceResource;
use App\Models\fifthyformatfour\DeplomaticPlace;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class DeplomaticPlaceController extends Controller
{
    public function index(Request $request)
    {
        $query = DeplomaticPlace::query()->with(['resource:id,name,code_number,phone_number']);

        if ($s = trim((string)$request->query('search', ''))) {
            $query->where(function (Builder $q) use ($s) {
                $q->where('deplomatic_place_name','like',"%$s%")
                  ->orWhere('province','like',"%$s%")
                  ->orWhere('district','like',"%$s%")
                  ->orWhere('village','like',"%$s%");
            });
        }

        $perPage = $request->query('perPage');
        $page    = $request->query('page');
        $perPageInt = filter_var($perPage, FILTER_VALIDATE_INT, ['options'=>['min_range'=>1]]);
        $pageInt    = filter_var($page,    FILTER_VALIDATE_INT, ['options'=>['min_range'=>1]]);

        // Fallback → return ALL when missing/invalid
        if ($perPageInt === false || $pageInt === false) {
            return DeplomaticPlaceResource::collection($query->orderByDesc('id')->get());
        }

        $rows = $query->orderByDesc('id')->paginate($perPageInt, ['*'], 'page', $pageInt);
        return DeplomaticPlaceResource::collection($rows);
    }

    public function store(StoreDeplomaticPlaceRequest $request)
    {
        $row = DeplomaticPlace::create($request->validated());
        $row->load(['resource:id,name,code_number,phone_number']);
        return new DeplomaticPlaceResource($row);
    }

    public function show(DeplomaticPlace $deplomaticplace)
    {
        $deplomaticplace->load('resource');
        return new DeplomaticPlaceResource($deplomaticplace);
    }

    public function update(UpdateDeplomaticPlaceRequest $request, DeplomaticPlace $deplomaticplace)
    {
        $deplomaticplace->update($request->validated());
        $deplomaticplace->load(['resource:id,name,code_number,phone_number']);
        return new DeplomaticPlaceResource($deplomaticplace);
    }

    public function destroy(DeplomaticPlace $deplomaticplace)
    {
        $deplomaticplace->delete();
        return response()->json(['message' => 'Deleted']);
    }
}
