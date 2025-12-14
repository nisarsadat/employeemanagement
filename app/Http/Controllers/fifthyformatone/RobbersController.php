<?php

namespace App\Http\Controllers\fifthyformatone;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\fifthyformatone\Robbers;
use App\Http\Resources\fifthyformatone\RobbersResource;
use App\Http\Requests\fifthyformatone\StoreRobbersRequest;
use App\Http\Requests\fifthyformatone\UpdateRobbersRequest;

class RobbersController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) $request->get('perPage', 15);
        $search  = $request->get('search');

        $query = Robbers::query()
            ->with(['resource:id,name,code_number,phone_number'])
            ->latest();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('nickname', 'like', "%{$search}%")
                  ->orWhere('father_name', 'like', "%{$search}%")
                  ->orWhere('phone_number', 'like', "%{$search}%");
            });
        }

        return RobbersResource::collection(
            $query->paginate($perPage)
        );
    }

    public function store(StoreRobbersRequest $request)
    {
        $robber = Robbers::create($request->validated());

        $robber->load(['resource:id,name,code_number,phone_number']);

        return RobbersResource::make($robber);
    }

    /**
     * IMPORTANT:
     * This endpoint must ONLY be called with a VALID ID
     */
    public function show($id)
    {
        $robber = Robbers::with(['resource:id,name,code_number,phone_number'])
            ->findOrFail($id);

        return RobbersResource::make($robber);
    }

    public function update(UpdateRobbersRequest $request, $id)
    {
        $robber = Robbers::findOrFail($id);

        $robber->update($request->validated());

        $robber->load(['resource:id,name,code_number,phone_number']);

        return RobbersResource::make($robber);
    }

    public function destroy($id)
    {
        Robbers::findOrFail($id)->delete();

        return response()->noContent();
    }
}
