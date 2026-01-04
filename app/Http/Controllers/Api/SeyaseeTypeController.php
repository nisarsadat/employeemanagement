<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSeyaseeTypeRequest;
use App\Http\Requests\UpdateSeyaseeTypeRequest;
use App\Http\Resources\SeyaseeTypeResource;
use App\Models\SeyaseeType;
use Illuminate\Http\Request;

class SeyaseeTypeController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) $request->query('perPage', 25);
        $perPage = max(1, min(200, $perPage));

        $search = trim((string) $request->query('search', ''));

        $query = SeyaseeType::query();

        if ($search !== '') {
            $query->where('name', 'like', "%{$search}%");
        }

        $query->latest('id');

        return SeyaseeTypeResource::collection(
            $query->paginate($perPage)
        );
    }

    public function store(StoreSeyaseeTypeRequest $request)
    {
        $type = SeyaseeType::create($request->validated());
        return new SeyaseeTypeResource($type);
    }

    public function show(SeyaseeType $seyaseeType)
    {
        return new SeyaseeTypeResource($seyaseeType);
    }

    public function update(UpdateSeyaseeTypeRequest $request, SeyaseeType $seyaseeType)
    {
        $seyaseeType->update($request->validated());
        return new SeyaseeTypeResource($seyaseeType);
    }

    public function destroy(SeyaseeType $seyaseeType)
    {
        $seyaseeType->delete();
        return response()->json(['message' => 'Deleted successfully']);
    }
}
