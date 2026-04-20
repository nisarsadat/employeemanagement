<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\MarjaResource;
use App\Models\Marja;
use Illuminate\Http\Request;

class MarjaController extends Controller
{
    public function index(Request $request)
    {
        $page    = $request->query('page');
        $perPage = $request->query('perPage', $request->query('per_page'));

        $isValidPage    = is_numeric($page) && (int) $page > 0;
        $isValidPerPage = is_numeric($perPage) && (int) $perPage > 0;

        $search = trim((string) $request->query('search', ''));

        $q = Marja::query()
            ->when($search !== '', fn ($x) => $x->where('name', 'like', "%{$search}%"))
            ->orderByDesc('id');

        if ($isValidPage && $isValidPerPage) {
            return MarjaResource::collection(
                $q->paginate((int) $perPage, ['*'], 'page', (int) $page)
            );
        }

        return MarjaResource::collection($q->get());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'unique:marjas,name'],
        ]);

        $marja = Marja::create($data);

        return MarjaResource::make($marja);
    }

    public function show(Marja $marja)
    {
        return MarjaResource::make($marja);
    }

    public function update(Request $request, Marja $marja)
    {
        $data = $request->validate([
            'name' => ['required', 'unique:marjas,name,' . $marja->id],
        ]);

        $marja->update($data);

        return MarjaResource::make($marja);
    }

    public function destroy(Marja $marja)
    {
        $marja->delete();

        return response()->noContent();
    }
}