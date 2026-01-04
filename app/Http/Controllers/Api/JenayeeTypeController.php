<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreJenayeeTypeRequest;
use App\Http\Requests\UpdateJenayeeTypeRequest;
use App\Http\Resources\JenayeeTypeResource;
use App\Models\JenayeeType;
use Illuminate\Http\Request;

class JenayeeTypeController extends Controller
{
    public function index(Request $request)
    {
        return JenayeeTypeResource::collection(
            JenayeeType::latest()->get()
        );
    }

    public function store(StoreJenayeeTypeRequest $request)
    {
        $type = JenayeeType::create($request->validated());
        return new JenayeeTypeResource($type);
    }

    public function show(JenayeeType $jenayeeType)
    {
        return new JenayeeTypeResource($jenayeeType);
    }

    public function update(UpdateJenayeeTypeRequest $request, JenayeeType $jenayeeType)
    {
        $jenayeeType->update($request->validated());
        return new JenayeeTypeResource($jenayeeType);
    }

    public function destroy(JenayeeType $jenayeeType)
    {
        $jenayeeType->delete();
        return response()->json(['message' => 'Deleted successfully']);
    }
}
