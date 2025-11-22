<?php

namespace App\Http\Controllers\Api\fifthyformatfour;

use App\Http\Controllers\Controller;
use App\Http\Requests\fifthyformatfour\StoreDonelostplaceRequest;
use App\Http\Requests\fifthyformatfour\UpdateDonelostplaceRequest;
use App\Http\Resources\fifthyformatfour\DonelostplaceResource;
use App\Models\fifthyformatfour\Donelostplace;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class DonelostplaceController extends Controller
{
    /**
     * GET /done-lost-places
     * Supports: ?search=...&perPage=...&page=...
     */
    public function index(Request $request)
    {
        $query = Donelostplace::query()
            ->with(['resource:id,name,code_number,phone_number']);

        if ($s = trim((string) $request->query('search', ''))) {
            $query->where(function (Builder $q) use ($s) {
                $q->where('place_name', 'like', "%{$s}%")
                  ->orWhere('lost_person_name', 'like', "%{$s}%")
                  ->orWhere('lost_person_phone', 'like', "%{$s}%")
                  ->orWhere('village', 'like', "%{$s}%");
            });
        }

        $perPage = $request->query('perPage');
        $page    = $request->query('page');

        $perPageInt = filter_var($perPage, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $pageInt    = filter_var($page,    FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

        // ✅ Same pattern as Highway/Mawad:
        // if perPage / page invalid or missing → return ALL rows, no pagination
        if ($perPageInt === false || $pageInt === false) {
            $rows = $query->orderByDesc('id')->get();

            return DonelostplaceResource::collection($rows);
        }

        $rows = $query
            ->orderByDesc('id')
            ->paginate($perPageInt, ['*'], 'page', $pageInt);

        return DonelostplaceResource::collection($rows);
    }

    /**
     * POST /done-lost-places
     */
    public function store(StoreDonelostplaceRequest $request)
    {
        /** @var \App\Models\fifthyformatfour\Donelostplace $row */
        $row = Donelostplace::create($request->validated());

        // ✅ eager-load resource so resource_code/resource_phone are filled in Resource
        $row->load(['resource:id,name,code_number,phone_number']);

        return new DonelostplaceResource($row);
    }

    /**
     * GET /done-lost-places/{done_lost_place}
     * Route model binding from apiResource('done-lost-places', ...)
     */
    public function show(Donelostplace $done_lost_place)
    {
        $done_lost_place->load(['resource:id,name,code_number,phone_number']);

        return new DonelostplaceResource($done_lost_place);
    }

    /**
     * PUT/PATCH /done-lost-places/{done_lost_place}
     */
    public function update(UpdateDonelostplaceRequest $request, Donelostplace $done_lost_place)
    {
        $done_lost_place->update($request->validated());

        // ✅ reload to refresh flattened resource_* fields
        $done_lost_place->load(['resource:id,name,code_number,phone_number']);

        return new DonelostplaceResource($done_lost_place);
    }

    /**
     * DELETE /done-lost-places/{done_lost_place}
     */
    public function destroy(Donelostplace $done_lost_place)
    {
        $done_lost_place->delete();

        return response()->json(['message' => 'Deleted']);
    }
}
