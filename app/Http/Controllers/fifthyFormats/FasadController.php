<?php

namespace App\Http\Controllers\fifthyformats;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\fifthyFormats\Fasad;
use App\Http\Resources\fifthyformats\FasadResource;
use App\Http\Requests\fifthyformats\StoreFasadRequest;
use App\Http\Requests\fifthyformats\UpdateFasadRequest;

class FasadController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) $request->input('perPage', 10);
        $search  = $request->input('search');

        $q = Fasad::query()
            ->with(['resource:id,name,code_number,phone_number'])
            ->latest();

        if ($search) {
            $q->where('name', 'like', "%{$search}%");
            // If you want broader search (optional):
            // $q->orWhere('father_name','like',"%{$search}%")
            //   ->orWhere('nick_name','like',"%{$search}%");
        }

        return FasadResource::collection($q->paginate($perPage));
    }

    public function store(StoreFasadRequest $request)
    {
        $fasad = Fasad::create($request->validated())
            ->load(['resource:id,name,code_number,phone_number']);

        return FasadResource::make($fasad);
    }

    public function show(Fasad $fasad)
    {
        $fasad->load(['resource:id,name,code_number,phone_number']);
        return FasadResource::make($fasad);
    }

    public function update(UpdateFasadRequest $request, Fasad $fasad)
    {
        $fasad->update($request->validated());
        $fasad->load(['resource:id,name,code_number,phone_number']);

        return FasadResource::make($fasad);
    }

    public function destroy(Fasad $fasad)
    {
        $fasad->delete();
        return response()->noContent();
    }

    public function bulkDelete(Request $request)
    {
        $ids = $request->input('ids', []);
        Fasad::destroy($ids);
        return response()->noContent();
    }
}
