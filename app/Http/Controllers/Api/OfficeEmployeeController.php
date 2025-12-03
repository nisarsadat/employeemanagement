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
            'status'  => 'nullable|string', // 'active' | 'inactive' | 'فعال' | 'غیر فعال' | 'all'
        ]);

        $search     = $request->input('search');
        $rawPerPage = $request->input('perPage', null);
        $page       = (int) $request->input('page', 1);
        $status     = $request->input('status', 'all');

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
            // ✅ filter by activity (accepts both Dari and English)
            ->when($status && strtolower($status) !== 'all', function ($q) use ($status) {
                $activeVals   = ['active', OfficeEmployee::ACTIVE];     // 'active' | 'فعال'
                $inactiveVals = ['inactive', OfficeEmployee::INACTIVE]; // 'inactive' | 'غیر فعال'

                if (in_array($status, $activeVals, true)) {
                    $q->where('activity', OfficeEmployee::ACTIVE);
                } elseif (in_array($status, $inactiveVals, true)) {
                    $q->where('activity', OfficeEmployee::INACTIVE);
                }
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

    public function show($id)
    {
        $officeEmployee = OfficeEmployee::with(['documents'])
            ->withCount(['events', 'resources', 'informations', 'documents'])
            ->findOrFail($id);

        return new OfficeEmployeeResource($officeEmployee);
    }

    /**
     * ✅ NEW: quick counts for UI badges
     */
    public function counts()
    {
        return response()->json([
            'active'   => OfficeEmployee::query()->where('activity', OfficeEmployee::ACTIVE)->count(),
            'inactive' => OfficeEmployee::query()->where('activity', OfficeEmployee::INACTIVE)->count(),
            'total'    => OfficeEmployee::count(),
        ]);
    }

    /**
     * Store a new office employee.
     */
    public function store(OfficeEmployeeRequest $request)
    {
        DB::beginTransaction();

        try {
            // ✅ Create the employee (without picture first)
            $employee = OfficeEmployee::create($request->validated());

            // 🖼️ Optional: handle profile picture
            if ($request->hasFile('picture')) {
                // store as "uploads/office-employees/..."
                $path = $request->file('picture')->store('uploads/office-employees', 'public');

                // ✅ store ONLY relative path (no "storage/" prefix)
                $employee->update(['picture' => $path]);
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
    public function update(UpdateOfficeEmployeeRequest $request, $id)
    {
        DB::beginTransaction();

        try {
            // Load the employee
            $officeEmployee = OfficeEmployee::with(['documents'])->findOrFail($id);

            // Update base fields
            $officeEmployee->update($request->validated());

            // Optional: replace profile picture
            if ($request->hasFile('picture')) {
                // delete old file if present (now stored as relative path)
                if ($officeEmployee->picture) {
                    Storage::disk('public')->delete($officeEmployee->picture);
                }

                $path = $request->file('picture')->store('uploads/office-employees', 'public');

                // ✅ store ONLY relative path (no "storage/" prefix)
                $officeEmployee->update(['picture' => $path]);
            }

            // Handle new documents
            if ($request->hasFile('documents')) {
                foreach ($request->file('documents') as $file) {
                    $path = $file->store("office_employees/{$officeEmployee->id}", 'public');
                    $officeEmployee->documents()->create([
                        'file_name'  => $file->getClientOriginalName(),
                        'file_path'  => $path,
                        'mime_type'  => $file->getClientMimeType(),
                        'size_bytes' => $file->getSize(),
                    ]);
                }
            }

            DB::commit();

            // Return fresh
            return new OfficeEmployeeResource(
                $officeEmployee->load(['documents'])->loadCount(['events', 'resources', 'informations', 'documents'])
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
    public function destroy($id)
    {
        $officeEmployee = OfficeEmployee::with('documents')->findOrFail($id);

        // Delete files from storage
        foreach ($officeEmployee->documents as $doc) {
            Storage::disk('public')->delete($doc->file_path);
        }

        // Delete DB records
        $officeEmployee->documents()->delete();

        // Delete profile picture if present (now relative path)
        if ($officeEmployee->picture) {
            Storage::disk('public')->delete($officeEmployee->picture);
        }

        $officeEmployee->delete();

        return response()->json(['message' => 'Office employee deleted successfully']);
    }

    // -------------------------------------------------------------
    // 🔹 List of NON-ACTIVE office employees
    // -------------------------------------------------------------
    public function inactive(Request $request)
    {
        $query = OfficeEmployee::query()
            ->where('activity', 'غیر فعال')
            ->with(['documents'])
            ->withCount(['events', 'resources', 'informations', 'documents'])
            ->orderByDesc('id');

        // optional search and pagination
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code_number', 'like', "%{$search}%")
                  ->orWhere('phone_number', 'like', "%{$search}%");
            });
        }

        $perPage = $request->input('perPage', 0);
        if (!$perPage || strtolower($perPage) === 'all') {
            return OfficeEmployeeResource::collection($query->get());
        }

        return OfficeEmployeeResource::collection($query->paginate($perPage));
    }
}
