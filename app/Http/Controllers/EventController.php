<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;
use App\Http\Requests\StoreEventRequest;
use App\Http\Requests\UpdateEventRequest;
use App\Http\Resources\EventResource;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class EventController extends Controller
{
    public function index(Request $request)
    {
        $query = Event::with([
            'resource',
            'officeEmployee',
            'documents',
        ])->when($request->search, function ($q) use ($request) {
            $s = $request->search;
            $q->where(function ($qq) use ($s) {
                $qq->where('event_type', 'like', "%{$s}%")
                    ->orWhere('event_specific_type', 'like', "%{$s}%")
                    ->orWhere('target_person', 'like', "%{$s}%")
                    ->orWhere('criminal', 'like', "%{$s}%");
            });
        });

        $perPage = $request->integer('perPage');
        $page    = $request->integer('page');

        if ($perPage && $perPage > 0 && $page && $page > 0) {
            return EventResource::collection(
                $query->paginate($perPage, ['*'], 'page', $page)
            );
        }

        return EventResource::collection($query->get());
    }

    public function store(StoreEventRequest $request)
    {
        DB::beginTransaction();

        try {
            $event = Event::create($request->validated());

            if ($request->hasFile('documents')) {
                foreach ($request->file('documents') as $file) {
                    $path = $file->store("events/{$event->id}", 'public');

                    $event->documents()->create([
                        'file_name'  => $file->getClientOriginalName(),
                        'file_path'  => $path,
                        'mime_type'  => $file->getClientMimeType(),
                        'size_bytes' => $file->getSize(),
                    ]);
                }
            }

            DB::commit();

            $event->load([
                'resource',
                'officeEmployee',
                'documents',
            ]);

            return new EventResource($event);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Event creation failed',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function show($id)
    {
        $event = Event::with([
            'resource',
            'officeEmployee',
            'documents',
        ])->findOrFail($id);

        return new EventResource($event);
    }

    public function update(UpdateEventRequest $request, $id)
    {
        DB::beginTransaction();

        try {
            $event = Event::with('documents')->findOrFail($id);

            $event->update($request->validated());

            if ($request->hasFile('documents')) {
                foreach ($request->file('documents') as $file) {
                    $path = $file->store("events/{$event->id}", 'public');

                    $event->documents()->create([
                        'file_name'  => $file->getClientOriginalName(),
                        'file_path'  => $path,
                        'mime_type'  => $file->getClientMimeType(),
                        'size_bytes' => $file->getSize(),
                    ]);
                }
            }

            DB::commit();

            $event->load([
                'resource',
                'officeEmployee',
                'documents',
            ]);

            return new EventResource($event);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Event update failed',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy($id)
    {
        $event = Event::with('documents')->findOrFail($id);

        foreach ($event->documents as $doc) {
            Storage::disk('public')->delete($doc->file_path);
        }

        $event->documents()->delete();
        $event->delete();

        return response()->json([
            'message' => 'Event deleted successfully',
        ]);
    }
}
