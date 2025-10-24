<?php

namespace App\Http\Controllers\Api\fifthyformatfour;

use App\Http\Controllers\Controller;
use App\Http\Requests\fifthyformatfour\StoreUniversityRequest;
use App\Http\Requests\fifthyformatfour\UpdateUniversityRequest;
use App\Http\Resources\fifthyformatfour\UniversityResource;
use App\Models\fifthyformatfour\University;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class UniversityController extends Controller
{
    public function index(Request $request)
    {
        $query = University::query()
            ->with(['resource:id,name,code_number,phone_number']);

        if ($s = trim((string) $request->query('search', ''))) {
            $query->where(function (Builder $q) use ($s) {
                $q->where('uni_name','like',"%$s%")
                  ->orWhere('responsible_name','like',"%s%")
                  ->orWhere('responsible_phone','like',"%$s%")
                  ->orWhere('province','like',"%$s%");
            });
        }

        $perPage = $request->query('perPage');
        $page    = $request->query('page');

        $perPageInt = filter_var($perPage, FILTER_VALIDATE_INT, ['options'=>['min_range'=>1]]);
        $pageInt    = filter_var($page,    FILTER_VALIDATE_INT, ['options'=>['min_range'=>1]]);

        if ($perPageInt === false || $pageInt === false) {
            $rows = $query->orderByDesc('id')->get();
            return UniversityResource::collection($rows);
        }

        $rows = $query->orderByDesc('id')->paginate($perPageInt, ['*'], 'page', $pageInt);
        return UniversityResource::collection($rows);
    }

    public function store(StoreUniversityRequest $request)
    {
        $row = University::create($request->validated());
        $row->load(['resource:id,name,code_number,phone_number']);
        return new UniversityResource($row);
    }

    public function show(University $university)
    {
        $university->load(['resource:id,name,code_number,phone_number']);
        return new UniversityResource($university);
    }

    public function update(UpdateUniversityRequest $request, University $university)
    {
        $university->update($request->validated());
        $university->load(['resource:id,name,code_number,phone_number']);
        return new UniversityResource($university);
    }

    public function destroy(University $university)
    {
        $university->delete();
        return response()->json(['message' => 'Deleted']);
    }
}
