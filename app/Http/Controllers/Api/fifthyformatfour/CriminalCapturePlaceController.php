<?php

namespace App\Http\Controllers\Api\fifthyformatfour;

use App\Http\Controllers\Controller;
use App\Http\Requests\fifthyformatfour\StoreCriminalCapturePlaceRequest;
use App\Http\Requests\fifthyformatfour\UpdateCriminalCapturePlaceRequest;
use App\Http\Resources\fifthyformatfour\CriminalCapturePlaceResource;
use App\Models\fifthyformatfour\CriminalCapturePlace;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class CriminalCapturePlaceController extends Controller
{
    public function index(Request $request)
    {
        $query = CriminalCapturePlace::query()
            ->with(['resource:id,name,code_number,phone_number']);

        if ($s = trim((string) $request->query('search', ''))) {
            $query->where(function (Builder $q) use ($s) {
                $q->where('name','like',"%$s%")
                  ->orWhere('father_name','like',"%$s%")
                  ->orWhere('office','like',"%$s%")
                  ->orWhere('village','like',"%$s%");
            });
        }

        $perPage = $request->query('perPage');
        $page    = $request->query('page');
        $perPageInt = filter_var($perPage, FILTER_VALIDATE_INT, ['options'=>['min_range'=>1]]);
        $pageInt    = filter_var($page,    FILTER_VALIDATE_INT, ['options'=>['min_range'=>1]]);

        // Fallback → return ALL when missing/invalid
        if ($perPageInt === false || $pageInt === false) {
            return CriminalCapturePlaceResource::collection($query->orderByDesc('id')->get());
        }

        $rows = $query->orderByDesc('id')->paginate($perPageInt, ['*'], 'page', $pageInt);
        return CriminalCapturePlaceResource::collection($rows);
    }

    public function store(StoreCriminalCapturePlaceRequest $request)
    {
        $row = CriminalCapturePlace::create($request->validated());
        $row->load(['resource:id,name,code_number,phone_number']);
        return new CriminalCapturePlaceResource($row);
    }

    public function show(CriminalCapturePlace $criminalCapturePlace)
    {
        $criminalCapturePlace->load(['resource:id,name,code_number,phone_number']);
        return new CriminalCapturePlaceResource($criminalCapturePlace);
    }

    public function update(UpdateCriminalCapturePlaceRequest $request, CriminalCapturePlace $criminalCapturePlace)
    {
        $criminalCapturePlace->update($request->validated());
        $criminalCapturePlace->load(['resource:id,name,code_number,phone_number']);
        return new CriminalCapturePlaceResource($criminalCapturePlace);
    }

    public function destroy(CriminalCapturePlace $criminalCapturePlace)
    {
        $criminalCapturePlace->delete();
        return response()->json(['message' => 'Deleted']);
    }
}
