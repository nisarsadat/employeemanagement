<?php

namespace App\Http\Controllers\fifthyformatsthree;

use App\Http\Controllers\Controller;
use App\Models\fifthyformatsthree\Sarayano;
use App\Http\Resources\fifthyformatsthree\SarayanoResource;
use App\Http\Requests\fifthyformatsthree\StoreSarayanoRequest;
use App\Http\Requests\fifthyformatsthree\UpdateSarayanoRequest;
use Illuminate\Http\Request;

class SarayanoController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) $request->input('perPage', 10);
        $search  = $request->input('search');

        $q = Sarayano::query()
            ->with(['resource:id,name,code_number,phone_number'])
            ->latest();

        if ($search) {
            $q->where(function ($qq) use ($search) {
                $qq->where('saray_name', 'like', "%{$search}%")
                   ->orWhere('responsible_name', 'like', "%{$search}%")
                   ->orWhere('province', 'like', "%{$search}%");
            });
        }

        return SarayanoResource::collection($q->paginate($perPage));
    }

    public function store(StoreSarayanoRequest $request)
    {
        $sarayano = Sarayano::create($request->validated())
            ->load(['resource:id,name,code_number,phone_number']);

        return SarayanoResource::make($sarayano);
    }

    public function show(Sarayano $sarayano)
    {
        $sarayano->load(['resource:id,name,code_number,phone_number']);
        return SarayanoResource::make($sarayano);
    }

    public function update(UpdateSarayanoRequest $request, Sarayano $sarayano)
    {
        $sarayano->update($request->validated());
        $sarayano->load(['resource:id,name,code_number,phone_number']);

        return SarayanoResource::make($sarayano);
    }

    public function destroy(Sarayano $sarayano)
    {
        $sarayano->delete();
        return response()->noContent();
    }

    public function bulkDelete(Request $request)
    {
        $ids = (array) $request->input('ids', []);
        Sarayano::destroy($ids);
        return response()->noContent();
    }
}
