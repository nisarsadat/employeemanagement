<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;
use App\Http\Requests\StoreEventRequest;
use App\Http\Requests\UpdateEventRequest;
use App\Http\Resources\EventResource;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use App\Services\NotificationService; // 🔔

class EventController extends Controller
{
    private function isDuplicateKey(QueryException $e): bool
    {
        // MySQL duplicate key error code
        return (int) ($e->errorInfo[1] ?? 0) === 1062;
    }

    private function duplicateResponse()
    {
        return response()->json([
            'message' => 'این رویداد قبلاً ثبت شده است.',
            'errors'  => [
                'duplicate' => [
                    'رویداد با همین مشخصات (نوع رویداد، نوع مشخص، شخص هدف، مجرم و موقعیت) قبلاً در سیستم موجود است.',
                ],
            ],
        ], 422);
    }

    public function index(Request $request)
    {
        $query = Event::with([
            'resource:id,code_number',
            'officeEmployee:id,code_number',
            'akhistonkyEmployee:id,code_number',
            'documents',
            'finances',
        ])->when($request->search, function ($q) use ($request) {
            $s = trim((string) $request->search);
            $q->where(function ($qq) use ($s) {
                $qq->where('event_type', 'like', "%{$s}%")
                ->orWhere('event_specific_type', 'like', "%{$s}%")
                ->orWhere('target_person', 'like', "%{$s}%")
                ->orWhere('criminal', 'like', "%{$s}%")
                ->orWhere('event_zone', 'like', "%{$s}%")
                ->orWhere('event_hawza', 'like', "%{$s}%")
                ->orWhere('event_specific_place', 'like', "%{$s}%");
            });
        })->orderByDesc('id');


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
        $user = $request->user(); // 🔔 who created

        try {
            return DB::transaction(function () use ($request, $user) {
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

                $event->load([
                    'resource',
                    'officeEmployee',
                    'akhistonkyEmployee',
                    'documents',
            'finances',

                ]);

                // 🔔 Notification: CREATED
                if ($user) {
                    NotificationService::record(
                        $user,
                        module: 'event',
                        action: 'created',
                        title: 'رویداد جدید ثبت شد',
                        message: 'نوع رویداد: ' . ($event->event_type ?? ''),
                        routeName: 'events',              // adjust to your real route name/form route if needed
                        routeParams: ['id' => $event->id],
                        level: 'success',
                        changes: null
                    );
                }

                return new EventResource($event);
            });
        } catch (QueryException $e) {
            if ($this->isDuplicateKey($e)) {
                return $this->duplicateResponse();
            }
            throw $e;
        }
    }

    public function show($id)
    {
        $event = Event::with([
            'resource',
            'officeEmployee',
            'akhistonkyEmployee',
            'documents',
            'finances',

        ])->findOrFail($id);

        return new EventResource($event);
    }

    public function update(UpdateEventRequest $request, $id)
    {
        $user = $request->user(); // 🔔 who updated

        try {
            return DB::transaction(function () use ($request, $id, $user) {
                $event = Event::with('documents')->findOrFail($id);

                // 🧮 build changes BEFORE update (same style as InformationController)
                $original  = $event->getOriginal();
                $validated = $request->validated();
                $changes   = [];

                foreach ($validated as $field => $newValue) {
                    if (array_key_exists($field, $original)) {
                        $oldValue = $original[$field];
                        if ($oldValue != $newValue) {
                            $changes[$field] = [
                                'old' => $oldValue,
                                'new' => $newValue,
                            ];
                        }
                    }
                }

                $event->update($validated);

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

                $event->load([
                    'resource',
                    'officeEmployee',
                    'akhistonkyEmployee',
                    'documents',
            'finances',

                ]);

                // 🔔 Notification: UPDATED
                if ($user) {
                    NotificationService::record(
                        $user,
                        module: 'event',
                        action: 'updated',
                        title: 'رویداد اصلاح شد',
                        message: 'نوع رویداد: ' . ($event->event_type ?? ''),
                        routeName: 'events',              // adjust if you use a form route like 'eventform'
                        routeParams: ['id' => $event->id],
                        level: 'info',
                        changes: !empty($changes) ? $changes : null
                    );
                }

                return new EventResource($event);
            });
        } catch (QueryException $e) {
            if ($this->isDuplicateKey($e)) {
                return $this->duplicateResponse();
            }
            throw $e;
        }
    }

    public function destroy(Request $request, $id)
    {
        $user = $request->user(); // 🔔 who deleted

        return DB::transaction(function () use ($id, $user) {
            $event = Event::with('documents')->findOrFail($id);
            $type  = $event->event_type;

            foreach ($event->documents as $doc) {
                Storage::disk('public')->delete($doc->file_path);
            }

            $event->documents()->delete();
            $event->delete();

            // 🔔 Notification: DELETED
            if ($user) {
                NotificationService::record(
                    $user,
                    module: 'event',
                    action: 'deleted',
                    title: 'رویداد حذف شد',
                    message: 'نوع رویداد: ' . ($type ?? ''),
                    routeName: null,
                    routeParams: [],
                    level: 'warning',
                    changes: null
                );
            }

            return response()->json([
                'message' => 'Event deleted successfully',
            ]);
        });
    }
}
