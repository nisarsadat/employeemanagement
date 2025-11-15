<?php
// app/Http/Controllers/Api/MapPointController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MapPoint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MapPointController extends Controller
{
    public function index()
    {
        $points = MapPoint::orderByDesc('id')->get();

        $points->transform(function ($p) {
            $p->picture_url = $p->picture_path 
                ? asset('storage/' . $p->picture_path) 
                : null;
            return $p;
        });

        return response()->json($points);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'         => ['required', 'string', 'max:255'],
            'father_name'  => ['nullable', 'string', 'max:255'],
            'code_number'  => ['nullable', 'string', 'max:255'],
            'coordinates'  => ['required', 'string', 'max:100'],
            'phone'        => ['nullable', 'string', 'max:50'],
            'note'         => ['nullable', 'string'],
            'picture'      => ['nullable', 'image', 'max:4096'],
        ]);

        if ($request->hasFile('picture')) {
            $data['picture_path'] = $request->file('picture')
                ->store('map_points', 'public');
        }

        // points created by clicking on map → subject_type/id stay null
        $point = MapPoint::create($data);

        $point->picture_url = $point->picture_path
            ? asset('storage/' . $point->picture_path)
            : null;

        return response()->json($point, 201);
    }

    public function destroy($id)
    {
        $point = MapPoint::findOrFail($id);

        if ($point->picture_path && Storage::disk('public')->exists($point->picture_path)) {
            Storage::disk('public')->delete($point->picture_path);
        }

        $point->delete();

        return response()->json(['message' => 'Deleted successfully']);
    }
}
