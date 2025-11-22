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

        // 🔍 Search
        if ($s = trim((string) $request->query('search', ''))) {
            $query->where(function (Builder $q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                  ->orWhere('responsiable_name', 'like', "%{$s}%")
                  ->orWhere('province', 'like', "%{$s}%")
                  ->orWhere('district', 'like', "%{$s}%");
            });
        }

        // 📄 Pagination (supports itemsPerPage from Vue + perPage fallback)
        $perPageRaw = $request->query('itemsPerPage', $request->query('perPage'));
        $pageRaw    = $request->query('page');

        $perPageInt = filter_var($perPageRaw, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $pageInt    = filter_var($pageRaw,    FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

        // If no valid pagination → return ALL (for your 100000 pattern)
        if ($perPageInt === false || $pageInt === false) {
            $rows = $query->orderByDesc('id')->get();
            return EconemyProjectResource::collection($rows);
        }

        // Normal paginated response
        $rows = $query
            ->orderByDesc('id')
            ->paginate($perPageInt, ['*'], 'page', $pageInt);

        return EconemyProjectResource::collection($rows);
    }

    public function store(StoreEconemyProjectRequest $request)
    {
        $row = EconemyProject::create($request->validated());

        // eager-load minimal resource for frontend
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

        return response()->json([
            'message' => 'Deleted',
        ]);
    }
}
