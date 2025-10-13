<?php

namespace App\Http\Controllers\fifthyformatone;

use App\Http\Controllers\Controller;
use App\Http\Requests\fifthyformatone\StoreRobbersRequest;
use App\Http\Requests\fifthyformatone\UpdateRobbersRequest;
use App\Http\Resources\fifthyformatone\RobbersResource;
use App\Models\fifthyformatone\Robbers;
use Illuminate\Http\Request;

class RobbersController extends Controller
{
    /** GET /robbers */
    public function index(Request $request)
    {
        $perPage = (int) $request->input('perPage', 10);
        $search  = $request->input('search');

        $q = Robbers::query()
            ->with(['resource:id,name,code_number,phone_number'])
            ->latest();

        if ($search) {
            $q->where(function ($w) use ($search) {
                $w->where('name', 'like', "%{$search}%")
                  ->orWhere('nickname', 'like', "%{$search}%")
                  ->orWhere('father_name', 'like', "%{$search}%")
                  ->orWhere('phone_number', 'like', "%{$search}%")
                  ->orWhere('now_province', 'like', "%{$search}%")
                  ->orWhere('main_province', 'like', "%{$search}%")
                  ->orWhere('now_district', 'like', "%{$search}%");
            });
        }

        return RobbersResource::collection($q->paginate($perPage));
    }

    /** POST /robbers */
    public function store(StoreRobbersRequest $request)
    {
        $data = $request->validated();

        $item = Robbers::create($data)
            ->load(['resource:id,name,code_number,phone_number']);

        return RobbersResource::make($item);
    }

    /** GET /robbers/{robber} */
    public function show(Robbers $robber)
    {
        $robber->load(['resource:id,name,code_number,phone_number']);
        return RobbersResource::make($robber);
    }

    /** PUT /robbers/{robber} */
    public function update(UpdateRobbersRequest $request, Robbers $robber)
    {
        $data = $request->validated();

        $robber->update($data);
        $robber->load(['resource:id,name,code_number,phone_number']);

        return RobbersResource::make($robber);
    }

    /** DELETE /robbers/{robber} */
    public function destroy(Robbers $robber)
    {
        $robber->delete();
        return response()->noContent();
    }

    /** POST /robbers/bulk-delete { "ids": [1,2,3] } */
    public function bulkDelete(Request $request)
    {
        $ids = $request->input('ids', []);
        Robbers::destroy($ids);
        return response()->noContent();
    }
}
