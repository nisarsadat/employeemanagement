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
            'name' => ['required', 'string', 'unique:event_types,name'],
        ], [
            'name.required' => 'د پېښې ډول نوم ضروري دی.',
            'name.string'   => 'د پېښې ډول نوم باید متن وي.',
            'name.unique'   => 'دا د پېښې ډول نوم مخکې نه ثبت شوی دی.',
        ]);

        $row = EventType::create($data);
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
                'required',
                'string',
                Rule::unique('event_types', 'name')->ignore($eventType->id),
            ],
        ], [
            'name.required' => 'د پېښې ډول نوم ضروري دی.',
            'name.string'   => 'د پېښې ډول نوم باید متن وي.',
            'name.unique'   => 'دا د پېښې ډول نوم مخکې نه ثبت شوی دی.',
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

    public function options()
    {
        $rows = EventType::query()
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        return response()->json(['data' => $rows]);
    }
}