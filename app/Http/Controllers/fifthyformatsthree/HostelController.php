<?php

namespace App\Http\Controllers\fifthyformatsthree;

use App\Http\Controllers\Controller;
use App\Models\fifthyformatsthree\Hostel;
use App\Http\Resources\fifthyformatsthree\HostelResource;
use App\Http\Requests\fifthyformatsthree\StoreHostelRequest;
use App\Http\Requests\fifthyformatsthree\UpdateHostelRequest;
use Illuminate\Http\Request;

class HostelController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) $request->input('perPage', 10);
        $search  = $request->input('search');

        $q = Hostel::query()
            ->with(['resource:id,name,code_number,phone_number'])
            ->latest();

        if ($search) {
            $q->where(function ($qq) use ($search) {
                $qq->where('hostel_name', 'like', "%{$search}%")
                   ->orWhere('responsible_name', 'like', "%{$search}%")
                   ->orWhere('province', 'like', "%{$search}%");
            });
        }

        return HostelResource::collection($q->paginate($perPage));
    }

    public function store(StoreHostelRequest $request)
    {
        $item = Hostel::create($request->validated())
            ->load(['resource:id,name,code_number,phone_number']);

        return HostelResource::make($item);
    }

    public function show(Hostel $hostel)
    {
        $hostel->load(['resource:id,name,code_number,phone_number']);
        return HostelResource::make($hostel);
    }

    public function update(UpdateHostelRequest $request, Hostel $hostel)
    {
        $hostel->update($request->validated());
        $hostel->load(['resource:id,name,code_number,phone_number']);

        return HostelResource::make($hostel);
    }

    public function destroy(Hostel $hostel)
    {
        $hostel->delete();
        return response()->noContent();
    }

    public function bulkDelete(Request $request)
    {
        $ids = (array) $request->input('ids', []);
        Hostel::destroy($ids);
        return response()->noContent();
    }
}
