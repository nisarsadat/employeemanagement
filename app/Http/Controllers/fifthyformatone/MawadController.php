<?php

namespace App\Http\Controllers\fifthyformatone;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\fifthyformatone\Mawad;
use App\Http\Resources\fifthyformatone\MawadResource;
use App\Http\Requests\fifthyformatone\StoreMawadRequest;
use App\Http\Requests\fifthyformatone\UpdateMawadRequest;

class MawadController extends Controller
{
    /**
     * GET /mawads
     */
    public function index(Request $request)
    {
        $perPage = (int) $request->input('perPage', 10);
        $search  = $request->input('search');

        $q = Mawad::query()->with('resource')->latest();

        if ($search) {
            // same as Fasad: search by name only
            $q->where('name', 'like', "%{$search}%");
        }

        return MawadResource::collection($q->paginate($perPage));
    }

    /**
     * POST /mawads
     */
    public function store(StoreMawadRequest $request)
    {
        $data = $request->validated();

        $mawad = Mawad::create($data)->load('resource');

        return MawadResource::make($mawad);
    }

    /**
     * GET /mawads/{mawad}
     */
    public function show(Mawad $mawad)
    {
        $mawad->load('resource');
        return MawadResource::make($mawad);
    }

    /**
     * PUT /mawads/{mawad}
     */
    public function update(UpdateMawadRequest $request, Mawad $mawad)
    {
        $data = $request->validated();

        $mawad->update($data);
        $mawad->load('resource');

        return MawadResource::make($mawad);
    }

    /**
     * DELETE /mawads/{mawad}
     */
    public function destroy(Mawad $mawad)
    {
        $mawad->delete();
        return response()->noContent();
    }

    /**
     * Optional bulk delete to match your style:
     * POST /mawads/bulk-delete  with { "ids": [1,2,3] }
     */
    public function bulkDelete(Request $request)
    {
        $ids = $request->input('ids', []);
        Mawad::destroy($ids);
        return response()->noContent();
    }
}
