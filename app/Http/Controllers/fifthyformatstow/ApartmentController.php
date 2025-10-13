<?php

namespace App\Http\Controllers\fifthyformatstow;

use App\Http\Controllers\Controller;
use App\Http\Requests\fifthyformatstow\StoreApartmentRequest;
use App\Http\Requests\fifthyformatstow\UpdateApartmentRequest;
use App\Http\Resources\fifthyformatstow\ApartmentResource;
use App\Models\fifthyformatstow\Apartment;
use Illuminate\Http\Request;

class ApartmentController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) $request->input('perPage', 10);
        $search  = $request->input('search');

        $q = Apartment::query()
            ->with(['resource:id,name,code_number,phone_number'])
            ->latest();

        if ($search) {
            $q->where(function ($qq) use ($search) {
                $qq->where('block_name', 'like', "%{$search}%")
                   ->orWhere('shahrak_name', 'like', "%{$search}%")
                   ->orWhere('responsible_name', 'like', "%{$search}%")
                   ->orWhere('responsible_phone', 'like', "%{$search}%")
                   ->orWhere('province', 'like', "%{$search}%")
                   ->orWhere('district', 'like', "%{$search}%");
            });
        }

        return ApartmentResource::collection($q->paginate($perPage));
    }

    public function store(StoreApartmentRequest $request)
    {
        $apartment = Apartment::create($request->validated())
            ->load(['resource:id,name,code_number,phone_number']);

        return ApartmentResource::make($apartment);
    }

    public function show(Apartment $apartment)
    {
        $apartment->load(['resource:id,name,code_number,phone_number']);
        return ApartmentResource::make($apartment);
    }

    public function update(UpdateApartmentRequest $request, Apartment $apartment)
    {
        $apartment->update($request->validated());
        $apartment->load(['resource:id,name,code_number,phone_number']);

        return ApartmentResource::make($apartment);
    }

    public function destroy(Apartment $apartment)
    {
        $apartment->delete();
        return response()->noContent();
    }

    public function bulkDelete(Request $request)
    {
        $ids = $request->input('ids', []);
        Apartment::destroy($ids);
        return response()->noContent();
    }
}
