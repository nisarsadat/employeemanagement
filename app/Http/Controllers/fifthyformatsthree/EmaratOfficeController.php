<?php

namespace App\Http\Controllers\fifthyformatsthree;

use App\Http\Controllers\Controller;
use App\Models\fifthyformatsthree\EmaratOffice;
use App\Http\Resources\fifthyformatsthree\EmaratOfficeResource;
use App\Http\Requests\fifthyformatsthree\StoreEmaratOfficeRequest;
use App\Http\Requests\fifthyformatsthree\UpdateEmaratOfficeRequest;
use Illuminate\Http\Request;

class EmaratOfficeController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) $request->input('perPage', 10);
        $search  = $request->input('search');

        $q = EmaratOffice::query()
            ->with(['resource:id,name,code_number,phone_number'])
            ->latest();

        if ($search) {
            $q->where(function ($qq) use ($search) {
                $qq->where('province', 'like', "%{$search}%")
                   ->orWhere('district', 'like', "%{$search}%")
                   ->orWhere('responsible_office', 'like', "%{$search}%");
            });
        }

        return EmaratOfficeResource::collection($q->paginate($perPage));
    }

    public function store(StoreEmaratOfficeRequest $request)
    {
        $item = EmaratOffice::create($request->validated())
            ->load(['resource:id,name,code_number,phone_number']);

        return EmaratOfficeResource::make($item);
    }

    public function show(EmaratOffice $emaratOffice)
    {
        $emaratOffice->load(['resource:id,name,code_number,phone_number']);
        return EmaratOfficeResource::make($emaratOffice);
    }

    public function update(UpdateEmaratOfficeRequest $request, EmaratOffice $emaratOffice)
    {
        $emaratOffice->update($request->validated());
        $emaratOffice->load(['resource:id,name,code_number,phone_number']);

        return EmaratOfficeResource::make($emaratOffice);
    }

    public function destroy(EmaratOffice $emaratOffice)
    {
        $emaratOffice->delete();
        return response()->noContent();
    }

    public function bulkDelete(Request $request)
    {
        $ids = (array) $request->input('ids', []);
        EmaratOffice::destroy($ids);
        return response()->noContent();
    }
}
