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
    public function index(Request $request)
    {
        $query = Donelostplace::query()
            ->with(['resource:id,name,code_number,phone_number']);

        if ($s = trim((string) $request->query('search', ''))) {
            $query->where(function (Builder $q) use ($s) {
                $q->where('place_name','like',"%$s%")
                  ->orWhere('lost_person_name','like',"%$s%")
                  ->orWhere('lost_person_phone','like',"%$s%")
                  ->orWhere('village','like',"%$s%");
            });
        }

        $perPage = $request->query('perPage');
        $page    = $request->query('page');

        $perPageInt = filter_var($perPage, FILTER_VALIDATE_INT, ['options'=>['min_range'=>1]]);
        $pageInt    = filter_var($page,    FILTER_VALIDATE_INT, ['options'=>['min_range'=>1]]);

        // Fallback → return ALL when missing/invalid
        if ($perPageInt === false || $pageInt === false) {
            return DonelostplaceResource::collection($query->orderByDesc('id')->get());
        }

        $rows = $query->orderByDesc('id')->paginate($perPageInt, ['*'], 'page', $pageInt);
        return DonelostplaceResource::collection($rows);
    }

    public function store(StoreDonelostplaceRequest $request)
    {
        $row = Donelostplace::create($request->validated());
        $row->load(['resource:id,name,code_number,phone_number']);
        return new DonelostplaceResource($row);
    }

    public function show(Donelostplace $donelostplace)
    {
        $donelostplace->load(['resource:id,name,code_number,phone_number']);
        return new DonelostplaceResource($donelostplace);
    }

    public function update(UpdateDonelostplaceRequest $request, Donelostplace $donelostplace)
    {
        $donelostplace->update($request->validated());
        $donelostplace->load(['resource:id,name,code_number,phone_number']);
        return new DonelostplaceResource($donelostplace);
    }

    public function destroy(Donelostplace $donelostplace)
    {
        $donelostplace->delete();
        return response()->json(['message' => 'Deleted']);
    }
}
