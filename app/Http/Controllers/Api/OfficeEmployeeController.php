<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\OfficeEmployeeRequest;
use App\Http\Requests\UpdateOfficeEmployeeRequest;
use App\Http\Resources\OfficeEmployeeResource;
use App\Models\OfficeEmployee;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\QueryException;

class OfficeEmployeeController extends Controller
{
    // =========================
    // ✅ DUPLICATE HELPERS
    // =========================
    private function isDuplicateKey(QueryException $e): bool
    {
        return (int) ($e->errorInfo[1] ?? 0) === 1062;
    }

    private function duplicateResponsePashto()
    {
        return response()->json([
            'message' => 'دا کارمند مخکې ثبت شوی دی.',
            'errors'  => [
                'duplicate' => [
                    'په همدې نوم، د پلار نوم، د نيکه نوم او د اصلي استوګنې ځای (ولايت، ولسوالۍ، کلی) سره کارمند مخکې په سیستم کې موجود دی.',
                ],
            ],
        ], 422);
    }

    // =========================
    // ✅ DATE FILTER HELPERS
    // =========================
    private function resolveDateField(?string $dateField): string
    {
        $allowed = ['join_date', 'left_date', 'created_at'];

        $field = trim((string) $dateField);
        if ($field === '' || !in_array($field, $allowed, true)) {
            return 'join_date';
        }

        return $field;
    }

    private function applyMonthYearFilter($query, ?int $year, ?int $month, string $dateField)
    {
        if ($year) {
            $query->whereYear($dateField, $year);
        }

        if ($month) {
            $query->whereMonth($dateField, $month);
        }

        return $query;
    }

    // ✅ keep the main list query consistent everywhere
    private function baseListQuery()
    {
        return OfficeEmployee::query()
            ->with(['documents'])
            ->withSum('finances as payed_money', 'money_amount_number')
            ->withCount([
                'events',
                'resources',
                'resources as resources_active_count' => fn ($q) => $q->where('activity', 'فعال'),
                'resources as resources_inactive_count' => fn ($q) => $q->where('activity', 'غیر فعال'),
                'informations',
                'infoInformations',
                'documents',
            ]);
    }

    /**
     * ✅ NEW: Return available years/months based on REAL DB data.
     *
     * Query params:
     * - date_field (join_date|left_date|created_at) default join_date
     * - year (optional) if provided, returns available months for that year too
     *
     * Response:
     * {
     *   "date_field": "join_date",
     *   "years": [2026,2025,2024],
     *   "months": [1,2,3] // only when year is given
     * }
     */
    public function dateOptions(Request $request)
    {
        $request->validate([
            'date_field' => 'nullable|string',
            'year'       => 'nullable|integer|min:1900|max:2500',
        ]);

        $dateField = $this->resolveDateField($request->input('date_field'));
        $year      = $request->filled('year') ? (int) $request->input('year') : null;

        $base = OfficeEmployee::query()->whereNotNull($dateField);

        $years = (clone $base)
            ->selectRaw("YEAR($dateField) as y")
            ->distinct()
            ->orderByDesc('y')
            ->pluck('y')
            ->filter()
            ->values()
            ->all();

        $months = [];
        if ($year) {
            $months = (clone $base)
                ->whereYear($dateField, $year)
                ->selectRaw("MONTH($dateField) as m")
                ->distinct()
                ->orderBy('m')
                ->pluck('m')
                ->filter()
                ->values()
                ->all();
        }

        return response()->json([
            'date_field' => $dateField,
            'years'      => $years,
            'months'     => $months,
        ]);
    }

    /**
     * List all office employees with search + month/year filters.
     */
    public function index(Request $request)
    {
        $request->validate([
            'page'       => 'sometimes|integer|min:1',
            'perPage'    => 'sometimes',
            'search'     => 'nullable|string',
            'status'     => 'nullable|string',
            'year'       => 'nullable|integer|min:1900|max:2500',
            'month'      => 'nullable|integer|min:1|max:12',
            'date_field' => 'nullable|string',
        ]);

        $search     = $request->input('search');
        $rawPerPage = $request->input('perPage', null);
        $page       = (int) $request->input('page', 1);
        $status     = $request->input('status', 'all');

        $year      = $request->filled('year') ? (int) $request->input('year') : null;
        $month     = $request->filled('month') ? (int) $request->input('month') : null;
        $dateField = $this->resolveDateField($request->input('date_field'));

        $query = $this->baseListQuery()
            ->when($search, function ($q) use ($search) {
                $q->where(function ($qq) use ($search) {
                    $qq->where('name', 'like', "%{$search}%")
                       ->orWhere('code_number', 'like', "%{$search}%")
                       ->orWhere('phone_number', 'like', "%{$search}%");
                });
            })
            ->when($status && strtolower($status) !== 'all', function ($q) use ($status) {
                $activeVals   = ['active', OfficeEmployee::ACTIVE];
                $inactiveVals = ['inactive', OfficeEmployee::INACTIVE];

                if (in_array($status, $activeVals, true)) {
                    $q->active();
                } elseif (in_array($status, $inactiveVals, true)) {
                    $q->inactive();
                }
            });

        $this->applyMonthYearFilter($query, $year, $month, $dateField);

        $query->orderByDesc('id');

        if (is_null($rawPerPage) || $rawPerPage === '' || strtolower((string) $rawPerPage) === 'all' || (int) $rawPerPage === 0) {
            return OfficeEmployeeResource::collection($query->get());
        }

        return OfficeEmployeeResource::collection(
            $query->paginate(max(1, (int) $rawPerPage), ['*'], 'page', $page)
        );
    }

    public function show($id)
    {
        $officeEmployee = $this->baseListQuery()->findOrFail($id);
        return new OfficeEmployeeResource($officeEmployee);
    }

    public function counts()
    {
        return response()->json([
            'active'   => OfficeEmployee::query()->active()->count(),
            'inactive' => OfficeEmployee::query()->inactive()->count(),
            'total'    => OfficeEmployee::count(),
        ]);
    }

    public function store(OfficeEmployeeRequest $request)
    {
        DB::beginTransaction();

        try {
            $user = $request->user();

            $employee = OfficeEmployee::create($request->validated());

            if ($request->hasFile('picture')) {
                $path = $request->file('picture')->store('uploads/office-employees', 'public');
                $employee->update(['picture' => $path]);
            }

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
                    ->loadSum('finances as payed_money', 'money_amount_number')
                    ->loadCount([
                        'events',
                        'resources',
                        'resources as resources_active_count' => fn ($q) => $q->where('activity', 'فعال'),
                        'resources as resources_inactive_count' => fn ($q) => $q->where('activity', 'غیر فعال'),
                        'informations',
                        'infoInformations',
                        'documents',
                    ])
            );

        } catch (QueryException $e) {
            DB::rollBack();

            if ($this->isDuplicateKey($e)) {
                return $this->duplicateResponsePashto();
            }

            return response()->json([
                'message' => 'Office employee creation failed',
                'error'   => $e->getMessage(),
            ], 500);

        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Office employee creation failed',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function update(UpdateOfficeEmployeeRequest $request, $id)
    {
        DB::beginTransaction();

        try {
            $user = $request->user();
            $officeEmployee = OfficeEmployee::with(['documents'])->findOrFail($id);

            $original = $officeEmployee->getOriginal();
            $officeEmployee->fill($request->validated());

            $dirty   = $officeEmployee->getDirty();
            $changes = [];

            foreach ($dirty as $field => $newValue) {
                $changes[$field] = [
                    'old' => $original[$field] ?? null,
                    'new' => $newValue,
                ];
            }

            $officeEmployee->save();

            if ($request->hasFile('picture')) {
                if ($officeEmployee->picture) {
                    Storage::disk('public')->delete($officeEmployee->picture);
                }

                $path = $request->file('picture')->store('uploads/office-employees', 'public');
                $officeEmployee->update(['picture' => $path]);

                $changes['picture'] = [
                    'old' => $original['picture'] ?? null,
                    'new' => $path,
                ];
            }

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

                $changes['documents'] = [
                    'old' => null,
                    'new' => 'updated',
                ];
            }

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
                    ->loadSum('finances as payed_money', 'money_amount_number')
                    ->loadCount([
                        'events',
                        'resources',
                        'resources as resources_active_count' => fn ($q) => $q->where('activity', 'فعال'),
                        'resources as resources_inactive_count' => fn ($q) => $q->where('activity', 'غیر فعال'),
                        'informations',
                        'infoInformations',
                        'documents',
                    ])
            );

        } catch (QueryException $e) {
            DB::rollBack();

            if ($this->isDuplicateKey($e)) {
                return $this->duplicateResponsePashto();
            }

            return response()->json([
                'message' => 'Office employee update failed',
                'error'   => $e->getMessage(),
            ], 500);

        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Office employee update failed',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(Request $request, $id)
    {
        DB::beginTransaction();

        try {
            $officeEmployee = OfficeEmployee::with('documents')->findOrFail($id);
            $user           = $request->user();

            foreach ($officeEmployee->documents as $doc) {
                Storage::disk('public')->delete($doc->file_path);
            }

            $officeEmployee->documents()->delete();

            if ($officeEmployee->picture) {
                Storage::disk('public')->delete($officeEmployee->picture);
            }

            $officeEmployee->delete();

            NotificationService::recordForAllUsers(
                actor: $user,
                module: 'کارمند دفتر',
                action: 'deleted',
                title: 'کارمند دفتر حذف شد',
                message: 'کد کارمند: ' . ($officeEmployee->code_number ?? ''),
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

    public function inactive(Request $request)
    {
        $request->validate([
            'search'     => 'nullable|string',
            'perPage'    => 'sometimes',
            'year'       => 'nullable|integer|min:1900|max:2500',
            'month'      => 'nullable|integer|min:1|max:12',
            'date_field' => 'nullable|string',
        ]);

        $year      = $request->filled('year') ? (int) $request->input('year') : null;
        $month     = $request->filled('month') ? (int) $request->input('month') : null;
        $dateField = $this->resolveDateField($request->input('date_field'));

        $query = $this->baseListQuery()->inactive()->orderByDesc('id');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code_number', 'like', "%{$search}%")
                  ->orWhere('phone_number', 'like', "%{$search}%");
            });
        }

        $this->applyMonthYearFilter($query, $year, $month, $dateField);

        $perPage = $request->input('perPage', 0);

        if (!$perPage || strtolower((string) $perPage) === 'all') {
            return OfficeEmployeeResource::collection($query->get());
        }

        return OfficeEmployeeResource::collection($query->paginate((int) $perPage));
    }
}
