<?php

namespace App\Http\Controllers\fifthyformatsthree;

use App\Http\Controllers\Controller;
use App\Models\fifthyformatsthree\Hotel;
use App\Http\Resources\fifthyformatsthree\HotelResource;
use App\Http\Requests\fifthyformatsthree\StoreHotelRequest;
use App\Http\Requests\fifthyformatsthree\UpdateHotelRequest;
use Illuminate\Http\Request;

class HotelController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) $request->input('perPage', 10);
        $search  = $request->input('search');

        $q = Hotel::query()
            ->with(['resource:id,name,code_number,phone_number'])
            ->latest();

        if ($search) {
            $q->where(function ($qq) use ($search) {
                $qq->where('hotel_name', 'like', "%{$search}%")
                   ->orWhere('responsible_name', 'like', "%{$search}%")
                   ->orWhere('province', 'like', "%{$search}%");
            });
        }

        return HotelResource::collection($q->paginate($perPage));
    }

    public function store(StoreHotelRequest $request)
    {
        $hotel = Hotel::create($request->validated())
            ->load(['resource:id,name,code_number,phone_number']);

        return HotelResource::make($hotel);
    }

    public function show(Hotel $hotel)
    {
        $hotel->load(['resource:id,name,code_number,phone_number']);
        return HotelResource::make($hotel);
    }

    public function update(UpdateHotelRequest $request, Hotel $hotel)
    {
        $hotel->update($request->validated());
        $hotel->load(['resource:id,name,code_number,phone_number']);

        return HotelResource::make($hotel);
    }

    public function destroy(Hotel $hotel)
    {
        $hotel->delete();
        return response()->noContent();
    }

    public function bulkDelete(Request $request)
    {
        $ids = (array) $request->input('ids', []);
        Hotel::destroy($ids);
        return response()->noContent();
    }
}
