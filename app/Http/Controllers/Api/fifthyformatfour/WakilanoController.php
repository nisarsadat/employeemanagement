<?php

namespace App\Http\Controllers\Api\fifthyformatfour;

use App\Http\Controllers\Controller;
use App\Http\Requests\fifthyformatfour\StoreWakilanoRequest;
use App\Http\Requests\fifthyformatfour\UpdateWakilanoRequest;
use App\Http\Resources\fifthyformatfour\WakilanoResource;
use App\Models\fifthyformatfour\Wakilano;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class WakilanoController extends Controller
{
    /**
     * GET /api/wakilanos
     * - Eager-load resource:id,name,code_number,phone_number
     * - Search (optional): ?search=...
     * - Pagination: if perPage/page missing or invalid => return ALL (no pagination)
     */
    public function index(Request $request)
    {
        $query = Wakilano::query()
            ->with(['resource:id,name,code_number,phone_number']);

        // Simple search
        if ($s = trim((string) $request->query('search', ''))) {
            $query->where(function (Builder $q) use ($s) {
                $q->where('name', 'like', "%$s%")
                  ->orWhere('f_name', 'like', "%$s%")
                  ->orWhere('grand_father_name', 'like', "%$s%")
                  ->orWhere('phone_number', 'like', "%$s%")
                  ->orWhere('now_village', 'like', "%$s%");
            });
        }

        // Validate pagination inputs
        $perPage = $request->query('perPage');
        $page    = $request->query('page');

        $perPageInt = filter_var($perPage, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $pageInt    = filter_var($page,    FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

        // Fallback → return ALL if inputs are missing or invalid
        if ($perPageInt === false || $pageInt === false) {
            $rows = $query->orderByDesc('id')->get();
            return WakilanoResource::collection($rows);
        }

        // Paginated path
        $rows = $query->orderByDesc('id')->paginate($perPageInt, ['*'], 'page', $pageInt);

        return WakilanoResource::collection($rows);
    }

    public function store(StoreWakilanoRequest $request)
    {
        $row = Wakilano::create($request->validated());
        $row->load(['resource:id,name,code_number,phone_number']);

        return new WakilanoResource($row);
    }

    public function show(Wakilano $wakilano)
    {
        $wakilano->load(['resource:id,name,code_number,phone_number']);
        return new WakilanoResource($wakilano);
    }

    public function update(UpdateWakilanoRequest $request, Wakilano $wakilano)
    {
        $wakilano->update($request->validated());
        $wakilano->load(['resource:id,name,code_number,phone_number']);

        return new WakilanoResource($wakilano);
    }

    public function destroy(Wakilano $wakilano)
    {
        $wakilano->delete();
        return response()->json(['message' => 'Deleted']);
    }
}
