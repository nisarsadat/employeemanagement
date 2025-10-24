<?php

namespace App\Http\Controllers\Api\fifthyformatfour;

use App\Http\Controllers\Controller;
use App\Http\Requests\fifthyformatfour\StoreParkRequest;
use App\Http\Requests\fifthyformatfour\UpdateParkRequest;
use App\Http\Resources\fifthyformatfour\ParkResource;
use App\Models\fifthyformatfour\Park;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class ParkController extends Controller
{
    public function index(Request $request)
    {
        $query = Park::query()->with(['resource:id,name,code_number,phone_number']);

        if ($s = trim((string) $request->query('search', ''))) {
            $query->where(function (Builder $q) use ($s) {
                $q->where('park_name','like',"%$s%")
                  ->orWhere('province','like',"%$s%")
                  ->orWhere('village','like',"%$s%");
            });
        }

        $perPage = $request->query('perPage');
        $page    = $request->query('page');
        $perPageInt = filter_var($perPage, FILTER_VALIDATE_INT, ['options'=>['min_range'=>1]]);
        $pageInt    = filter_var($page,    FILTER_VALIDATE_INT, ['options'=>['min_range'=>1]]);

        if ($perPageInt === false || $pageInt === false) {
            return ParkResource::collection($query->orderByDesc('id')->get());
        }

        $rows = $query->orderByDesc('id')->paginate($perPageInt, ['*'], 'page', $pageInt);
        return ParkResource::collection($rows);
    }

    public function store(StoreParkRequest $request)
    {
        $row = Park::create($request->validated());
        $row->load(['resource:id,name,code_number,phone_number']);
        return new ParkResource($row);
    }

    public function show(Park $park)
    {
        $park->load(['resource:id,name,code_number,phone_number']);
        return new ParkResource($park);
    }

    public function update(UpdateParkRequest $request, Park $park)
    {
        $park->update($request->validated());
        $park->load(['resource:id,name,code_number,phone_number']);
        return new ParkResource($park);
    }

    public function destroy(Park $park)
    {
        $park->delete();
        return response()->json(['message' => 'Deleted']);
    }
}
