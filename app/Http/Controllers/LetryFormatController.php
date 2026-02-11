<?php

namespace App\Http\Controllers;

use App\Models\LetryFormat;
use Illuminate\Http\Request;
use App\Http\Resources\LetryFormatResource;
use App\Http\Requests\StoreLetryFormatRequest;
use App\Http\Requests\UpdateLetryFormatRequest;

class LetryFormatController extends Controller
{
    public function index()
    {
        $letryFormats = LetryFormat::latest()->get();
        return LetryFormatResource::collection($letryFormats);
    }

    public function store(StoreLetryFormatRequest $request)
    {
        $letryFormat = LetryFormat::create($request->validated());
        return new LetryFormatResource($letryFormat);
    }

    public function show(LetryFormat $letry_format)
    {
        return new LetryFormatResource($letry_format);
    }

    public function update(UpdateLetryFormatRequest $request, LetryFormat $letry_format)
    {
        $letry_format->update($request->validated());
        return new LetryFormatResource($letry_format);
    }

    public function destroy(LetryFormat $letry_format)
    {
        $letry_format->delete();
        return response()->json([
            'message' => 'Deleted successfully'
        ]);
    }
}
