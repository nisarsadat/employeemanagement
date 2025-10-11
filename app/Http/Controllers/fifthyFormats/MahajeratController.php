<?php

namespace App\Http\Controllers\fifthyFormats;

use App\Http\Controllers\Controller;
use App\Http\Requests\fifthyFormats\StoreMahajeratRequest;
use App\Http\Requests\fifthyFormats\UpdateMahajeratRequest;
use App\Http\Resources\fifthyFormats\MahajeratResource;
use App\Models\fifthyFormats\Mahajerat;
use Illuminate\Http\Request;

class MahajeratController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) $request->get('perPage', 15);
        $search  = $request->get('search');
        $rid     = $request->get('resource_id');

        $sortBy  = $request->get('sort_by', 'created_at');
        $sortDir = in_array($request->get('sort_dir'), ['asc','desc'], true) ? $request->get('sort_dir') : 'desc';

        $q = Mahajerat::query()->with([
            'resource' => fn($qr) => $qr->select('id','name','code_number','phone_number'),
        ]);

        if ($search) {
            $q->where(function ($qq) use ($search) {
                $qq->where('name', 'like', "%{$search}%")
                   ->orWhere('father_name', 'like', "%{$search}%")
                   ->orWhere('scientific_level', 'like', "%{$search}%")
                   ->orWhere('main_province', 'like', "%{$search}%")
                   ->orWhere('main_district', 'like', "%{$search}%")
                   ->orWhere('main_village', 'like', "%{$search}%")
                   ->orWhere('now_province', 'like', "%{$search}%")
                   ->orWhere('now_zone', 'like', "%{$search}%")
                   ->orWhere('now_district', 'like', "%{$search}%")
                   ->orWhere('now_vinage', 'like', "%{$search}%")
                   ->orWhere('specific_place', 'like', "%{$search}%")
                   ->orWhere('country', 'like', "%{$search}%");
            });
        }

        if ($rid) {
            $q->where('resource_id', $rid);
        }

        $q->orderBy($sortBy, $sortDir);

        return MahajeratResource::collection($q->paginate($perPage));
    }

    public function show(Mahajerat $mahajerat)
    {
        $mahajerat->load([
            'resource' => fn($qr) => $qr->select('id','name','code_number','phone_number'),
        ]);

        return new MahajeratResource($mahajerat);
    }

    public function store(StoreMahajeratRequest $request)
    {
        $data = $request->validated();

        $item = Mahajerat::create($data);
        $item->load([
            'resource' => fn($qr) => $qr->select('id','name','code_number','phone_number'),
        ]);

        return (new MahajeratResource($item))
            ->additional(['message' => 'Created'])
            ->response()
            ->setStatusCode(201);
    }

    public function update(UpdateMahajeratRequest $request, Mahajerat $mahajerat)
    {
        $data = $request->validated();

        $mahajerat->update($data);
        $mahajerat->load([
            'resource' => fn($qr) => $qr->select('id','name','code_number','phone_number'),
        ]);

        return new MahajeratResource($mahajerat);
    }

    public function destroy(Mahajerat $mahajerat)
    {
        $mahajerat->delete();
        return response()->noContent();
    }
}
