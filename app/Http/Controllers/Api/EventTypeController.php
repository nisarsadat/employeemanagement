<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\EventTypeResource;
use App\Models\EventType;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;

class EventTypeController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) $request->query('perPage', 10);
        $search  = trim((string) $request->query('search', ''));

        $query = EventType::query()
            ->select('id', 'name')
            // ✅ load specific types so frontend can show chips
            ->with(['specificTypes:id,event_type_id,name']);

        if ($search !== '') {
            $query->where('name', 'like', "%{$search}%");
        }

        $query->orderBy('id', 'desc');

        return EventTypeResource::collection($query->paginate($perPage));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:190', 'unique:event_types,name'],
        ]);

        $row = EventType::create($data);

        // ✅ also load children after create (empty list, but consistent)
        $row->load(['specificTypes:id,event_type_id,name']);

        return response()->json([
            'data' => new EventTypeResource($row),
            'message' => 'created',
        ], 201);
    }

    public function show(EventType $eventType): JsonResponse
    {
        $eventType->load(['specificTypes:id,event_type_id,name']);

        return response()->json([
            'data' => new EventTypeResource($eventType),
        ]);
    }

    public function update(Request $request, EventType $eventType): JsonResponse
    {
        $data = $request->validate([
            'name' => [
                'required', 'string', 'max:190',
                Rule::unique('event_types', 'name')->ignore($eventType->id),
            ],
        ]);

        $eventType->update($data);

        $eventType->load(['specificTypes:id,event_type_id,name']);

        return response()->json([
            'data' => new EventTypeResource($eventType),
            'message' => 'updated',
        ]);
    }

    public function destroy(EventType $eventType): JsonResponse
    {
        $eventType->delete();

        return response()->json([
            'message' => 'deleted',
        ]);
    }

    // ✅ dropdown options for front
    public function options()
    {
        $rows = EventType::query()
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        return response()->json(['data' => $rows]);
    }
}
