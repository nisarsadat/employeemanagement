<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MapPoint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MapPointController extends Controller
{
    /**
     * Get all points
     */
    public function index()
    {
        $points = MapPoint::orderByDesc('id')->get();

        // Append full URL for picture
        $points->transform(function ($p) {
            $p->picture_url = $p->picture_path 
                ? asset('storage/' . $p->picture_path) 
                : null;
            return $p;
        });

        return response()->json($points);
    }

    /**
     * Store a new map point
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'         => ['required', 'string', 'max:255'],
            'father_name'  => ['nullable', 'string', 'max:255'],
            'code_number'  => ['nullable', 'string', 'max:255'],
            'coordinates'  => ['required', 'string', 'max:100'],
            'phone'        => ['nullable', 'string', 'max:50'],
            'note'         => ['nullable', 'string'],
            'picture'      => ['nullable', 'image', 'max:4096'], // 4MB
        ]);

        // Save picture if exists
        if ($request->hasFile('picture')) {
            $data['picture_path'] = $request->file('picture')
                ->store('map_points', 'public');
        }

        $point = MapPoint::create($data);

        // Add picture URL in response
        $point->picture_url = $point->picture_path
            ? asset('storage/' . $point->picture_path)
            : null;

        return response()->json($point, 201);
    }

    /**
     * Delete a map point
     */
    public function destroy($id)
    {
        $point = MapPoint::findOrFail($id);

        // delete picture if exists
        if ($point->picture_path && Storage::disk('public')->exists($point->picture_path)) {
            Storage::disk('public')->delete($point->picture_path);
        }

        $point->delete();

        return response()->json(['message' => 'Deleted successfully']);
    }
}
