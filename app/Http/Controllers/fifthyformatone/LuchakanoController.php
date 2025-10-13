<?php

namespace App\Http\Controllers\fifthyformatone;

use App\Http\Controllers\Controller;
use App\Http\Requests\fifthyformatone\StoreLuchakanoRequest;
use App\Http\Requests\fifthyformatone\UpdateLuchakanoRequest;
use App\Http\Resources\fifthyformatone\LuchakanoResource;
use App\Models\fifthyformatone\Luchakano;
use Illuminate\Http\Request;

class LuchakanoController extends Controller
{
    /** GET /luchakano */
    public function index(Request $request)
    {
        $perPage = (int) $request->input('perPage', 10);
        $search  = $request->input('search');

        $q = Luchakano::query()
            ->with(['resource:id,name,code_number,phone_number'])
            ->latest();

        if ($search) {
            $q->where(function ($w) use ($search) {
                $w->where('nickname', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%")
                  ->orWhere('main_province', 'like', "%{$search}%")
                  ->orWhere('now_province', 'like', "%{$search}%")
                  ->orWhere('job', 'like', "%{$search}%")
                  ->orWhere('gun_type', 'like', "%{$search}%")
                  ->orWhere('phone_number', 'like', "%{$search}%");
            });
        }

        return LuchakanoResource::collection($q->paginate($perPage));
    }

    /** POST /luchakano */
    public function store(StoreLuchakanoRequest $request)
    {
        $record = Luchakano::create($request->validated())
            ->load(['resource:id,name,code_number,phone_number']);

        return LuchakanoResource::make($record);
    }

    /** GET /luchakano/{luchakano} */
    public function show(Luchakano $luchakano)
    {
        $luchakano->load(['resource:id,name,code_number,phone_number']);
        return LuchakanoResource::make($luchakano);
    }

    /** PUT /luchakano/{luchakano} */
    public function update(UpdateLuchakanoRequest $request, Luchakano $luchakano)
    {
        $luchakano->update($request->validated());
        $luchakano->load(['resource:id,name,code_number,phone_number']);
        return LuchakanoResource::make($luchakano);
    }

    /** DELETE /luchakano/{luchakano} */
    public function destroy(Luchakano $luchakano)
    {
        $luchakano->delete();
        return response()->noContent();
    }

    /** POST /luchakano/bulk-delete  { "ids": [1,2,3] } */
    public function bulkDelete(Request $request)
    {
        Luchakano::destroy($request->input('ids', []));
        return response()->noContent();
    }
}
