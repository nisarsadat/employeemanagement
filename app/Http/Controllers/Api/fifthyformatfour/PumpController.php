<?php

namespace App\Http\Controllers\Api\fifthyformatfour;

use App\Http\Controllers\Controller;
use App\Http\Requests\fifthyformatfour\StorePumpRequest;
use App\Http\Requests\fifthyformatfour\UpdatePumpRequest;
use App\Http\Resources\fifthyformatfour\PumpResource;
use App\Models\fifthyformatfour\Pump;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class PumpController extends Controller
{
    public function index(Request $request)
    {
        $query = Pump::query()->with(['resource:id,name,code_number,phone_number']);

        if ($s = trim((string) $request->query('search', ''))) {
            $query->where(function (Builder $q) use ($s) {
                $q->where('pump_name','like',"%$s%")
                  ->orWhere('main_owner','like',"%$s%")
                  ->orWhere('province','like',"%$s%")
                  ->orWhere('village','like',"%$s%");
            });
        }

        $perPage = $request->query('perPage');
        $page    = $request->query('page');
        $perPageInt = filter_var($perPage, FILTER_VALIDATE_INT, ['options'=>['min_range'=>1]]);
        $pageInt    = filter_var($page,    FILTER_VALIDATE_INT, ['options'=>['min_range'=>1]]);

        // Fallback → return ALL when invalid/missing
        if ($perPageInt === false || $pageInt === false) {
            return PumpResource::collection($query->orderByDesc('id')->get());
        }

        $rows = $query->orderByDesc('id')->paginate($perPageInt, ['*'], 'page', $pageInt);
        return PumpResource::collection($rows);
    }

    public function store(StorePumpRequest $request)
    {
        $row = Pump::create($request->validated());
        $row->load(['resource:id,name,code_number,phone_number']);
        return new PumpResource($row);
    }

    public function show(Pump $pump)
    {
        $pump->load(['resource:id,name,code_number,phone_number']);
        return new PumpResource($pump);
    }

    public function update(UpdatePumpRequest $request, Pump $pump)
    {
        $pump->update($request->validated());
        $pump->load(['resource:id,name,code_number,phone_number']);
        return new PumpResource($pump);
    }

    public function destroy(Pump $pump)
    {
        $pump->delete();
        return response()->json(['message' => 'Deleted']);
    }
}
