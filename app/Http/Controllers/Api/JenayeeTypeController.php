<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\JenayeeTypeResource;
use App\Models\JenayeeType;
use Illuminate\Http\Request;

class JenayeeTypeController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) $request->query('perPage', 25);
        $perPage = max(1, min(200, $perPage));

        $search = trim((string) $request->query('search', ''));

        $query = JenayeeType::query();

        if ($search !== '') {
            $query->where('name', 'like', "%{$search}%");
        }

        $query->latest('id');

        return JenayeeTypeResource::collection(
            $query->paginate($perPage)
        );
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'unique:jenayee_types,name'],
        ], [
            'name.required' => 'د جنایي ډول نوم ضروري دی.',
            'name.unique'   => 'دا د جنایي ډول نوم مخکې نه ثبت شوی دی.',
        ]);

        $type = JenayeeType::create($data);

        return new JenayeeTypeResource($type);
    }

    public function show(JenayeeType $jenayeeType)
    {
        return new JenayeeTypeResource($jenayeeType);
    }

    public function update(Request $request, JenayeeType $jenayeeType)
    {
        $data = $request->validate([
            'name' => ['required', 'unique:jenayee_types,name,' . $jenayeeType->id],
        ], [
            'name.required' => 'د جنایي ډول نوم ضروري دی.',
            'name.unique'   => 'دا د جنایي ډول نوم مخکې نه ثبت شوی دی.',
        ]);

        $jenayeeType->update($data);

        return new JenayeeTypeResource($jenayeeType);
    }

    public function destroy(JenayeeType $jenayeeType)
    {
        $jenayeeType->delete();
        return response()->json(['message' => 'په بریالیتوب سره حذف شو.']);
    }
}