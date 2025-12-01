<?php

namespace App\Http\Controllers;

use App\Models\Information;
use App\Models\InformationDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\StoreInformationRequest;
use App\Http\Requests\UpdateInformationRequest;
use App\Http\Resources\InformationResource;

class InformationController extends Controller
{
    public function index(Request $request)
    {
        $page    = $request->query('page');
        $perPage = $request->query('perPage', $request->query('per_page'));

        $isValidPage    = is_numeric($page) && (int) $page > 0;
        $isValidPerPage = is_numeric($perPage) && (int) $perPage > 0;

        $search = trim((string) $request->query('search', ''));

        $q = Information::query()
            ->with(['resource', 'officeEmployee', 'documents'])
            ->when($search !== '', function ($sub) use ($search) {
                $sub->where(function ($x) use ($search) {
                    $x->where('name', 'like', "%{$search}%")
                      ->orWhere('father_name', 'like', "%{$search}%")
                      ->orWhere('nick_name', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('id');

        if ($isValidPage && $isValidPerPage) {
            $data = $q->paginate((int) $perPage, ['*'], 'page', (int) $page);
            return InformationResource::collection($data);
        }

        return InformationResource::collection($q->get());
    }

    public function store(StoreInformationRequest $request)
    {
        return DB::transaction(function () use ($request) {
            $info = Information::create($request->validated());

            if ($request->hasFile('documents')) {
                foreach ($request->file('documents') as $file) {
                    $path = $file->store("information_docs/{$info->id}", 'public');
                    InformationDocument::create([
                        'information_id' => $info->id,
                        'file_name'      => $file->getClientOriginalName(),
                        'file_path'      => $path,
                        'mime_type'      => $file->getMimeType(),
                        'size_bytes'     => $file->getSize(),
                    ]);
                }
            }

            $info->load(['resource', 'officeEmployee', 'documents']);

            return new InformationResource($info);
        });
    }

    public function show($id)
    {
        // 🔹 Fetch by id + relations (no route model binding confusion)
        $information = Information::with(['resource', 'officeEmployee', 'documents'])
            ->findOrFail($id);

        return new InformationResource($information);
    }

    public function update(UpdateInformationRequest $request, $id)
    {
        return DB::transaction(function () use ($request, $id) {
            $information = Information::with(['resource', 'officeEmployee', 'documents'])
                ->findOrFail($id);

            $information->update($request->validated());

            if ($request->hasFile('documents')) {
                foreach ($request->file('documents') as $file) {
                    $path = $file->store("information_docs/{$information->id}", 'public');
                    InformationDocument::create([
                        'information_id' => $information->id,
                        'file_name'      => $file->getClientOriginalName(),
                        'file_path'      => $path,
                        'mime_type'      => $file->getMimeType(),
                        'size_bytes'     => $file->getSize(),
                    ]);
                }
            }

            $information->load(['resource', 'officeEmployee', 'documents']);

            return new InformationResource($information);
        });
    }

    public function destroy($id)
    {
        $information = Information::with('documents')->findOrFail($id);

        foreach ($information->documents as $doc) {
            Storage::disk('public')->delete($doc->file_path);
        }

        $information->delete();

        return response()->json(['message' => 'Deleted']);
    }
}
