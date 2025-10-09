<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use App\Models\Resedint;
use App\Http\Requests\ResedintRequest;
use App\Http\Resources\ResedintResource;

class ResedintController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) $request->input('perPage', 10);
        $search  = $request->input('search');

        $items = Resedint::query()
            ->search($search)
            ->latest()
            ->paginate($perPage);

        return ResedintResource::collection($items);
    }

    public function store(ResedintRequest $request)
    {
        $data = $request->validated();

        if ($request->hasFile('picture')) {
            $path = $request->file('picture')->store('uploads/resedints', 'public');
            $data['picture'] = "storage/{$path}";
        }

        $item = Resedint::create($data);
        return ResedintResource::make($item);
    }

    public function show(Resedint $resedint)
    {
        return ResedintResource::make($resedint);
    }

    public function update(ResedintRequest $request, Resedint $resedint)
    {
        $data = $request->validated();

        if ($request->hasFile('picture')) {
            if ($resedint->picture && str_starts_with($resedint->picture, 'storage/')) {
                Storage::disk('public')->delete(str_replace('storage/', '', $resedint->picture));
            }
            $path = $request->file('picture')->store('uploads/resedints', 'public');
            $data['picture'] = "storage/{$path}";
        } else {
            unset($data['picture']);
        }

        $resedint->update($data);
        return ResedintResource::make($resedint);
    }

    public function destroy(Resedint $resedint)
    {
        if ($resedint->picture && str_starts_with($resedint->picture, 'storage/')) {
            Storage::disk('public')->delete(str_replace('storage/', '', $resedint->picture));
        }
        $resedint->delete();
        return response()->noContent();
    }

    // optional
    public function bulkDelete(Request $request)
    {
        $ids = $request->input('resedintIds', []);
        Resedint::destroy($ids);
        return response()->noContent();
    }
}
