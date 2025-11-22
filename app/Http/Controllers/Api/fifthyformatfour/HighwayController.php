<?php

namespace App\Http\Controllers\Api\fifthyformatfour;

use App\Http\Controllers\Controller;
use App\Http\Requests\fifthyformatfour\StoreHighwayRequest;
use App\Http\Requests\fifthyformatfour\UpdateHighwayRequest;
use App\Http\Resources\fifthyformatfour\HighwayResource;
use App\Models\fifthyformatfour\Highway;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class HighwayController extends Controller
{
    public function index(Request $request)
    {
        $query = Highway::query()
            ->with(['resource:id,name,code_number,phone_number']);

        if ($s = trim((string) $request->query('search', ''))) {
            $query->where(function (Builder $q) use ($s) {
                $q->where('high_way_name', 'like', "%{$s}%")
                  ->orWhere('province', 'like', "%{$s}%")
                  ->orWhere('district', 'like', "%{$s}%")
                  ->orWhere('village', 'like', "%{$s}%");
            });
        }

        $perPage = $request->query('perPage');
        $page    = $request->query('page');

        $perPageInt = filter_var($perPage, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $pageInt    = filter_var($page,    FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

        // ✅ Same pattern: if perPage / page invalid → return all, no pagination
        if ($perPageInt === false || $pageInt === false) {
            $rows = $query->orderByDesc('id')->get();
            return HighwayResource::collection($rows);
        }

        $rows = $query->orderByDesc('id')
            ->paginate($perPageInt, ['*'], 'page', $pageInt);

        return HighwayResource::collection($rows);
    }

    public function store(StoreHighwayRequest $request)
    {
        $row = Highway::create($request->validated());

        // ✅ eager-load resource for flattened fields
        $row->load(['resource:id,name,code_number,phone_number']);

        return new HighwayResource($row);
    }

    public function show(Highway $highway)
    {
        $highway->load(['resource:id,name,code_number,phone_number']);

        return new HighwayResource($highway);
    }

    public function update(UpdateHighwayRequest $request, Highway $highway)
    {
        $highway->update($request->validated());

        // ✅ reload for fresh resource_code / resource_phone
        $highway->load(['resource:id,name,code_number,phone_number']);

        return new HighwayResource($highway);
    }

    public function destroy(Highway $highway)
    {
        $highway->delete();

        return response()->json(['message' => 'Deleted']);
    }
}
