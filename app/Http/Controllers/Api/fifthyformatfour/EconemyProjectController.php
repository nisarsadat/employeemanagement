<?php

namespace App\Http\Controllers\Api\fifthyformatfour;

use App\Http\Controllers\Controller;
use App\Http\Requests\fifthyformatfour\StoreEconemyProjectRequest;
use App\Http\Requests\fifthyformatfour\UpdateEconemyProjectRequest;
use App\Http\Resources\fifthyformatfour\EconemyProjectResource;
use App\Models\fifthyformatfour\EconemyProject;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class EconemyProjectController extends Controller
{
    public function index(Request $request)
    {
        $query = EconemyProject::query()
            ->with(['resource:id,name,code_number,phone_number']);

        if ($s = trim((string) $request->query('search', ''))) {
            $query->where(function (Builder $q) use ($s) {
                $q->where('name','like',"%$s%")
                  ->orWhere('responsiable_name','like',"%$s%")
                  ->orWhere('province','like',"%$s%")
                  ->orWhere('district','like',"%$s%");
            });
        }

        $perPage = $request->query('perPage');
        $page    = $request->query('page');

        $perPageInt = filter_var($perPage, FILTER_VALIDATE_INT, ['options'=>['min_range'=>1]]);
        $pageInt    = filter_var($page,    FILTER_VALIDATE_INT, ['options'=>['min_range'=>1]]);

        // Fallback → return ALL when missing/invalid
        if ($perPageInt === false || $pageInt === false) {
            return EconemyProjectResource::collection($query->orderByDesc('id')->get());
        }

        $rows = $query->orderByDesc('id')->paginate($perPageInt, ['*'], 'page', $pageInt);
        return EconemyProjectResource::collection($rows);
    }

    public function store(StoreEconemyProjectRequest $request)
    {
        $row = EconemyProject::create($request->validated());
        $row->load(['resource:id,name,code_number,phone_number']);
        return new EconemyProjectResource($row);
    }

    public function show(EconemyProject $econemyProject)
    {
        $econemyProject->load(['resource:id,name,code_number,phone_number']);
        return new EconemyProjectResource($econemyProject);
    }

    public function update(UpdateEconemyProjectRequest $request, EconemyProject $econemyProject)
    {
        $econemyProject->update($request->validated());
        $econemyProject->load(['resource:id,name,code_number,phone_number']);
        return new EconemyProjectResource($econemyProject);
    }

    public function destroy(EconemyProject $econemyProject)
    {
        $econemyProject->delete();
        return response()->json(['message' => 'Deleted']);
    }
}
