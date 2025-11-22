<?php

namespace App\Http\Controllers\Api\fifthyformatfour;

use App\Http\Controllers\Controller;
use App\Http\Requests\fifthyformatfour\StoreMasjidRequest;
use App\Http\Requests\fifthyformatfour\UpdateMasjidRequest;
use App\Http\Resources\fifthyformatfour\MasjidResource;
use App\Models\fifthyformatfour\Masjid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class MasjidController extends Controller
{
    /**
     * GET /masjids
     */
    public function index(Request $request)
    {
        $query = Masjid::query()
            ->with(['resource:id,name,code_number,phone_number']);

        if ($s = trim((string) $request->query('search', ''))) {
            $query->where(function (Builder $q) use ($s) {
                $q->where('masjid_name', 'like', "%$s%")
                  ->orWhere('responsible_name', 'like', "%$s%")
                  ->orWhere('responsible_f_name', 'like', "%$s%")
                  ->orWhere('masjid_village', 'like', "%$s%");
            });
        }

        $perPage = $request->query('perPage');
        $page    = $request->query('page');

        $perPageInt = filter_var($perPage, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $pageInt    = filter_var($page,    FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

        if ($perPageInt === false || $pageInt === false) {
            $rows = $query->orderByDesc('id')->get();
            return MasjidResource::collection($rows);
        }

        $rows = $query
            ->orderByDesc('id')
            ->paginate($perPageInt, ['*'], 'page', $pageInt);

        return MasjidResource::collection($rows);
    }

    /**
     * POST /masjids
     */
    public function store(StoreMasjidRequest $request)
    {
        // Only fields from masjids table (including resource_id)
        $data = $request->validated();

        $row = Masjid::create($data);

        // Eager-load resource so resource_code/resource_phone flatten correctly
        $row->load(['resource:id,name,code_number,phone_number']);

        return new MasjidResource($row);
    }

    /**
     * GET /masjids/{masjid}
     */
    public function show(Masjid $masjid)
    {
        $masjid->load(['resource:id,name,code_number,phone_number']);

        return new MasjidResource($masjid);
    }

    /**
     * PUT /masjids/{masjid}
     */
    public function update(UpdateMasjidRequest $request, Masjid $masjid)
    {
        // 1️⃣ Update Masjid fields (including resource_id)
        $data = $request->validated();
        $masjid->update($data);

        // 2️⃣ (Optional) update linked Resource code/phone if frontend sends them
        $resourceCode  = $request->input('resource_code');   // not in validated(), extra field
        $resourcePhone = $request->input('resource_phone');  // same

        if (($resourceCode !== null || $resourcePhone !== null) && $masjid->resource) {
            $updates = [];

            if ($resourceCode !== null) {
                $updates['code_number'] = $resourceCode;
            }
            if ($resourcePhone !== null) {
                $updates['phone_number'] = $resourcePhone;
            }

            if (!empty($updates)) {
                $masjid->resource->update($updates);
            }
        }

        // 3️⃣ Reload with fresh relation so resource_code/resource_phone are up-to-date
        $masjid->load(['resource:id,name,code_number,phone_number']);

        return new MasjidResource($masjid);
    }

    /**
     * DELETE /masjids/{masjid}
     */
    public function destroy(Masjid $masjid)
    {
        $masjid->delete();

        return response()->json(['message' => 'Deleted']);
    }
}
