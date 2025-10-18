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
        // ✅ Validate/sanitize pagination & search
        $request->validate([
            'page'    => 'sometimes|integer|min:1',
            'perPage' => 'sometimes', // allow 'all' (string) from UI
            'search'  => 'nullable|string',
        ]);

        $rawPerPage = $request->input('perPage', 15);
        $page       = (int) $request->input('page', 1);
        $search     = $request->input('search');

        $query = OfficeEmployee::query()
            ->with(['documents'])
            // ✅ Grouped search to avoid OR leaking outside
            ->when($search, function ($q) use ($search) {
                $q->where(function ($qq) use ($search) {
                    $qq->where('name', 'like', "%{$search}%")
                       ->orWhere('code_number', 'like', "%{$search}%")
                       ->orWhere('phone_number', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('id');

        // ✅ Support "all" without breaking paginator math
        if ($rawPerPage === 'all') {
            $items = $query->get();
            return OfficeEmployeeResource::collection($items);
        }

        $perPage = (int) $rawPerPage ?: 15;

        return OfficeEmployeeResource::collection(
            $query->paginate($perPage, ['*'], 'page', $page)
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
     * Show a single office employee with documents.
     */
    public function show(OfficeEmployee $officeEmployee)
    {
        return new OfficeEmployeeResource(
            $officeEmployee->load(['documents']) // ✅ load before return
        );
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
