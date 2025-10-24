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
        $search = trim((string)$req->get('search', ''));

        $q = Resource::query()
            ->with(['officeEmployee:id,name,code_number,father_name'])
            ->when($search !== '', function ($w) use ($search) {
                $w->where(function ($q2) use ($search) {
                    $q2->where('name', 'like', "%$search%")
                       ->orWhere('father_name', 'like', "%$search%")
                       ->orWhere('grand_father_name', 'like', "%$search%")
                       ->orWhere('nick_name', 'like', "%$search%")
                       ->orWhere('code_number', 'like', "%$search%")
                       ->orWhere('tazkira', 'like', "%$search%")
                       ->orWhere('phone_number', 'like', "%$search%");
                });
            })
            ->latest('id');

        // If perPage is provided and valid (>0), paginate (honors ?page=)
        $perPage = $req->filled('perPage') ? (int) $req->get('perPage') : null;
        if ($perPage !== null && $perPage > 0) {
            $data = $q->paginate($perPage);
            return ResourceResource::collection($data)
                ->additional(['meta' => ['total' => $data->total()]]);
        }

        // Otherwise return all
        $rows = $q->get();
        return ResourceResource::collection($rows)
            ->additional(['meta' => ['total' => $rows->count()]]);
    }

    public function show($id)
    {
        $row = Resource::with(['officeEmployee:id,name,code_number,father_name,nickname'])
            ->findOrFail($id);

        return new ResourceResource($row);
    }

    public function store(ResourceRequest $req)
    {
        $data = $req->validated();

        // Image upload (optional)
        if ($req->hasFile('picture')) {
            $path = $req->file('picture')->store('uploads/resources', 'public');
            $data['picture'] = "storage/{$path}";
        }

        $row = Resource::create($data);

        return (new ResourceResource($row->loadMissing('officeEmployee:id,name,code_number,father_name')))
            ->additional(['message' => 'Created'])
            ->response()->setStatusCode(201);
    }

    public function update(ResourceRequest $req, $id)
    {
        $row  = Resource::findOrFail($id);
        $data = $req->validated();

        // Replace image if a new one is provided
        if ($req->hasFile('picture')) {
            if ($row->picture && str_starts_with($row->picture, 'storage/')) {
                Storage::disk('public')->delete(str_replace('storage/', '', $row->picture));
            }
            $path = $req->file('picture')->store('uploads/resources', 'public');
            $data['picture'] = "storage/{$path}";
        } else {
            unset($data['picture']); // don't null out existing if not sent
        }

        $row->fill($data)->save();

        return new ResourceResource($row->loadMissing('officeEmployee:id,name,code_number,father_name'));
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
