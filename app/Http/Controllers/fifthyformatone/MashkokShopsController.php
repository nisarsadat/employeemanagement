<?php

namespace App\Http\Controllers\fifthyformatone;

use App\Http\Controllers\Controller;
use App\Http\Requests\fifthyformatone\StoreMashkokShopsRequest;
use App\Http\Requests\fifthyformatone\UpdateMashkokShopsRequest;
use App\Http\Resources\fifthyformatone\MashkokShopsResource;
use App\Models\fifthyformatone\MashkokShops;
use Illuminate\Http\Request;

class MashkokShopsController extends Controller
{
    /** GET /mashkok-shops */
    public function index(Request $request)
    {
        $perPage = (int) $request->input('perPage', 10);
        $search  = $request->input('search');

        $q = MashkokShops::query()
            ->with(['resource:id,name,code_number,phone_number'])
            ->latest();

        if ($search) {
            $q->where(function ($w) use ($search) {
                $w->where('province', 'like', "%{$search}%")
                  ->orWhere('district', 'like', "%{$search}%")
                  ->orWhere('village', 'like', "%{$search}%")
                  ->orWhere('market_name', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%");
            });
        }

        return MashkokShopsResource::collection($q->paginate($perPage));
    }

    /** POST /mashkok-shops */
    public function store(StoreMashkokShopsRequest $request)
    {
        $record = MashkokShops::create($request->validated())
            ->load(['resource:id,name,code_number,phone_number']);

        return MashkokShopsResource::make($record);
    }

    /** GET /mashkok-shops/{mashkok_shop} */
    public function show(MashkokShops $mashkok_shop)
    {
        $mashkok_shop->load(['resource:id,name,code_number,phone_number']);
        return MashkokShopsResource::make($mashkok_shop);
    }

    /** PUT /mashkok-shops/{mashkok_shop} */
    public function update(UpdateMashkokShopsRequest $request, MashkokShops $mashkok_shop)
    {
        $mashkok_shop->update($request->validated());
        $mashkok_shop->load(['resource:id,name,code_number,phone_number']);
        return MashkokShopsResource::make($mashkok_shop);
    }

    /** DELETE /mashkok-shops/{mashkok_shop} */
    public function destroy(MashkokShops $mashkok_shop)
    {
        $mashkok_shop->delete();
        return response()->noContent();
    }

    /** POST /mashkok-shops/bulk-delete  { "ids": [1,2,3] } */
    public function bulkDelete(Request $request)
    {
        MashkokShops::destroy($request->input('ids', []));
        return response()->noContent();
    }
}
