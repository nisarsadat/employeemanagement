<?php

namespace App\Http\Controllers\Api\fifthyformatfour;

use App\Http\Controllers\Controller;
use App\Http\Requests\fifthyformatfour\StoreSuperMarketRequest;
use App\Http\Requests\fifthyformatfour\UpdateSuperMarketRequest;
use App\Http\Resources\fifthyformatfour\SuperMarketResource;
use App\Models\fifthyformatfour\SuperMarket;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class SuperMarketController extends Controller
{
    public function index(Request $request)
    {
        $query = SuperMarket::query()->with(['resource:id,name,code_number,phone_number']);

        if ($s = trim((string) $request->query('search', ''))) {
            $query->where(function (Builder $q) use ($s) {
                $q->where('supermarket_name','like',"%$s%")
                  ->orWhere('province','like',"%$s%")
                  ->orWhere('village','like',"%$s%");
            });
        }

        $perPage = $request->query('perPage');
        $page    = $request->query('page');
        $perPageInt = filter_var($perPage, FILTER_VALIDATE_INT, ['options'=>['min_range'=>1]]);
        $pageInt    = filter_var($page,    FILTER_VALIDATE_INT, ['options'=>['min_range'=>1]]);

        if ($perPageInt === false || $pageInt === false) {
            return SuperMarketResource::collection($query->orderByDesc('id')->get());
        }

        $rows = $query->orderByDesc('id')->paginate($perPageInt, ['*'], 'page', $pageInt);
        return SuperMarketResource::collection($rows);
    }

    public function store(StoreSuperMarketRequest $request)
    {
        $row = SuperMarket::create($request->validated());
        $row->load(['resource:id,name,code_number,phone_number']);
        return new SuperMarketResource($row);
    }

    public function show(SuperMarket $supermarket)
    {
        $supermarket->load('resource');
        return new SuperMarketResource($supermarket);
    }

    public function update(UpdateSuperMarketRequest $request, SuperMarket $supermarket)
    {
        $supermarket->update($request->validated());
        $supermarket->load(['resource:id,name,code_number,phone_number']);
        return new SuperMarketResource($supermarket);
    }

    public function destroy(SuperMarket $supermarket)
    {
        $supermarket->delete();
        return response()->json(['message' => 'Deleted']);
    }
}
