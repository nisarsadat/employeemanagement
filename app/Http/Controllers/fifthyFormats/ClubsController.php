<?php

namespace App\Http\Controllers\fifthyFormats;

use App\Http\Controllers\Controller;
use App\Http\Requests\fifthyFormats\StoreClubsRequest;
use App\Http\Requests\fifthyFormats\UpdateClubsRequest;
use App\Http\Resources\fifthyFormats\ClubsResource;
use App\Models\fifthyFormats\Clubs;
use Illuminate\Http\Request;

class ClubsController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) $request->get('perPage', 15);
        $search  = $request->get('search');
        $rid     = $request->get('resource_id');

        $sortBy  = $request->get('sort_by', 'created_at');
        $sortDir = in_array($request->get('sort_dir'), ['asc','desc'], true) ? $request->get('sort_dir') : 'desc';

        $q = Clubs::query()->with([
            'resource' => fn($qr) => $qr->select('id','name','code_number','phone_number'),
        ]);

        if ($search) {
            $q->where(function ($qq) use ($search) {
                $qq->where('name', 'like', "%{$search}%")
                   ->orWhere('club_name', 'like', "%{$search}%")
                   ->orWhere('club_type', 'like', "%{$search}%")
                   ->orWhere('father_name', 'like', "%{$search}%")
                   ->orWhere('position', 'like', "%{$search}%")
                   ->orWhere('phone_number', 'like', "%{$search}%")
                   ->orWhere('province', 'like', "%{$search}%")
                   ->orWhere('district', 'like', "%{$search}%")
                   ->orWhere('specific_place', 'like', "%{$search}%");
            });
        }

        if ($rid) {
            $q->where('resource_id', $rid);
        }

        $q->orderBy($sortBy, $sortDir);

        return ClubsResource::collection($q->paginate($perPage));
    }

    public function show(Clubs $club)
    {
        $club->load([
            'resource' => fn($qr) => $qr->select('id','name','code_number','phone_number'),
        ]);

        return new ClubsResource($club);
    }

    public function store(StoreClubsRequest $request)
    {
        $data = $request->validated();

        $club = Clubs::create($data);
        $club->load([
            'resource' => fn($qr) => $qr->select('id','name','code_number','phone_number'),
        ]);

        return (new ClubsResource($club))
            ->additional(['message' => 'Created'])
            ->response()
            ->setStatusCode(201);
    }

    public function update(UpdateClubsRequest $request, Clubs $club)
    {
        $data = $request->validated();

        $club->update($data);
        $club->load([
            'resource' => fn($qr) => $qr->select('id','name','code_number','phone_number'),
        ]);

        return new ClubsResource($club);
    }

    public function destroy(Clubs $club)
    {
        $club->delete();
        return response()->noContent();
    }
}
