<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ResourceActivityTypeResource;
use App\Models\ResourceActivityType;
use Illuminate\Http\Request;

class ResourceActivityTypeController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) $request->query('perPage', 25);
        $perPage = max(1, min(200, $perPage));

        $search = trim((string) $request->query('search', ''));

        $query = ResourceActivityType::query();

        if ($search !== '') {
            $query->where('name', 'like', "%{$search}%");
        }

        $query->latest('id');

        return ResourceActivityTypeResource::collection(
            $query->paginate($perPage)
        );
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'unique:resource_activity_types,name'],
        ], [
            'name.required' => 'د فعالیت ډول نوم ضروري دی.',
            'name.unique'   => 'دا د فعالیت ډول نوم مخکې نه ثبت شوی دی.',
        ]);

        $item = ResourceActivityType::create($data);

        return new ResourceActivityTypeResource($item);
    }

    public function show(ResourceActivityType $resourceActivityType)
    {
        return new ResourceActivityTypeResource($resourceActivityType);
    }

    public function update(Request $request, ResourceActivityType $resourceActivityType)
    {
        $data = $request->validate([
            'name' => ['required', 'unique:resource_activity_types,name,' . $resourceActivityType->id],
        ], [
            'name.required' => 'د فعالیت ډول نوم ضروري دی.',
            'name.unique'   => 'دا د فعالیت ډول نوم مخکې نه ثبت شوی دی.',
        ]);

        $resourceActivityType->update($data);

        return new ResourceActivityTypeResource($resourceActivityType);
    }

    public function destroy(ResourceActivityType $resourceActivityType)
    {
        $resourceActivityType->delete();
        return response()->json(['message' => 'په بریالیتوب سره حذف شو.']);
    }
}