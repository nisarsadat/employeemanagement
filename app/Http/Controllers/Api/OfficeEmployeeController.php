<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\OfficeEmployeeRequest;
use App\Http\Requests\StoreOfficeEmployeeRequest;
use App\Http\Requests\UpdateOfficeEmployeeRequest;
use App\Http\Resources\OfficeEmployeeResource;
use App\Models\OfficeEmployee;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

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
        if (is_null($rawPerPage) || $rawPerPage === '' || strtolower((string) $rawPerPage) === 'all' || (int) $rawPerPage === 0) {
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
     * ✅ quick counts for UI badges
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
            $user = $request->user(); // 🔔 who did this action

            // Create the employee (without picture first)
            $employee = OfficeEmployee::create($request->validated());

            // Picture
            if ($request->hasFile('picture')) {
                $path = $request->file('picture')->store('uploads/office-employees', 'public');
                $employee->update(['picture' => $path]);
            }

            // Documents
            if ($request->hasFile('documents')) {
                foreach ($request->file('documents') as $file) {
                    $path = $file->store("office_employees/{$employee->id}", 'public');
                    $employee->documents()->create([
                        'file_name'  => $file->getClientOriginalName(),
                        'file_path'  => $path,
                        'mime_type'  => $file->getClientMimeType(),
                        'size_bytes' => $file->getSize(),
                    ]);
                }
            }

            // 🔔 Notification: CREATED → for ALL users (including actor)
            NotificationService::recordForAllUsers(
                actor: $user,
                module: 'کارمند دفتر',
                action: 'created',
                title: 'کارمند دفتر جدید ثبت شد',
                message: 'کد کارمند: ' . ($employee->code_number ?? ''),
                routeName: 'officeemployee',
                routeParams: ['id' => $employee->id],
                level: 'success',
                changes: null,
            );

            DB::commit();

            return new OfficeEmployeeResource(
                $employee->load(['documents'])
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
     *
     * Here we also compute the field changes and store them in notification.changes.
     */
    public function update(UpdateOfficeEmployeeRequest $request, $id)
    {
        DB::beginTransaction();

        try {
            $user = $request->user();

            // Load the employee
            $officeEmployee = OfficeEmployee::with(['documents'])->findOrFail($id);

            // Keep original values for diff
            $original = $officeEmployee->getOriginal();

            // Apply validated fields (in memory)
            $officeEmployee->fill($request->validated());

            // Compute which fields changed
            $dirty   = $officeEmployee->getDirty();
            $changes = [];

            foreach ($dirty as $field => $newValue) {
                $changes[$field] = [
                    'old' => $original[$field] ?? null,
                    'new' => $newValue,
                ];
            }

            // Save updated base fields
            $officeEmployee->save();

            // Picture replace
            if ($request->hasFile('picture')) {
                if ($officeEmployee->picture) {
                    Storage::disk('public')->delete($officeEmployee->picture);
                }

                $path = $request->file('picture')->store('uploads/office-employees', 'public');
                $officeEmployee->update(['picture' => $path]);

                // also track in changes array
                $changes['picture'] = [
                    'old' => $original['picture'] ?? null,
                    'new' => $path,
                ];
            }

            // New documents
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

                // simple flag to show there were document changes
                $changes['documents'] = [
                    'old' => null,
                    'new' => 'updated',
                ];
            }

            // 🔔 Notification: UPDATED → for ALL users
            NotificationService::recordForAllUsers(
                actor: $user,
                module: 'کارمند دفتر',
                action: 'updated',
                title: 'کارمند دفتر اصلاح شد',
                message: 'کد کارمند: ' . ($officeEmployee->code_number ?? ''),
                routeName: 'officeemployee',
                routeParams: ['id' => $officeEmployee->id],
                level: 'info',
                changes: $changes ?: null,
            );

            DB::commit();

            return new OfficeEmployeeResource(
                $officeEmployee
                    ->load(['documents'])
                    ->loadCount(['events', 'resources', 'informations', 'documents'])
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
    public function destroy(Request $request, $id)
    {
        DB::beginTransaction();

        try {
            $officeEmployee = OfficeEmployee::with('documents')->findOrFail($id);
            $user           = $request->user();

            // keep code_number before delete
            $code = $officeEmployee->code_number;

            // Delete files from storage
            foreach ($officeEmployee->documents as $doc) {
                Storage::disk('public')->delete($doc->file_path);
            }

            // Delete DB rows (documents + picture)
            $officeEmployee->documents()->delete();

            if ($officeEmployee->picture) {
                Storage::disk('public')->delete($officeEmployee->picture);
            }

            $officeEmployee->delete();

            // 🔔 Notification: DELETED → for ALL users
            NotificationService::recordForAllUsers(
                actor: $user,
                module: 'کارمند دفتر',
                action: 'deleted',
                title: 'کارمند دفتر حذف شد',
                message: 'کد کارمند: ' . ($code ?? ''),
                routeName: null,
                routeParams: ['officeemployee'],
                level: 'warning',
                changes: null,
            );

            DB::commit();

            return response()->json(['message' => 'Office employee deleted successfully']);

        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Office employee delete failed',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * List of NON-ACTIVE office employees.
     */
    public function inactive(Request $request)
    {
        $query = OfficeEmployee::query()
            ->where('activity', 'غیر فعال')
            ->with(['documents'])
            ->withCount(['events', 'resources', 'informations', 'documents'])
            ->orderByDesc('id');

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
