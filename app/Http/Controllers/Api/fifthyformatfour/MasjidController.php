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
    public function index(Request $request)
    {
        $query = Masjid::query()->with(['resource:id,name,code_number,phone_number']);

        if ($s = trim((string) $request->query('search', ''))) {
            $query->where(function (Builder $q) use ($s) {
                $q->where('masjid_name','like',"%$s%")
                  ->orWhere('responsible_name','like',"%$s%")
                  ->orWhere('responsible_f_name','like',"%$s%")
                  ->orWhere('masjid_village','like',"%$s%");
            });
        }

        $perPage = $request->query('perPage');
        $page    = $request->query('page');

        $perPageInt = filter_var($perPage, FILTER_VALIDATE_INT, ['options'=>['min_range'=>1]]);
        $pageInt    = filter_var($page,    FILTER_VALIDATE_INT, ['options'=>['min_range'=>1]]);

        if ($perPageInt === false || $pageInt === false) {
            return MasjidResource::collection($query->orderByDesc('id')->get());
        }

        $rows = $query->orderByDesc('id')->paginate($perPageInt, ['*'], 'page', $pageInt);
        return MasjidResource::collection($rows);
    }

    public function store(StoreMasjidRequest $request)
    {
        $row = Masjid::create($request->validated());
        $row->load(['resource:id,name,code_number,phone_number']);
        return new MasjidResource($row);
    }

    public function show(Masjid $masjid)
    {
        $masjid->load(['resource:id,name,code_number,phone_number']);
        return new MasjidResource($masjid);
    }

    public function update(UpdateMasjidRequest $request, Masjid $masjid)
    {
        $masjid->update($request->validated());
        $masjid->load(['resource:id,name,code_number,phone_number']);
        return new MasjidResource($masjid);
    }

    public function destroy(Masjid $masjid)
    {
        $masjid->delete();
        return response()->json(['message' => 'Deleted']);
    }
}
