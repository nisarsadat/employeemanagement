<?php

namespace App\Http\Controllers\fifthyFormats;

use App\Http\Controllers\Controller;
use App\Http\Requests\fifthyFormats\StoreEntryExitRequest;
use App\Http\Requests\fifthyFormats\UpdateEntryExitRequest;
use App\Http\Resources\fifthyFormats\EntryExitResource;
use App\Models\fifthyFormats\EntryExit;
use Illuminate\Http\Request;

class EntryExitController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) $request->get('perPage', 15);
        $search  = $request->get('search');
        $rid     = $request->get('resource_id');

        $sortBy  = $request->get('sort_by', 'created_at');
        $sortDir = in_array($request->get('sort_dir'), ['asc','desc'], true) ? $request->get('sort_dir') : 'desc';

        $q = EntryExit::query()->with([
            'resource' => fn($qr) => $qr->select('id','name','code_number','phone_number'),
        ]);

        if ($search) {
            $q->where(function ($qq) use ($search) {
                $qq->where('name', 'like', "%{$search}%")
                   ->orWhere('entry_way', 'like', "%{$search}%")
                   ->orWhere('province', 'like', "%{$search}%")
                   ->orWhere('district', 'like', "%{$search}%")
                   ->orWhere('village', 'like', "%{$search}%")
                   ->orWhere('specific_place', 'like', "%{$search}%")
                   ->orWhere('where_went', 'like', "%{$search}%")
                   ->orWhere('where_come', 'like', "%{$search}%");
            });
        }

        if ($rid) {
            $q->where('resource_id', $rid);
        }

        $q->orderBy($sortBy, $sortDir);

        return EntryExitResource::collection($q->paginate($perPage));
    }

    public function show(EntryExit $entryexit)
    {
        $entryexit->load([
            'resource' => fn($qr) => $qr->select('id','name','code_number','phone_number'),
        ]);

        return new EntryExitResource($entryexit);
    }

    public function store(StoreEntryExitRequest $request)
    {
        $data = $request->validated();

        $entryExit = EntryExit::create($data);
        $entryExit->load([
            'resource' => fn($qr) => $qr->select('id','name','code_number','phone_number'),
        ]);

        return (new EntryExitResource($entryExit))
            ->additional(['message' => 'Created'])
            ->response()
            ->setStatusCode(201);
    }

    public function update(UpdateEntryExitRequest $request, EntryExit $entryexit)
    {
        $data = $request->validated();

        $entryexit->update($data);
        $entryexit->load([
            'resource' => fn($qr) => $qr->select('id','name','code_number','phone_number'),
        ]);

        return new EntryExitResource($entryexit);
    }

    public function destroy(EntryExit $entryexit)
    {
        $entryexit->delete();
        return response()->noContent();
    }
}
