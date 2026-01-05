<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreResourceActivityTypeRequest;
use App\Http\Requests\UpdateResourceActivityTypeRequest;
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

    public function store(StoreResourceActivityTypeRequest $request)
    {
        $item = ResourceActivityType::create($request->validated());
        return new ResourceActivityTypeResource($item);
    }

    public function show(ResourceActivityType $resourceActivityType)
    {
        return new ResourceActivityTypeResource($resourceActivityType);
    }

    public function update(UpdateResourceActivityTypeRequest $request, ResourceActivityType $resourceActivityType)
    {
        $resourceActivityType->update($request->validated());
        return new ResourceActivityTypeResource($resourceActivityType);
    }

    public function destroy(ResourceActivityType $resourceActivityType)
    {
        $resourceActivityType->delete();
        return response()->json(['message' => 'Deleted successfully']);
    }
}
