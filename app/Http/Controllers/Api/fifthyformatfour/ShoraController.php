<?php

namespace App\Http\Controllers\Api\fifthyformatfour;

use App\Http\Controllers\Controller;
use App\Http\Requests\fifthyformatfour\StoreShoraRequest;
use App\Http\Requests\fifthyformatfour\UpdateShoraRequest;
use App\Http\Resources\fifthyformatfour\ShoraResource;
use App\Models\fifthyformatfour\Shora;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class ShoraController extends Controller
{
    public function index(Request $request)
    {
        $query = Shora::query()
            ->with(['resource:id,name,code_number,phone_number']);

        if ($s = trim((string) $request->query('search', ''))) {
            $query->where(function (Builder $q) use ($s) {
                $q->where('shora_name','like',"%$s%")
                  ->orWhere('responsible_name','like',"%$s%")
                  ->orWhere('shora_province','like',"%$s%")
                  ->orWhere('shora_village','like',"%$s%");
            });
        }

        $perPage = $request->query('perPage');
        $page    = $request->query('page');

        $perPageInt = filter_var($perPage, FILTER_VALIDATE_INT, ['options'=>['min_range'=>1]]);
        $pageInt    = filter_var($page,    FILTER_VALIDATE_INT, ['options'=>['min_range'=>1]]);

        // Fallback → return ALL when missing/invalid
        if ($perPageInt === false || $pageInt === false) {
            return ShoraResource::collection($query->orderByDesc('id')->get());
        }

        $rows = $query->orderByDesc('id')->paginate($perPageInt, ['*'], 'page', $pageInt);
        return ShoraResource::collection($rows);
    }

    public function store(StoreShoraRequest $request)
    {
        $row = Shora::create($request->validated());
        $row->load(['resource:id,name,code_number,phone_number']);
        return new ShoraResource($row);
    }

    public function show(Shora $shora)
    {
        $shora->load('resource');
        return ShoraResource::make($shora);
    }


    public function update(UpdateShoraRequest $request, Shora $shora)
    {
        $shora->update($request->validated());
        $shora->load(['resource:id,name,code_number,phone_number']);
        return new ShoraResource($shora);
    }

    public function destroy(Shora $shora)
    {
        $shora->delete();
        return response()->json(['message' => 'Deleted']);
    }
}
