<?php

namespace App\Http\Controllers\fifthyformatone;

use App\Http\Controllers\Controller;
use App\Models\fifthyformatone\LostMujremeen;
use App\Http\Resources\fifthyformatone\LostMujremeenResource;
use App\Http\Requests\fifthyformatone\StoreLostMujremeenRequest;
use App\Http\Requests\fifthyformatone\UpdateLostMujremeenRequest;
use Illuminate\Http\Request;

class LostMujremeenController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) $request->input('perPage', 10);
        $search  = $request->input('search');

        $q = LostMujremeen::query()
            ->with(['resource:id,name,code_number,phone_number'])
            ->latest();

        if ($search) {
            $q->where(function ($qq) use ($search) {
                $qq->where('name', 'like', "%{$search}%")
                   ->orWhere('father_name', 'like', "%{$search}%")
                   ->orWhere('now_province', 'like', "%{$search}%")
                   ->orWhere('jorm', 'like', "%{$search}%");
            });
        }

        return LostMujremeenResource::collection($q->paginate($perPage));
    }

    public function store(StoreLostMujremeenRequest $request)
    {
        $item = LostMujremeen::create($request->validated())
            ->load(['resource:id,name,code_number,phone_number']);

        return LostMujremeenResource::make($item);
    }

    public function show(LostMujremeen $lostMujremeen)
    {
        $lostMujremeen->load(['resource:id,name,code_number,phone_number']);
        return LostMujremeenResource::make($lostMujremeen);
    }

    public function update(UpdateLostMujremeenRequest $request, LostMujremeen $lostMujremeen)
    {
        $lostMujremeen->update($request->validated());
        $lostMujremeen->load(['resource:id,name,code_number,phone_number']);

        return LostMujremeenResource::make($lostMujremeen);
    }

    public function destroy(LostMujremeen $lostMujremeen)
    {
        $lostMujremeen->delete();
        return response()->noContent();
    }

    public function bulkDelete(Request $request)
    {
        $ids = (array) $request->input('ids', []);
        LostMujremeen::destroy($ids);
        return response()->noContent();
    }
}
