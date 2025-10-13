<?php

namespace App\Http\Controllers\fifthyformatstow;

use App\Http\Controllers\Controller;
use App\Http\Requests\fifthyformatstow\StoreParkingRequest;
use App\Http\Requests\fifthyformatstow\UpdateParkingRequest;
use App\Http\Resources\fifthyformatstow\ParkingResource;
use App\Models\fifthyformatstow\Parking;
use Illuminate\Http\Request;

class ParkingController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) $request->input('perPage', 10);
        $search  = $request->input('search');

        $q = Parking::query()
            ->with(['resource:id,name,code_number,phone_number'])
            ->latest();

        if ($search) {
            $q->where(function ($qq) use ($search) {
                $qq->where('parking_name', 'like', "%{$search}%")
                   ->orWhere('responsible_name', 'like', "%{$search}%")
                   ->orWhere('responsible_phone', 'like', "%{$search}%")
                   ->orWhere('province', 'like', "%{$search}%")
                   ->orWhere('district', 'like', "%{$search}%");
            });
        }

        return ParkingResource::collection($q->paginate($perPage));
    }

    public function store(StoreParkingRequest $request)
    {
        $parking = Parking::create($request->validated())
            ->load(['resource:id,name,code_number,phone_number']);

        return ParkingResource::make($parking);
    }

    public function show(Parking $parking)
    {
        $parking->load(['resource:id,name,code_number,phone_number']);
        return ParkingResource::make($parking);
    }

    public function update(UpdateParkingRequest $request, Parking $parking)
    {
        $parking->update($request->validated());
        $parking->load(['resource:id,name,code_number,phone_number']);

        return ParkingResource::make($parking);
    }

    public function destroy(Parking $parking)
    {
        $parking->delete();
        return response()->noContent();
    }

    public function bulkDelete(Request $request)
    {
        $ids = $request->input('ids', []);
        Parking::destroy($ids);
        return response()->noContent();
    }
}
