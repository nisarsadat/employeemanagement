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
            $query->where('name', 'like', "%{$search}%");
        }

        $query->latest('id');

        return LetryFormatResource::collection(
            $query->paginate($perPage)
        );
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'unique:letry_formats,name'],
        ], [
            'name.required' => 'د لتري فارمت نوم ضروري دی.',
            'name.unique'   => 'دا د لتري فارمت نوم له مخکې نه موجود دی.',
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
            'name' => ['required', 'unique:letry_formats,name,' . $letry_format->id],
        ], [
            'name.required' => 'د لتري فارمت نوم ضروري دی.',
            'name.unique'   => 'دا د لتري فارمت نوم له مخکې نه موجود دی.',
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