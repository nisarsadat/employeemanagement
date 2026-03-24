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

    private function applyCommonFilters($query, ?string $search, ?string $status, ?string $jobType)
    {
        $query
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
            })
            ->when($jobType, function ($q) use ($jobType) {
                if ($jobType === 'اداری') {
                    $q->whereIn('job_type', ['اداری', 'اداري']);
                } elseif ($jobType === 'اوپراتیفی') {
                    $q->whereIn('job_type', ['اوپراتیفی', 'اوپراتیفي']);
                } else {
                    $q->where('job_type', $jobType);
                }
            });

        return $query;
    }

    private function buildCardCounts(?string $search, ?string $status, ?string $jobType, ?int $year, ?int $month, string $dateField): array
    {
        $base = OfficeEmployee::query();

        $this->applyCommonFilters($base, $search, $status, $jobType);
        $this->applyMonthYearFilter($base, $year, $month, $dateField);

        return [
            'total'      => (clone $base)->count(),
            'edari'      => (clone $base)->whereIn('job_type', ['اداری', 'اداري'])->count(),
            'operatifi'  => (clone $base)->whereIn('job_type', ['اوپراتیفی', 'اوپراتیفي'])->count(),
            'active'     => (clone $base)->where('activity', 'فعال')->count(),
            'inactive'   => (clone $base)->where('activity', 'غیر فعال')->count(),
        ];
    }

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

    public function index(Request $request)
    {
        $request->validate([
            'page'       => 'sometimes|integer|min:1',
            'perPage'    => 'sometimes',
            'search'     => 'nullable|string',
            'status'     => 'nullable|string',
            'job_type'   => 'nullable|string',
            'year'       => 'nullable|integer|min:1900|max:2500',
            'month'      => 'nullable|integer|min:1|max:12',
            'date_field' => 'nullable|string',
        ]);

        $search     = $request->input('search');
        $rawPerPage = $request->input('perPage', null);
        $page       = (int) $request->input('page', 1);
        $status     = $request->input('status', 'all');
        $jobType    = $request->input('job_type');

        $year      = $request->filled('year') ? (int) $request->input('year') : null;
        $month     = $request->filled('month') ? (int) $request->input('month') : null;
        $dateField = $this->resolveDateField($request->input('date_field'));

        $cardCounts = $this->buildCardCounts($search, $status, $jobType, $year, $month, $dateField);

        $query = $this->baseListQuery();

        $this->applyCommonFilters($query, $search, $status, $jobType);
        $this->applyMonthYearFilter($query, $year, $month, $dateField);

        $query->orderByDesc('id');

        if (is_null($rawPerPage) || $rawPerPage === '' || strtolower((string) $rawPerPage) === 'all' || (int) $rawPerPage === 0) {
            return OfficeEmployeeResource::collection($query->get())
                ->additional([
                    'card_counts' => $cardCounts,
                ]);
        }

        return OfficeEmployeeResource::collection(
            $query->paginate(max(1, (int) $rawPerPage), ['*'], 'page', $page)
        )->additional([
            'card_counts' => $cardCounts,
        ]);
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

            if (empty($employee->code_number)) {
                $employee->update(['code_number' => (string) $employee->id]);
            }

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
                module: 'کارمند',
                action: 'created',
                title: 'کارمند ثبت',
                message: 'کارمند: ' . ($employee->code_number ?? ''),
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

            $data = $request->validated();
            unset($data['code_number']);

            $officeEmployee->fill($data);

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
                module: 'کارمند',
                action: 'updated',
                title: 'کارمند اصلاح',
                message: 'کارمند: ' . ($officeEmployee->code_number ?? ''),
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
                module: 'کارمند ',
                action: 'deleted',
                title: 'کارمند حذف',
                message: ' کارمند: ' . ($officeEmployee->code_number ?? ''),
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