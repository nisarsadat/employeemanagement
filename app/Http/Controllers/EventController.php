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
    /**
     * List all events with relationships and search support.
     */
    public function index(Request $request)
    {
        $query = Event::with([
            'resource',
            'officeEmployee',
            'zoneEmployee',
            'eventOfficeEmployee',
            'eventZoneEmployee',
            'documents'
        ])
        ->when($request->search, function ($q) use ($request) {
            $q->where('event_type', 'like', "%{$request->search}%")
              ->orWhere('event_specific_type', 'like', "%{$request->search}%")
              ->orWhere('target_person', 'like', "%{$request->search}%");
        });

        return EventResource::collection(
            $query->paginate($request->perPage ?? 15)
        );
    }

    /**
     * Store a new event.
     */
    public function store(StoreEventRequest $request)
    {
        DB::beginTransaction();

        try {
            // ✅ Create the event
            $event = Event::create($request->validated());

            // 📎 Handle document uploads
            if ($request->hasFile('documents')) {
                foreach ($request->file('documents') as $file) {
                    $path = $file->store("events/{$event->id}", 'public');
                    $event->documents()->create([
                        'file_name' => $file->getClientOriginalName(),
                        'file_path' => $path,
                        'mime_type' => $file->getClientMimeType(),
                        'size_bytes' => $file->getSize(),
                    ]);
                }
            }

            DB::commit();

            return new EventResource(
                $event->load([
                    'resource',
                    'officeEmployee',
                    'zoneEmployee',
                    'eventOfficeEmployee',
                    'eventZoneEmployee',
                    'documents'
                ])
            );

        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Event creation failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show a single event with all relationships.
     */
    public function show(Event $event)
    {
        return new EventResource(
            $event->load([
                'resource',
                'officeEmployee',
                'zoneEmployee',
                'eventOfficeEmployee',
                'eventZoneEmployee',
                'documents'
            ])
        );
    }

    /**
     * Update an existing event.
     */
    public function update(UpdateEventRequest $request, Event $event)
    {
        DB::beginTransaction();

        try {
            // ✅ Update event fields
            $event->update($request->validated());

            // 📎 Handle new documents (if uploaded)
            if ($request->hasFile('documents')) {
                foreach ($request->file('documents') as $file) {
                    $path = $file->store("events/{$event->id}", 'public');
                    $event->documents()->create([
                        'file_name' => $file->getClientOriginalName(),
                        'file_path' => $path,
                        'mime_type' => $file->getClientMimeType(),
                        'size_bytes' => $file->getSize(),
                    ]);
                }
            }

            DB::commit();

            return new EventResource(
                $event->load([
                    'resource',
                    'officeEmployee',
                    'zoneEmployee',
                    'eventOfficeEmployee',
                    'eventZoneEmployee',
                    'documents'
                ])
            );

        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Event update failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete an event and its documents.
     */
    public function destroy(Event $event)
    {
        // 🗑️ Delete files from storage
        foreach ($event->documents as $doc) {
            Storage::disk('public')->delete($doc->file_path);
        }

        // 🗑️ Delete DB records
        $event->documents()->delete();
        $event->delete();

        return response()->json(['message' => 'Event deleted successfully']);
    }
}
