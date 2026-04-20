<?php

namespace App\Http\Controllers;

use App\Http\Resources\LetryFormatResource;
use App\Models\LetryFormat;
use Illuminate\Http\Request;

class LetryFormatController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) $request->query('perPage', 25);
        $perPage = max(1, min(200, $perPage));

        $search = trim((string) $request->query('search', ''));

        $query = LetryFormat::query();

        if ($search !== '') {
            $query->where('letry_format_name', 'like', "%{$search}%");
        }

        $query->latest('id');

        return LetryFormatResource::collection(
            $query->paginate($perPage)
        );
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'letry_format_name' => ['required', 'unique:letry_formats,letry_format_name'],
        ], [
            'letry_format_name.required' => 'د لتري فارمت نوم ضروري دی.',
            'letry_format_name.unique'   => 'دا د لتري فارمت نوم له مخکې نه موجود دی.',
        ]);

        $letryFormat = LetryFormat::create($data);

        return new LetryFormatResource($letryFormat);
    }

    public function show(LetryFormat $letry_format)
    {
        return new LetryFormatResource($letry_format);
    }

    public function update(Request $request, LetryFormat $letry_format)
    {
        $data = $request->validate([
            'letry_format_name' => ['required', 'unique:letry_formats,letry_format_name,' . $letry_format->id],
        ], [
            'letry_format_name.required' => 'د لتري فارمت نوم ضروري دی.',
            'letry_format_name.unique'   => 'دا د لتري فارمت نوم له مخکې نه موجود دی.',
        ]);

        $letry_format->update($data);

        return new LetryFormatResource($letry_format);
    }

    public function destroy(LetryFormat $letry_format)
    {
        $letry_format->delete();

        return response()->json([
            'message' => 'په بریالیتوب سره حذف شو.',
        ]);
    }
}