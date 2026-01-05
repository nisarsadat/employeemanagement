<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMawqafJahadiRequest;
use App\Http\Requests\UpdateMawqafJahadiRequest;
use App\Http\Resources\MawqafJahadiResource;
use App\Models\MawqafJahadi;
use Illuminate\Http\Request;

class MawqafJahadiController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) $request->query('perPage', 25);
        $perPage = max(1, min(200, $perPage));

        $search = trim((string) $request->query('search', ''));

        $query = MawqafJahadi::query();

        if ($search !== '') {
            $query->where('name', 'like', "%{$search}%");
        }

        $query->latest('id');

        return MawqafJahadiResource::collection(
            $query->paginate($perPage)
        );
    }

    public function store(StoreMawqafJahadiRequest $request)
    {
        $item = MawqafJahadi::create($request->validated());
        return new MawqafJahadiResource($item);
    }

    public function show(MawqafJahadi $mawqafJahadi)
    {
        return new MawqafJahadiResource($mawqafJahadi);
    }

    public function update(UpdateMawqafJahadiRequest $request, MawqafJahadi $mawqafJahadi)
    {
        $mawqafJahadi->update($request->validated());
        return new MawqafJahadiResource($mawqafJahadi);
    }

    public function destroy(MawqafJahadi $mawqafJahadi)
    {
        $mawqafJahadi->delete();
        return response()->json(['message' => 'Deleted successfully']);
    }
}
