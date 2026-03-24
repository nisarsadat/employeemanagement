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
use App\Services\NotificationService;

class InformationController extends Controller
{
    // =========================
    // ✅ DATE FILTER HELPERS
    // =========================
    private function resolveDateField(?string $dateField): string
    {
        $allowed = ['information_saved_date', 'hamahangi_date', 'created_at'];

        $field = trim((string) $dateField);
        if ($field === '' || !in_array($field, $allowed, true)) {
            return 'information_saved_date';
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
        return Information::query()->with([
            'resource',
            'officeEmployee',
            'officeEmployeeInfo',
            'matchedInformation',
            'documents',
            'account:id,name,price',
            'finances',
        ]);
    }

    /**
     * ✅ backend related-record filter
     * Shows:
     * - selected record itself
     * - its parent (if selected row is child)
     * - its children
     */
    private function applyRelatedFilter($query, ?int $relatedId)
    {
        if (!$relatedId) {
            return $query;
        }

        $selected = Information::query()
            ->select(['id', 'matched_information_id'])
            ->find($relatedId);

        $ids = collect([$relatedId]);

        if ($selected && $selected->matched_information_id) {
            $ids->push((int) $selected->matched_information_id);
        }

        $ids = $ids->filter()->unique()->values();

        $query->where(function ($q) use ($ids) {
            $q->whereIn('id', $ids)
              ->orWhereIn('matched_information_id', $ids);
        });

        return $query;
    }

    /**
     * ✅ backend stat filter
     */
    private function applyStatFilter($query, ?string $stat)
    {
        $stat = trim((string) $stat);

        if ($stat === '') {
            return $query;
        }

        if ($stat === 'today') {
            $query->whereDate('information_saved_date', now()->toDateString());
            return $query;
        }

        if ($stat === 'saeen') {
            $query->where(function ($q) {
                $q->where('syasi_info_type', 'ساعین بالفساد')
                  ->orWhere('jenayee_info_type', 'ساعین بالفساد');
            });
            return $query;
        }

        if ($stat === 'shar') {
            $query->where(function ($q) {
                $q->where('syasi_info_type', 'شر او فساد')
                  ->orWhere('jenayee_info_type', 'شر او فساد');
            });
            return $query;
        }

        if ($stat === 'spy') {
            $query->where(function ($q) {
                $q->whereIn('syasi_info_type', ['جاسوسی', ' جاسوسي'])
                  ->orWhereIn('jenayee_info_type', ['جاسوسی', ' جاسوسي']);
            });
            return $query;
        }

        if ($stat === 'cat_alef') {
            $query->where('information_category', 'الف');
            return $query;
        }

        if ($stat === 'unpaid') {
            $query->where(function ($q) {
                $q->whereIn(DB::raw('LOWER(TRIM(finance_status))'), [
                    'unpaid',
                    'pending',
                    'ناپرداخت',
                    'not_paid',
                ]);
            });
            return $query;
        }

        if ($stat === 'resources') {
            return $query;
        }

        return $query;
    }

    /**
     * ✅ build backend stats based on current search/month/year/related filters
     * but without current card stat filter
     */
    private function buildStats(Request $request, string $dateField, string $search, ?int $year, ?int $month, string $date, string $dateFrom, string $dateTo, ?int $relatedId): array
    {
        $base = Information::query();

        if ($search !== '') {
            $base->where(function ($qq) use ($search) {
                $qq->where('name', 'like', "%{$search}%")
                   ->orWhere('father_name', 'like', "%{$search}%")
                   ->orWhere('nick_name', 'like', "%{$search}%");
            });
        }

        $this->applyMonthYearFilter($base, $year, $month, $dateField);
        $this->applyRelatedFilter($base, $relatedId);

        $base
            ->when($date !== '', function ($q) use ($date, $dateField) {
                $q->whereDate($dateField, $date);
            })
            ->when($dateFrom !== '' && $dateTo !== '', function ($q) use ($dateFrom, $dateTo, $dateField) {
                $q->whereDate($dateField, '>=', $dateFrom)
                  ->whereDate($dateField, '<=', $dateTo);
            })
            ->when($dateFrom !== '' && $dateTo === '' && $date === '', function ($q) use ($dateFrom, $dateField) {
                $q->whereDate($dateField, '>=', $dateFrom);
            })
            ->when($dateTo !== '' && $dateFrom === '' && $date === '', function ($q) use ($dateTo, $dateField) {
                $q->whereDate($dateField, '<=', $dateTo);
            });

        return [
            'total'    => (clone $base)->count(),

            'saeen'    => (clone $base)->where(function ($q) {
                $q->where('syasi_info_type', 'ساعین بالفساد')
                  ->orWhere('jenayee_info_type', 'ساعین بالفساد');
            })->count(),

            'shar'     => (clone $base)->where(function ($q) {
                $q->where('syasi_info_type', 'شر او فساد')
                  ->orWhere('jenayee_info_type', 'شر او فساد');
            })->count(),

            'spy'      => (clone $base)->where(function ($q) {
                $q->whereIn('syasi_info_type', ['جاسوسی', ' جاسوسي'])
                  ->orWhereIn('jenayee_info_type', ['جاسوسی', ' جاسوسي']);
            })->count(),

            'today'    => (clone $base)->whereDate('information_saved_date', now()->toDateString())->count(),

            'cat_alef' => (clone $base)->where('information_category', 'الف')->count(),

            'unpaid'   => (clone $base)->where(function ($q) {
                $q->whereIn(DB::raw('LOWER(TRIM(finance_status))'), [
                    'unpaid',
                    'pending',
                    'ناپرداخت',
                    'not_paid',
                ]);
            })->count(),
        ];
    }

    /**
     * ✅ Return available years/months based on REAL DB data.
     */
    public function dateOptions(Request $request)
    {
        $request->validate([
            'date_field' => 'nullable|string',
            'year'       => 'nullable|integer|min:1900|max:2500',
        ]);

        $dateField = $this->resolveDateField($request->input('date_field'));
        $year      = $request->filled('year') ? (int) $request->input('year') : null;

        $base = Information::query()->whereNotNull($dateField);

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
     * List all informations with search + month/year filters + backend card filter + related filter.
     */
    public function index(Request $request)
    {
        $request->validate([
            'page'       => 'sometimes|integer|min:1',
            'perPage'    => 'sometimes',
            'search'     => 'nullable|string',

            // ✅ month/year filters
            'year'       => 'nullable|integer|min:1900|max:2500',
            'month'      => 'nullable|integer|min:1|max:12',
            'date_field' => 'nullable|string',

            // ✅ optional: day or range filters
            'date'       => 'nullable|string',
            'date_from'  => 'nullable|string',
            'date_to'    => 'nullable|string',
            'from_date'  => 'nullable|string',
            'to_date'    => 'nullable|string',

            // ✅ NEW
            'stat'       => 'nullable|string',
            'related_id' => 'nullable|integer',
            'stats_only' => 'nullable',
        ]);

        $search     = trim((string) $request->input('search', ''));
        $rawPerPage = $request->input('perPage', null);
        $page       = (int) $request->input('page', 1);

        $year      = $request->filled('year') ? (int) $request->input('year') : null;
        $month     = $request->filled('month') ? (int) $request->input('month') : null;
        $dateField = $this->resolveDateField($request->input('date_field'));

        $date      = trim((string) $request->input('date', ''));
        $dateFrom  = trim((string) $request->input('date_from', $request->input('from_date', '')));
        $dateTo    = trim((string) $request->input('date_to', $request->input('to_date', '')));
        $stat      = trim((string) $request->input('stat', ''));
        $relatedId = $request->filled('related_id') ? (int) $request->input('related_id') : null;
        $statsOnly = $request->boolean('stats_only');

        // ✅ backend stats
        $stats = $this->buildStats(
            $request,
            $dateField,
            $search,
            $year,
            $month,
            $date,
            $dateFrom,
            $dateTo,
            $relatedId
        );

        if ($statsOnly) {
            return response()->json([
                'stats' => $stats,
            ]);
        }

        $query = $this->baseListQuery()
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($qq) use ($search) {
                    $qq->where('name', 'like', "%{$search}%")
                       ->orWhere('father_name', 'like', "%{$search}%")
                       ->orWhere('nick_name', 'like', "%{$search}%");
                });
            });

        // ✅ existing month/year filter
        $this->applyMonthYearFilter($query, $year, $month, $dateField);

        // ✅ existing day/range filter
        $query
            ->when($date !== '', function ($q) use ($date, $dateField) {
                $q->whereDate($dateField, $date);
            })
            ->when($dateFrom !== '' && $dateTo !== '', function ($q) use ($dateFrom, $dateTo, $dateField) {
                $q->whereDate($dateField, '>=', $dateFrom)
                  ->whereDate($dateField, '<=', $dateTo);
            })
            ->when($dateFrom !== '' && $dateTo === '' && $date === '', function ($q) use ($dateFrom, $dateField) {
                $q->whereDate($dateField, '>=', $dateFrom);
            })
            ->when($dateTo !== '' && $dateFrom === '' && $date === '', function ($q) use ($dateTo, $dateField) {
                $q->whereDate($dateField, '<=', $dateTo);
            });

        // ✅ NEW: related info filter
        $this->applyRelatedFilter($query, $relatedId);

        // ✅ NEW: stat/card filter
        $this->applyStatFilter($query, $stat);

        $query->orderByDesc('id');

        if (
            is_null($rawPerPage) ||
            $rawPerPage === '' ||
            strtolower((string) $rawPerPage) === 'all' ||
            (int) $rawPerPage === 0
        ) {
            return InformationResource::collection($query->get())->additional([
                'meta' => [
                    'stats' => $stats,
                ],
            ]);
        }

        $perPage  = max(1, (int) $rawPerPage);
        $total    = (clone $query)->count();
        $lastPage = max(1, (int) ceil($total / $perPage));
        $safePage = min(max(1, $page), $lastPage);

        return InformationResource::collection(
            $query->paginate($perPage, ['*'], 'page', $safePage)
        )->additional([
            'meta' => [
                'stats' => $stats,
            ],
        ]);
    }

    public function show($id)
    {
        $information = $this->baseListQuery()->findOrFail($id);
        return new InformationResource($information);
    }

    public function store(StoreInformationRequest $request)
    {
        $user = $request->user();

        DB::beginTransaction();

        try {
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

            $info->load([
                'resource',
                'officeEmployee',
                'officeEmployeeInfo',
                'matchedInformation',
                'documents',
                'finances',
            ]);

            NotificationService::record(
                $user,
                module: 'اطلاع',
                action: 'created',
                title: 'نوی اطلاع ثبت شوو',
                message: 'د متهم نوم: ' . ($info->name ?? ''),
                routeName: 'informations',
                routeParams: ['id' => $info->id],
                level: 'success',
                changes: null
            );

            DB::commit();

            return new InformationResource($info);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Information creation failed',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function update(UpdateInformationRequest $request, $id)
    {
        $user = $request->user();

        DB::beginTransaction();

        try {
            $information = Information::with([
                'resource',
                'officeEmployee',
                'officeEmployeeInfo',
                'matchedInformation',
                'documents',
                'finances',
            ])->findOrFail($id);

            $original  = $information->getOriginal();
            $validated = $request->validated();

            $information->fill($validated);
            $dirty   = $information->getDirty();
            $changes = [];

            foreach ($dirty as $field => $newValue) {
                $changes[$field] = [
                    'old' => $original[$field] ?? null,
                    'new' => $newValue,
                ];
            }

            $information->save();

            if (
                array_key_exists('result', $validated) &&
                !is_null($validated['result']) &&
                trim((string) $validated['result']) !== ''
            ) {
                $information->forceFill([
                    'result_missing_first_notified_at' => null,
                    'result_missing_last_notified_at'  => null,
                    'result_missing_notify_count'      => 0,
                ])->save();
            }

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

                $changes['documents'] = [
                    'old' => null,
                    'new' => 'updated',
                ];
            }

            $information->load([
                'resource',
                'officeEmployee',
                'officeEmployeeInfo',
                'matchedInformation',
                'documents',
                'finances',
            ]);

            NotificationService::record(
                $user,
                module: 'اطلاع',
                action: 'updated',
                title: 'اطلاع اصلاح شوو',
                message: 'د متهم نوم: ' . ($information->name ?? ''),
                routeName: 'informations',
                routeParams: ['id' => $information->id],
                level: 'info',
                changes: $changes ?: null
            );

            DB::commit();

            return new InformationResource($information);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Information update failed',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(Request $request, $id)
    {
        $user = $request->user();

        DB::beginTransaction();

        try {
            $information = Information::with('documents')->findOrFail($id);
            $name = $information->name;

            foreach ($information->documents as $doc) {
                Storage::disk('public')->delete($doc->file_path);
            }
            $information->documents()->delete();

            $information->delete();

            NotificationService::record(
                $user,
                module: 'اطلاع',
                action: 'deleted',
                title: 'اطلاع حذف شوو',
                message: 'د متهم نوم: ' . ($name ?? ''),
                routeName: null,
                routeParams: ['informations'],
                level: 'warning',
                changes: null
            );

            DB::commit();

            return response()->json(['message' => 'Deleted']);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Information delete failed',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function checkDuplicate(Request $request)
    {
        $data = $request->validate([
            'id'             => 'nullable|integer',
            'name'           => 'nullable|string',
            'father_name'    => 'nullable|string',
            'main_province'  => 'nullable|string',
            'main_districts' => 'nullable|string',
            'main_village'   => 'nullable|string',
        ]);

        $name = trim((string) ($data['name'] ?? ''));
        $fatherName = trim((string) ($data['father_name'] ?? ''));
        $mainProvince = trim((string) ($data['main_province'] ?? ''));
        $mainDistricts = trim((string) ($data['main_districts'] ?? ''));
        $mainVillage = trim((string) ($data['main_village'] ?? ''));
        $currentId = $data['id'] ?? null;

        if (
            $name === '' ||
            $fatherName === '' ||
            $mainProvince === '' ||
            $mainDistricts === '' ||
            $mainVillage === ''
        ) {
            return response()->json([
                'matches' => [],
            ]);
        }

        $query = Information::query()
            ->select([
                'id',
                'name',
                'father_name',
                'main_province',
                'main_districts',
                'main_village',
                'matched_information_id',
            ])
            ->where('name', $name)
            ->where('father_name', $fatherName)
            ->where('main_province', $mainProvince)
            ->where('main_districts', $mainDistricts)
            ->where('main_village', $mainVillage)
            ->orderBy('id', 'asc');

        if ($currentId) {
            $query->where('id', '!=', $currentId);
        }

        return response()->json([
            'matches' => $query->get(),
        ]);
    }
}