<?php

namespace App\Http\Controllers\fifthyformatone;

use App\Http\Controllers\Controller;
use App\Http\Requests\fifthyformatone\StoreMashkokHomesRequest;
use App\Http\Requests\fifthyformatone\UpdateMashkokHomesRequest;
use App\Http\Resources\fifthyformatone\MashkokHomesResource;
use App\Models\fifthyformatone\MashkokHomes;
use Illuminate\Http\Request;

class MashkokHomesController extends Controller
{

    public function index(Request $request)
    {
        $perPage = (int) $request->input('perPage', 10);
        $search  = $request->input('search');

        $q = MashkokHomes::query()
            ->with(['resource:id,name,code_number,phone_number']) // ⬅️ explicit
            ->latest();

        if ($search) {
            $q->where(function ($w) use ($search) {
                $w->where('province', 'like', "%{$search}%")
                ->orWhere('district', 'like', "%{$search}%")
                ->orWhere('village', 'like', "%{$search}%")
                ->orWhere('home_type', 'like', "%{$search}%");
            });
        }

        return MashkokHomesResource::collection($q->paginate($perPage));
    }

    public function store(StoreMashkokHomesRequest $request)
    {
        $record = MashkokHomes::create($request->validated())
            ->load(['resource:id,name,code_number,phone_number']); // ⬅️ explicit
        return MashkokHomesResource::make($record);
    }

    public function show(MashkokHomes $mashkok_home)
    {
        $mashkok_home->load(['resource:id,name,code_number,phone_number']); // ⬅️
        return MashkokHomesResource::make($mashkok_home);
    }

    public function update(UpdateMashkokHomesRequest $request, MashkokHomes $mashkok_home)
    {
        $mashkok_home->update($request->validated());
        $mashkok_home->load(['resource:id,name,code_number,phone_number']); // ⬅️
        return MashkokHomesResource::make($mashkok_home);
    }

}
