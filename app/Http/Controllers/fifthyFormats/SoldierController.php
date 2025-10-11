<?php

namespace App\Http\Controllers\fifthyFormats;

use App\Http\Controllers\Controller;
use App\Http\Requests\fifthyFormats\StoreSoldierRequest;
use App\Http\Requests\fifthyFormats\UpdateSoldierRequest;
use App\Http\Resources\fifthyFormats\SoldierResource;
use App\Models\fifthyFormats\Soldier;
use Illuminate\Http\Request;

class SoldierController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) $request->get('perPage', 15);
        $search  = $request->get('search');
        $rid     = $request->get('resource_id');

        $sortBy  = $request->get('sort_by', 'created_at');
        $sortDir = in_array($request->get('sort_dir'), ['asc','desc'], true) ? $request->get('sort_dir') : 'desc';

        $q = Soldier::query()->with([
            'resource' => fn($qr) => $qr->select('id','name','code_number','phone_number'),
        ]);

        if ($search) {
            $q->where(function ($qq) use ($search) {
                $qq->where('name', 'like', "%{$search}%")
                   ->orWhere('father_name', 'like', "%{$search}%")
                   ->orWhere('main_province', 'like', "%{$search}%")
                   ->orWhere('now_province', 'like', "%{$search}%")
                   ->orWhere('specific_place', 'like', "%{$search}%")
                   ->orWhere('old_job', 'like', "%{$search}%")
                   ->orWhere('now_job', 'like', "%{$search}%")
                   ->orWhere('phone_number', 'like', "%{$search}%");
            });
        }

        if ($rid) {
            $q->where('resource_id', $rid);
        }

        $q->orderBy($sortBy, $sortDir);

        return SoldierResource::collection($q->paginate($perPage));
    }

    public function show(Soldier $soldier)
    {
        $soldier->load([
            'resource' => fn($qr) => $qr->select('id','name','code_number','phone_number'),
        ]);

        return new SoldierResource($soldier);
    }

    public function store(StoreSoldierRequest $request)
    {
        $data = $request->validated();

        $soldier = Soldier::create($data);
        $soldier->load([
            'resource' => fn($qr) => $qr->select('id','name','code_number','phone_number'),
        ]);

        return (new SoldierResource($soldier))
            ->additional(['message' => 'Created'])
            ->response()
            ->setStatusCode(201);
    }

    public function update(UpdateSoldierRequest $request, Soldier $soldier)
    {
        $data = $request->validated();

        $soldier->update($data);
        $soldier->load([
            'resource' => fn($qr) => $qr->select('id','name','code_number','phone_number'),
        ]);

        return new SoldierResource($soldier);
    }

    public function destroy(Soldier $soldier)
    {
        $soldier->delete();
        return response()->noContent();
    }
}
