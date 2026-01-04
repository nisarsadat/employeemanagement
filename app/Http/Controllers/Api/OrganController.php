<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrganRequest;
use App\Http\Requests\UpdateOrganRequest;
use App\Http\Resources\OrganResource;
use App\Models\Organ;
use Illuminate\Http\Request;

class OrganController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) $request->query('perPage', 25);
        $perPage = max(1, min(200, $perPage));

        $search = trim((string) $request->query('search', ''));

        $query = Organ::query();

        if ($search !== '') {
            $query->where('name', 'like', "%{$search}%");
        }

        $query->latest('id');

        return OrganResource::collection(
            $query->paginate($perPage)
        );
    }

    public function store(StoreOrganRequest $request)
    {
        $organ = Organ::create($request->validated());
        return new OrganResource($organ);
    }

    public function show(Organ $organ)
    {
        return new OrganResource($organ);
    }

    public function update(UpdateOrganRequest $request, Organ $organ)
    {
        $organ->update($request->validated());
        return new OrganResource($organ);
    }

    public function destroy(Organ $organ)
    {
        $organ->delete();
        return response()->json(['message' => 'Deleted successfully']);
    }
}
