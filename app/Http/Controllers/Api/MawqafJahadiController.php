<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
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

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'unique:mawqaf_jahadis,name'],
        ], [
            'name.required' => 'د موقف جهادي نوم ضروري دی.',
            'name.unique'   => 'دا د موقف جهادي نوم مخکې نه ثبت شوی دی.',
        ]);

        $item = MawqafJahadi::create($data);

        return new MawqafJahadiResource($item);
    }

    public function show(MawqafJahadi $mawqafJahadi)
    {
        return new MawqafJahadiResource($mawqafJahadi);
    }

    public function update(Request $request, MawqafJahadi $mawqafJahadi)
    {
        $data = $request->validate([
            'name' => ['required', 'unique:mawqaf_jahadis,name,' . $mawqafJahadi->id],
        ], [
            'name.required' => 'د موقف جهادي نوم ضروري دی.',
            'name.unique'   => 'دا د موقف جهادي نوم مخکې نه ثبت شوی دی.',
        ]);

        $mawqafJahadi->update($data);

        return new MawqafJahadiResource($mawqafJahadi);
    }

    public function destroy(MawqafJahadi $mawqafJahadi)
    {
        $mawqafJahadi->delete();
        return response()->json(['message' => 'په بریالیتوب سره حذف شو.']);
    }
}