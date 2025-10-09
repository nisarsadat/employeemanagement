<?php

namespace App\Http\Controllers;

use App\Models\Information;
use App\Models\InformationDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class InformationController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) $request->query('perPage', 10);
        $search  = trim((string) $request->query('search', ''));

        $q = Information::query();

        if ($search !== '') {
            $q->where(function ($sub) use ($search) {
                $sub->where('name', 'like', "%{$search}%")
                    ->orWhere('father_name', 'like', "%{$search}%")
                    ->orWhere('nickname', 'like', "%{$search}%");
            });
        }

        $q->with([
            'resource',
            'officeEmployee',
            'zoneEmployee',
            'infoOfficeEmployee',
            'infoZoneEmployee',
            'documents',
        ]);

        $data = $q->orderByDesc('id')->paginate($perPage);

        return response()->json([
            'data' => $data->items(),
            'meta' => [
                'current_page' => $data->currentPage(),
                'per_page'     => $data->perPage(),
                'total'        => $data->total(),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'resource_id'  => ['nullable','integer','exists:resources,id'],
            'officeemployee_id' => ['nullable','integer','exists:office_employees,id'],
            'zone_employee_id'  => ['nullable','integer','exists:zone_employees,id'],

            'information_office_employee_code' => ['nullable','integer','exists:office_employees,id'],
            'infomation_zone_employee_code'    => ['nullable','integer','exists:zone_employees,id'],

            'name' => ['required','string'],
            'father_name' => ['nullable','string'],
            'nickname' => ['nullable','string'],
            'grand_father_name' => ['nullable','string'],

            'main_province' => ['nullable','string'],
            'main_district' => ['nullable','string'],
            'main_village'  => ['nullable','string'],

            'now_province' => ['nullable','string'],
            'now_district' => ['nullable','string'],
            'now_village'  => ['nullable','string'],

            'seyasi' => ['nullable','string'],
            'jenayee' => ['nullable','string'],

            'information_date' => ['nullable','string'],
            'information_hour' => ['nullable','string'],

            'importance_degree'    => ['nullable','string'],
            'information_category' => ['nullable','string'],

            'tafseel' => ['nullable','string'],
            'talks'   => ['nullable','string'],
            'outcome' => ['nullable','string'],
            'notes'   => ['nullable','string'],

            // documents[]
            'documents'   => ['nullable','array'],
            'documents.*' => ['file'], // 50MB (tune as needed)
        ]);

        return DB::transaction(function () use ($request, $validated) {
            $info = Information::create($validated);

            // handle documents[]
            if ($request->hasFile('documents')) {
                foreach ($request->file('documents', []) as $file) {
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

            $info->load([
                'resource',
                'officeEmployee',
                'zoneEmployee',
                'infoOfficeEmployee',
                'infoZoneEmployee',
                'documents',
            ]);

            return response()->json(['data' => $info], 201);
        });
    }

    public function show(Information $information)
    {
        $information->load([
            'resource',
            'officeEmployee',
            'zoneEmployee',
            'infoOfficeEmployee',
            'infoZoneEmployee',
            'documents',
        ]);

        return response()->json(['data' => $information]);
    }

    public function update(Request $request, Information $information)
    {
        $validated = $request->validate([
            'resource_id'  => ['nullable','integer','exists:resources,id'],
            'officeemployee_id' => ['nullable','integer','exists:office_employees,id'],
            'zone_employee_id'  => ['nullable','integer','exists:zone_employees,id'],

            'information_office_employee_code' => ['nullable','integer','exists:office_employees,id'],
            'infomation_zone_employee_code'    => ['nullable','integer','exists:zone_employees,id'],

            'name' => ['sometimes','required','string'],
            'father_name' => ['nullable','string'],
            'nickname' => ['nullable','string'],
            'grand_father_name' => ['nullable','string'],

            'main_province' => ['nullable','string'],
            'main_district' => ['nullable','string'],
            'main_village'  => ['nullable','string'],

            'now_province' => ['nullable','string'],
            'now_district' => ['nullable','string'],
            'now_village'  => ['nullable','string'],

            'seyasi' => ['nullable','string'],
            'jenayee' => ['nullable','string'],

            'information_date' => ['nullable','string'],
            'information_hour' => ['nullable','string'],

            'importance_degree'    => ['nullable','string'],
            'information_category' => ['nullable','string'],

            'tafseel' => ['nullable','string'],
            'talks'   => ['nullable','string'],
            'outcome' => ['nullable','string'],
            'notes'   => ['nullable','string'],

            // documents[]
            'documents'   => ['nullable','array'],
            'documents.*' => ['file'],
        ]);

        return DB::transaction(function () use ($request, $information, $validated) {
            $information->update($validated);

            // append new documents if any
            if ($request->hasFile('documents')) {
                foreach ($request->file('documents', []) as $file) {
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

            $information->load([
                'resource',
                'officeEmployee',
                'zoneEmployee',
                'infoOfficeEmployee',
                'infoZoneEmployee',
                'documents',
            ]);

            return response()->json(['data' => $information]);
        });
    }

    public function destroy(Information $information)
    {
        // delete all document files from disk first
        foreach ($information->documents as $doc) {
            Storage::disk('public')->delete($doc->file_path);
        }

        $information->delete();

        return response()->json(['message' => 'Deleted']);
    }
}
