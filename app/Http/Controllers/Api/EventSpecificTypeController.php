<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\EventSpecificTypeResource;
use App\Models\EventSpecificType;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;

class EventSpecificTypeController extends Controller
{
    public function index(Request $request)
    {
        $perPage      = (int) $request->query('perPage', 10);
        $search       = trim((string) $request->query('search', ''));
        $eventTypeId  = $request->query('event_type_id');

        $query = EventSpecificType::query()
            ->select('id', 'event_type_id', 'name')
            ->with(['eventType:id,name']);

        if ($eventTypeId) {
            $query->where('event_type_id', $eventTypeId);
        }

        if ($search !== '') {
            $query->where('name', 'like', "%{$search}%");
        }

        $query->orderBy('id', 'desc');

        return EventSpecificTypeResource::collection(
            $query->paginate($perPage)
        );
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'event_type_id' => ['required', 'integer', 'exists:event_types,id'],
            'name' => [
                'required',
                'string',
                'unique:event_specific_types,name',
            ],
        ], [
            'event_type_id.required' => 'د پېښې ډول ټاکل ضروري دي.',
            'event_type_id.integer'  => 'د پېښې ډول باید سم عدد وي.',
            'event_type_id.exists'   => 'ټاکل شوی د پېښې ډول ونه موندل شو.',
            'name.required'          => 'د پېښې ځانګړی ډول نوم ضروري دی.',
            'name.string'            => 'د پېښې ځانګړی ډول نوم باید متن وي.',
            'name.unique'            => 'دا د پېښې ځانګړی ډول نوم مخکې نه ثبت شوی دی.',
        ]);

        $row = EventSpecificType::create($data);
        $row->load(['eventType:id,name']);

        return response()->json([
            'data' => new EventSpecificTypeResource($row),
            'message' => 'created',
        ], 201);
    }

    public function show(EventSpecificType $eventSpecificType): JsonResponse
    {
        $eventSpecificType->load(['eventType:id,name']);

        return response()->json([
            'data' => new EventSpecificTypeResource($eventSpecificType),
        ]);
    }

    public function update(Request $request, EventSpecificType $eventSpecificType): JsonResponse
    {
        $data = $request->validate([
            'event_type_id' => ['required', 'integer', 'exists:event_types,id'],
            'name' => [
                'required',
                'string',
                Rule::unique('event_specific_types', 'name')->ignore($eventSpecificType->id),
            ],
        ], [
            'event_type_id.required' => 'د پېښې ډول ټاکل ضروري دي.',
            'event_type_id.integer'  => 'د پېښې ډول باید سم عدد وي.',
            'event_type_id.exists'   => 'ټاکل شوی د پېښې ډول ونه موندل شو.',
            'name.required'          => 'د پېښې ځانګړی ډول نوم ضروري دی.',
            'name.string'            => 'د پېښې ځانګړی ډول نوم باید متن وي.',
            'name.unique'            => 'دا د پېښې ځانګړی ډول نوم مخکې نه ثبت شوی دی.',
        ]);

        $eventSpecificType->update($data);
        $eventSpecificType->load(['eventType:id,name']);

        return response()->json([
            'data' => new EventSpecificTypeResource($eventSpecificType),
            'message' => 'updated',
        ]);
    }

    public function destroy(EventSpecificType $eventSpecificType): JsonResponse
    {
        $eventSpecificType->delete();

        return response()->json([
            'message' => 'deleted',
        ]);
    }
}