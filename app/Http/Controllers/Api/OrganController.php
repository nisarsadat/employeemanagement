<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrganResource;
use App\Models\Organ;
use Illuminate\Http\Request;

class OrganController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) $request->query('perPage', 25);
        $perPage = max(1, min(200, $perPage));

        $search = trim((string) $request->query('search', ''));

        $query = Organ::query();

        if ($search !== '') {
            $query->where('name', 'like', "%{$search}%");
        }

        $query->latest('id');

        return OrganResource::collection(
            $query->paginate($perPage)
        );
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'unique:organs,name'],
        ], [
            'name.required' => 'د ارګان نوم ضروري دی.',
            'name.unique'   => 'دا د ارګان نوم مخکې نه ثبت شوی دی.',
        ]);

        $organ = Organ::create($data);

        return new OrganResource($organ);
    }

    public function show(Organ $organ)
    {
        return new OrganResource($organ);
    }

    public function update(Request $request, Organ $organ)
    {
        $data = $request->validate([
            'name' => ['required', 'unique:organs,name,' . $organ->id],
        ], [
            'name.required' => 'د ارګان نوم ضروري دی.',
            'name.unique'   => 'دا د ارګان نوم مخکې نه ثبت شوی دی.',
        ]);

        $organ->update($data);

        return new OrganResource($organ);
    }

    public function destroy(Organ $organ)
    {
        $organ->delete();
        return response()->json(['message' => 'په بریالیتوب سره حذف شو.']);
    }
}