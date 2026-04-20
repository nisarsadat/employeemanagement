<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
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

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'unique:seyasee_types,name'],
        ], [
            'name.required' => 'د سیاسي ډول نوم ضروري دی.',
            'name.unique'   => 'دا د سیاسي ډول نوم مخکې نه ثبت شوی دی.',
        ]);

        $type = SeyaseeType::create($data);

        return new SeyaseeTypeResource($type);
    }

    public function show(SeyaseeType $seyaseeType)
    {
        return new SeyaseeTypeResource($seyaseeType);
    }

    public function update(Request $request, SeyaseeType $seyaseeType)
    {
        $data = $request->validate([
            'name' => ['required', 'unique:seyasee_types,name,' . $seyaseeType->id],
        ], [
            'name.required' => 'د سیاسي ډول نوم ضروري دی.',
            'name.unique'   => 'دا د سیاسي ډول نوم مخکې نه ثبت شوی دی.',
        ]);

        $seyaseeType->update($data);

        return new SeyaseeTypeResource($seyaseeType);
    }

    public function destroy(SeyaseeType $seyaseeType)
    {
        $seyaseeType->delete();
        return response()->json(['message' => 'په بریالیتوب سره حذف شو.']);
    }
}