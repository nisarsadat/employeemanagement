<?php

namespace App\Http\Controllers\Api\fifthyformatfour;

use App\Http\Controllers\Controller;
use App\Http\Requests\fifthyformatfour\StoreComplexRequest;
use App\Http\Requests\fifthyformatfour\UpdateComplexRequest;
use App\Http\Resources\fifthyformatfour\ComplexResource;
use App\Models\fifthyformatfour\Complex;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class ComplexController extends Controller
{
    public function index(Request $request)
    {
        $query = Complex::query()->with(['resource:id,name,code_number,phone_number']);

        if ($s = trim((string) $request->query('search', ''))) {
            $query->where(function (Builder $q) use ($s) {
                $q->where('complex_name','like',"%$s%")
                  ->orWhere('province','like',"%$s%")
                  ->orWhere('district','like',"%$s%")
                  ->orWhere('village','like',"%$s%");
            });
        }

        $perPage = $request->query('perPage');
        $page    = $request->query('page');
        $perPageInt = filter_var($perPage, FILTER_VALIDATE_INT, ['options'=>['min_range'=>1]]);
        $pageInt    = filter_var($page,    FILTER_VALIDATE_INT, ['options'=>['min_range'=>1]]);

        // Fallback → return ALL when invalid/missing
        if ($perPageInt === false || $pageInt === false) {
            return ComplexResource::collection($query->orderByDesc('id')->get());
        }

        $rows = $query->orderByDesc('id')->paginate($perPageInt, ['*'], 'page', $pageInt);
        return ComplexResource::collection($rows);
    }

    public function store(StoreComplexRequest $request)
    {
        $row = Complex::create($request->validated());
        $row->load(['resource:id,name,code_number,phone_number']);
        return new ComplexResource($row);
    }

    public function show(Complex $complex)
    {
        $complex->load('resource');
        return new ComplexResource($complex);
    }

    public function update(UpdateComplexRequest $request, Complex $complex)
    {
        $complex->update($request->validated());
        $complex->load(['resource:id,name,code_number,phone_number']);
        return new ComplexResource($complex);
    }

    public function destroy(Complex $complex)
    {
        $complex->delete();
        return response()->json(['message' => 'Deleted']);
    }
}
