<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ResourceRequest;
use App\Http\Resources\ResourceResource;
use App\Models\Resource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ResourceController extends Controller
{
    public function index(Request $req)
    {
        $perPage = (int) $req->get('perPage', 10);
        $search  = trim((string) $req->get('search', ''));

        $q = Resource::query();

        if ($search !== '') {
            $q->where(function ($w) use ($search) {
                $w->where('name', 'like', "%$search%")
                  ->orWhere('father_name', 'like', "%$search%")
                  ->orWhere('grand_father_name', 'like', "%$search%")
                  ->orWhere('nickname', 'like', "%$search%")
                  ->orWhere('code_number', 'like', "%$search%")
                  ->orWhere('tazkira', 'like', "%$search%")
                  ->orWhere('phone_number', 'like', "%$search%");
            });
        }

        $data = $q->latest('id')->paginate($perPage);

        return ResourceResource::collection($data)
            ->additional(['meta' => ['total' => $data->total()]]);
    }

    public function show($id)
    {
        return new ResourceResource(Resource::findOrFail($id));
    }

    public function store(ResourceRequest $req)
    {
        $data = $req->validated();

        if ($req->hasFile('picture')) {
            $path = $req->file('picture')->store('uploads/resources', 'public');
            $data['picture'] = "storage/{$path}";
        }

        $row = Resource::create($data);

        return (new ResourceResource($row))
            ->additional(['message' => 'Created'])
            ->response()->setStatusCode(201);
    }

    public function update(ResourceRequest $req, $id)
    {
        $row  = Resource::findOrFail($id);
        $data = $req->validated();

        if ($req->hasFile('picture')) {
            $old = $row->picture;
            if ($old && str_starts_with($old, 'storage/')) {
                Storage::disk('public')->delete(str_replace('storage/', '', $old));
            }
            $path = $req->file('picture')->store('uploads/resources', 'public');
            $data['picture'] = "storage/{$path}";
        } else {
            unset($data['picture']);
        }

        $row->fill($data)->save();

        return new ResourceResource($row->fresh());
    }

    public function destroy($id)
    {
        $row = Resource::findOrFail($id);

        if ($row->picture && str_starts_with($row->picture, 'storage/')) {
            Storage::disk('public')->delete(str_replace('storage/', '', $row->picture));
        }

        $row->delete();

        return response()->json(['message' => 'Deleted']);
    }
}
