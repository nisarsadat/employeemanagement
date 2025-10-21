<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\OfficeEmployeeRequest;
use App\Models\OfficeEmployee;
use Illuminate\Http\Request;
use App\Http\Requests\StoreOfficeEmployeeRequest;
use App\Http\Requests\UpdateOfficeEmployeeRequest;
use App\Http\Resources\OfficeEmployeeResource;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class OfficeEmployeeController extends Controller
{
    /**
     * List all office employees with search and documents.
     */

    public function index(Request $request)
    {
        $request->validate([
            'page'    => 'sometimes|integer|min:1',
            'perPage' => 'sometimes', // int | "all" | missing
            'search'  => 'nullable|string',
        ]);

        $search     = $request->input('search');
        $rawPerPage = $request->input('perPage', null);
        $page       = (int) $request->input('page', 1);

        $query = OfficeEmployee::query()
            ->with(['documents'])
            ->withCount([
                'events',
                'resources',
                'informations',
                'documents',
            ])
            ->when($search, function ($q) use ($search) {
                $q->where(function ($qq) use ($search) {
                    $qq->where('name', 'like', "%{$search}%")
                    ->orWhere('code_number', 'like', "%{$search}%")
                    ->orWhere('phone_number', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('id');

        // Return ALL if perPage missing / "all" / 0
        if (is_null($rawPerPage) || $rawPerPage === '' || strtolower((string)$rawPerPage) === 'all' || (int)$rawPerPage === 0) {
            $items = $query->get();
            return OfficeEmployeeResource::collection($items);
        }

        $perPage = max(1, (int) $rawPerPage);

        return OfficeEmployeeResource::collection(
            $query->paginate($perPage, ['*'], 'page', $page)
        );
    }

    public function show(OfficeEmployee $officeEmployee)
    {
        return new OfficeEmployeeResource(
            $officeEmployee
                ->load(['documents'])
                ->loadCount([
                    'events',
                    'resources',
                    'informations',
                    'documents',
                ])
        );
    }


    /**
     * Store a new office employee.
     */
    public function store(OfficeEmployeeRequest $request)
    {
        DB::beginTransaction();

        try {
            // ✅ Create the employee
            $employee = OfficeEmployee::create($request->validated());

            // 🖼️ Optional: handle profile picture
            if ($request->hasFile('picture')) {
                $path = $request->file('picture')->store('uploads/office-employees', 'public');
                $employee->update(['picture' => "storage/{$path}"]);
            }

            // 📎 Handle document uploads
            if ($request->hasFile('documents')) {
                foreach ($request->file('documents') as $file) {
                    $path = $file->store("office_employees/{$employee->id}", 'public'); // ✅ fixed braces/spacing
                    $employee->documents()->create([
                        'file_name'  => $file->getClientOriginalName(),
                        'file_path'  => $path,
                        'mime_type'  => $file->getClientMimeType(),
                        'size_bytes' => $file->getSize(),
                    ]);
                }
            }

            DB::commit();

            return new OfficeEmployeeResource(
                $employee->load(['documents']) // ✅ load before return
            );

        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Office employee creation failed',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }



    /**
     * Update an existing office employee.
     */
    public function update(UpdateOfficeEmployeeRequest $request, OfficeEmployee $officeEmployee)
    {
        DB::beginTransaction();

        try {
            // ✅ Update base fields
            $officeEmployee->update($request->validated());

            // 🖼️ Optional: replace profile picture
            if ($request->hasFile('picture')) {
                if ($officeEmployee->picture && str_starts_with($officeEmployee->picture, 'storage/')) {
                    $diskPath = str_replace('storage/', '', $officeEmployee->picture);
                    Storage::disk('public')->delete($diskPath);
                }
                $path = $request->file('picture')->store('uploads/office-employees', 'public');
                $officeEmployee->update(['picture' => "storage/{$path}"]);
            }

            // 📎 Handle new documents
            if ($request->hasFile('documents')) {
                foreach ($request->file('documents') as $file) {
                    $path = $file->store("office_employees/{$officeEmployee->id}", 'public'); // ✅ fixed braces/spacing
                    $officeEmployee->documents()->create([
                        'file_name'  => $file->getClientOriginalName(),
                        'file_path'  => $path,
                        'mime_type'  => $file->getClientMimeType(),
                        'size_bytes' => $file->getSize(),
                    ]);
                }
            }

            DB::commit();

            return new OfficeEmployeeResource(
                $officeEmployee->load(['documents']) // ✅ load before return
            );

        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Office employee update failed',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete an office employee and their documents.
     */
    public function destroy(OfficeEmployee $officeEmployee)
    {
        // 🗑️ Delete files from storage
        foreach ($officeEmployee->documents as $doc) {
            Storage::disk('public')->delete($doc->file_path);
        }

        // 🗑️ Delete DB records
        $officeEmployee->documents()->delete();

        // 🖼️ Delete profile picture if present
        if ($officeEmployee->picture && str_starts_with($officeEmployee->picture, 'storage/')) {
            $diskPath = str_replace('storage/', '', $officeEmployee->picture);
            Storage::disk('public')->delete($diskPath);
        }

        $officeEmployee->delete();

        return response()->json(['message' => 'Office employee deleted successfully']);
    }
}
