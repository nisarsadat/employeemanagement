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
        $perPage = (int) $request->input('perPage', 10);
        $search  = $request->input('search');

        $q = Robbers::query()
            ->with(['resource:id,name,code_number,phone_number']) // ⬅ include needed cols
            ->latest();

        if ($search) {
            $q->where(function ($w) use ($search) {
                $w->where('name', 'like', "%{$search}%")
                ->orWhere('nickname', 'like', "%{$search}%")
                ->orWhere('father_name', 'like', "%{$search}%")
                ->orWhere('phone_number', 'like', "%{$search}%");
            });
        }

        return RobbersResource::collection($q->paginate($perPage));
    }

    public function store(StoreRobbersRequest $request)
    {
        $record = Robbers::create($request->validated())
            ->load(['resource:id,name,code_number,phone_number']); // ⬅
        return RobbersResource::make($record);
    }

    public function show(Robbers $robbers)
    {
        $robbers->load(['resource:id,name,code_number,phone_number']); // ⬅
        return RobbersResource::make($robbers);
    }

    public function update(UpdateRobbersRequest $request, Robbers $robbers)
    {
        $robbers->update($request->validated());
        $robbers->load(['resource:id,name,code_number,phone_number']); // ⬅
        return RobbersResource::make($robbers);
    }

}
