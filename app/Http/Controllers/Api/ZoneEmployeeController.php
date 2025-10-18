<?php

namespace App\Http\Controllers\Api;

use App\Models\ZoneEmployee;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\ZoneEmployeeResource;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class ZoneEmployeeController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) $request->input('perPage', 10);
        $search  = $request->input('search');

        $q = ZoneEmployee::with('documents')->search($search)->latest();
        return ZoneEmployeeResource::collection($q->paginate($perPage));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'picture'           => ['nullable','image'],
            'name'              => ['required','string'],
            'father_name'       => ['nullable','string'],
            'nickname'          => ['nullable','string'],
            'code_number'       => ['nullable','string','unique:zone_employees,code_number'],
            'hawza'             => ['nullable','string'],
            'zone'              => ['nullable','string'],
            'degree'            => ['nullable','string'],
            'resedint_id'       => ['nullable','exists:resedints,id'],
            'zone_entry_hour'   => ['nullable','string'],
            'notes'             => ['nullable','string'],
            'documents.*'       => ['file','max:10240'],
        ]);

        DB::beginTransaction();
        try {
            // create
            $payload = $validated;
            unset($payload['documents']);
            if ($request->hasFile('picture')) {
                $p = $request->file('picture')->store('uploads/zone-employees', 'public');
                $payload['picture'] = "storage/{$p}";
            }

            $ze = ZoneEmployee::create($payload);

            // docs
            if ($request->hasFile('documents')) {
                foreach ($request->file('documents') as $file) {
                    $path = $file->store("zone_employees/{$ze->id}", 'public');
                    $ze->documents()->create([
                        'file_name'  => $file->getClientOriginalName(),
                        'file_path'  => $path,
                        'mime_type'  => $file->getClientMimeType(),
                        'size_bytes' => $file->getSize(),
                    ]);
                }
            }

            DB::commit();
            return new ZoneEmployeeResource($ze->load('documents'));
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['message' => 'Create failed','error' => $e->getMessage()], 500);
        }
    }

    public function show(ZoneEmployee $zoneEmployee)
    {
        return new ZoneEmployeeResource($zoneEmployee->load('documents'));
    }

    public function update(Request $request, ZoneEmployee $zoneEmployee)
    {
        $validated = $request->validate([
            'picture'           => ['nullable','image','max:2048'],
            'name'              => ['sometimes','required','string','max:255'],
            'father_name'       => ['sometimes','nullable','string','max:255'],
            'nickname'          => ['sometimes','nullable','string','max:255'],
            'code_number'       => ['sometimes','nullable','string','max:255','unique:zone_employees,code_number,'.$zoneEmployee->id],
            'hawza'             => ['sometimes','nullable','string','max:255'],
            'zone'              => ['sometimes','nullable','string','max:255'],
            'degree'            => ['sometimes','nullable','string','max:255'],
            'resedint_id'       => ['sometimes','nullable','exists:resedints,id'],
            'zone_entry_hour'   => ['sometimes','nullable','string','max:255'],
            'notes'             => ['sometimes','nullable','string'],
            'documents.*'       => ['file','max:10240'],
            'removed_document_ids'   => ['array'],
            'removed_document_ids.*' => ['integer','exists:zone_employee_documents,id'],
        ]);

        DB::beginTransaction();
        try {
            // base fields (exclude docs)
            $payload = $validated;
            unset($payload['documents'], $payload['removed_document_ids']);

            // picture replace
            if ($request->hasFile('picture')) {
                if ($zoneEmployee->picture && str_starts_with($zoneEmployee->picture, 'storage/')) {
                    $diskPath = str_replace('storage/', '', $zoneEmployee->picture);
                    Storage::disk('public')->delete($diskPath);
                }
                $p = $request->file('picture')->store('uploads/zone-employees', 'public');
                $payload['picture'] = "storage/{$p}";
            }

            $zoneEmployee->update($payload);

            // removals
            if ($ids = $request->input('removed_document_ids')) {
                $docs = $zoneEmployee->documents()->whereIn('id', $ids)->get();
                foreach ($docs as $doc) {
                    Storage::disk('public')->delete($doc->file_path);
                    $doc->delete();
                }
            }

            // new docs
            if ($request->hasFile('documents')) {
                foreach ($request->file('documents') as $file) {
                    $path = $file->store("zone_employees/{$zoneEmployee->id}", 'public');
                    $zoneEmployee->documents()->create([
                        'file_name'  => $file->getClientOriginalName(),
                        'file_path'  => $path,
                        'mime_type'  => $file->getClientMimeType(),
                        'size_bytes' => $file->getSize(),
                    ]);
                }
            }

            DB::commit();
            return new ZoneEmployeeResource($zoneEmployee->load('documents'));
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['message' => 'Update failed','error' => $e->getMessage()], 500);
        }
    }

    public function destroy(ZoneEmployee $zoneEmployee)
    {
        $zoneEmployee->load('documents');

        foreach ($zoneEmployee->documents as $doc) {
            Storage::disk('public')->delete($doc->file_path);
        }
        if ($zoneEmployee->picture && str_starts_with($zoneEmployee->picture, 'storage/')) {
            $diskPath = str_replace('storage/', '', $zoneEmployee->picture);
            Storage::disk('public')->delete($diskPath);
        }

        $zoneEmployee->documents()->delete();
        $zoneEmployee->delete();

        return response()->json(['message' => 'Deleted successfully']);
    }

    // Optional: single-doc delete
    public function destroyDocument(ZoneEmployee $zoneEmployee, \App\Models\ZoneEmployeeDocument $document)
    {
        abort_unless($document->zone_employee_id === $zoneEmployee->id, 404);
        Storage::disk('public')->delete($document->file_path);
        $document->delete();
        return response()->json(['message' => 'Document deleted']);
    }
}
