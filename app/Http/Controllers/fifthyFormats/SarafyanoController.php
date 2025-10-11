<?php

namespace App\Http\Controllers\fifthyFormats;

use App\Http\Controllers\Controller;
use App\Http\Requests\fifthyFormats\StoreSarafyanoRequest;
use App\Http\Requests\fifthyFormats\UpdateSarafyanoRequest;
use App\Http\Resources\fifthyFormats\SarafyanoResource;
use App\Models\fifthyFormats\Sarafyano;
use Illuminate\Http\Request;

class SarafyanoController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) $request->get('perPage', 15);
        $search  = $request->get('search');
        $rid     = $request->get('resource_id');

        $sortBy  = $request->get('sort_by', 'created_at');
        $sortDir = in_array($request->get('sort_dir'), ['asc','desc'], true) ? $request->get('sort_dir') : 'desc';

        $q = Sarafyano::query()->with([
            'resource' => fn($qr) => $qr->select('id','name','code_number','phone_number'),
        ]);

        if ($search) {
            $q->where(function ($qq) use ($search) {
                $qq->where('name', 'like', "%{$search}%")
                   ->orWhere('sarafi_name', 'like', "%{$search}%")
                   ->orWhere('sarafi_type', 'like', "%{$search}%")
                   ->orWhere('father_name', 'like', "%{$search}%")
                   ->orWhere('position', 'like', "%{$search}%")
                   ->orWhere('phone_number', 'like', "%{$search}%")
                   ->orWhere('province', 'like', "%{$search}%")
                   ->orWhere('district', 'like', "%{$search}%")
                   ->orWhere('village', 'like', "%{$search}%")
                   ->orWhere('market_name', 'like', "%{$search}%")
                   ->orWhere('shop_number', 'like', "%{$search}%");
            });
        }

        if ($rid) {
            $q->where('resource_id', $rid);
        }

        $q->orderBy($sortBy, $sortDir);

        return SarafyanoResource::collection($q->paginate($perPage));
    }

    public function show(Sarafyano $sarafyano)
    {
        $sarafyano->load([
            'resource' => fn($qr) => $qr->select('id','name','code_number','phone_number'),
        ]);

        return new SarafyanoResource($sarafyano);
    }

    public function store(StoreSarafyanoRequest $request)
    {
        $data = $request->validated();

        $sarafyano = Sarafyano::create($data);
        $sarafyano->load([
            'resource' => fn($qr) => $qr->select('id','name','code_number','phone_number'),
        ]);

        return (new SarafyanoResource($sarafyano))
            ->additional(['message' => 'Created'])
            ->response()
            ->setStatusCode(201);
    }

    public function update(UpdateSarafyanoRequest $request, Sarafyano $sarafyano)
    {
        $data = $request->validated();

        $sarafyano->update($data);
        $sarafyano->load([
            'resource' => fn($qr) => $qr->select('id','name','code_number','phone_number'),
        ]);

        return new SarafyanoResource($sarafyano);
    }

    public function destroy(Sarafyano $sarafyano)
    {
        $sarafyano->delete();
        return response()->noContent();
    }
}
