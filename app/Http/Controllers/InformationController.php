<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AppliesRelatedFilters;
use App\Models\Information;
use App\Models\InformationDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\StoreInformationRequest;
use App\Http\Requests\UpdateInformationRequest;
use App\Http\Resources\InformationResource;
use App\Services\NotificationService;
use Carbon\Carbon;
use App\Exports\InformationsExport;
use Maatwebsite\Excel\Facades\Excel;

class InformationController extends Controller
{
    use AppliesRelatedFilters;

    protected function relatedFilterRules(): array
    {
        return [
            'information_id' => [
                'callback' => function ($query, $value) {
                    $this->applyRelatedFilter($query, (int) $value);
                },
            ],

            'resource_id' => [
                'column' => 'resource_id',
            ],

            'office_employee_id' => [
                'callback' => function ($query, $value) {
                    $query->where(function ($q) use ($value) {
                        $q->where('office_employee_id', $value)
                          ->orWhere('office_employee_info_id', $value);
                    });
                },
            ],

            'event_id' => [
                'callback' => function ($query, $value) {
                    $query->whereHas('events', fn ($q) => $q->where('id', $value));
                },
            ],

            'fifty_six_format_id' => [
                'callback' => function ($query, $value) {
                    $query->whereHas('fiftySixFormats', fn ($q) => $q->where('id', $value));
                },
            ],
        ];
    }

    private function resolveDateField(?string $dateField): string
    {
        $allowed = ['information_saved_date', 'hamahangi_date', 'created_at'];

        $field = trim((string) $dateField);
        if ($field === '' || !in_array($field, $allowed, true)) {
            return 'information_saved_date';
        }

        return $field;
    }

    public function exportExcel(Request $request)
    {
        $fileName = 'informations_' . now()->format('Y_m_d_H_i_s') . '.xlsx';

        return Excel::download(
            new InformationsExport(
                $request->input('search'),
                $request->input('search_field'),
                $request->filled('year') ? (int) $request->input('year') : null,
                $request->filled('month') ? (int) $request->input('month') : null,
                $request->input('stat', 'all'),
                $request->input('date_field', 'information_saved_date'),
                $request->filled('information_id') ? (int) $request->input('information_id') : null
            ),
            $fileName
        );
    }

    private function gregorianToJalali(int $gy, int $gm, int $gd): array
    {
        $g_d_m = [0, 31, 59, 90, 120, 151, 181, 212, 243, 273, 304, 334];

        if ($gy > 1600) {
            $jy = 979;
            $gy -= 1600;
        } else {
            $jy = 0;
            $gy -= 621;
        }

        $gy2 = ($gm > 2) ? ($gy + 1) : $gy;
        $days = (365 * $gy)
            + intdiv(($gy2 + 3), 4)
            - intdiv(($gy2 + 99), 100)
            + intdiv(($gy2 + 399), 400)
            - 80
            + $gd
            + $g_d_m[$gm - 1];

        $jy += 33 * intdiv($days, 12053);
        $days %= 12053;

        $jy += 4 * intdiv($days, 1461);
        $days %= 1461;

        if ($days > 365) {
            $jy += intdiv(($days - 1), 365);
            $days = ($days - 1) % 365;
        }

        if ($days < 186) {
            $jm = 1 + intdiv($days, 31);
            $jd = 1 + ($days % 31);
        } else {
            $jm = 7 + intdiv(($days - 186), 30);
            $jd = 1 + (($days - 186) % 30);
        }

        return [$jy, $jm, $jd];
    }

    private function jalaliToGregorian(int $jy, int $jm, int $jd): array
    {
        if ($jy > 979) {
            $gy = 1600;
            $jy -= 979;
        } else {
            $gy = 621;
        }

        $days = (365 * $jy)
            + intdiv($jy, 33) * 8
            + intdiv((($jy % 33) + 3), 4)
            + 78
            + $jd
            + (($jm < 7) ? (($jm - 1) * 31) : ((($jm - 7) * 30) + 186));

        $gy += 400 * intdiv($days, 146097);
        $days %= 146097;

        if ($days > 36524) {
            $gy += 100 * intdiv(--$days, 36524);
            $days %= 36524;

            if ($days >= 365) {
                $days++;
            }
        }

        $gy += 4 * intdiv($days, 1461);
        $days %= 1461;

        if ($days > 365) {
            $gy += intdiv(($days - 1), 365);
            $days = ($days - 1) % 365;
        }

        $gd = $days + 1;

        $sal_a = [
            0,
            31,
            (($gy % 4 === 0 && $gy % 100 !== 0) || ($gy % 400 === 0)) ? 29 : 28,
            31,
            30,
            31,
            30,
            31,
            31,
            30,
            31,
            30,
            31,
        ];

        for ($gm = 1; $gm <= 12; $gm++) {
            if ($gd <= $sal_a[$gm]) {
                break;
            }
            $gd -= $sal_a[$gm];
        }

        return [$gy, $gm, $gd];
    }

    private function shamsiMonthName(int $month): string
    {
        $months = [
            1 => 'حمل',
            2 => 'ثور',
            3 => 'جوزا',
            4 => 'سرطان',
            5 => 'اسد',
            6 => 'سنبله',
            7 => 'میزان',
            8 => 'عقرب',
            9 => 'قوس',
            10 => 'جدی',
            11 => 'دلو',
            12 => 'حوت',
        ];

        return $months[$month] ?? (string) $month;
    }

    private function searchableColumns(): array
    {
        return [
            'id',
            'resource_id',
            'office_employee_id',
            'office_employee_info_id',
            'finance_paid_amount',
            'finance_status',
            'name',
            'nick_name',
            'father_name',
            'grand_father_name',
            'madani_halat',
            'age',
            'job',
            'main_province',
            'main_districts',
            'main_village',
            'now_province',
            'now_district',
            'now_village',
            'specefic_place',
            'street_number',
            'home_number',
            'coordinates',
            'syasi_info_type',
            'jenayee_info_type',
            'letry_format_name',
            'information_saved_date',
            'information_saved_hour',
            'information_degree',
            'information_category',
            'resource_lasrasy',
            'tafseel',
            'docs_note',
            'tahlil',
            'manager_comment',
            'lead_drirection',
            'hamahangi_date',
            'maktop_number',
            'organ',
            'result',
            'note',
        ];
    }

    private function resolveSearchField(?string $field): ?string
    {
        $field = trim((string) $field);

        return in_array($field, $this->searchableColumns(), true) ? $field : null;
    }

    private function applySearchFilter($query, string $search, ?string $searchField = null)
    {
        if ($search === '') {
            return $query;
        }

        $resolvedField = $this->resolveSearchField($searchField);

        if ($resolvedField) {
            $query->where($resolvedField, 'like', "%{$search}%");
            return $query;
        }

        $query->where(function ($qq) use ($search) {
            foreach ($this->searchableColumns() as $index => $column) {
                if ($index === 0) {
                    $qq->where($column, 'like', "%{$search}%");
                } else {
                    $qq->orWhere($column, 'like', "%{$search}%");
                }
            }
        });

        return $query;
    }

    private function applyMonthYearFilter($query, ?int $year, ?int $month, string $dateField)
    {
        if ($year && $month) {
            [$startGy, $startGm, $startGd] = $this->jalaliToGregorian($year, $month, 1);
            $lastDay = $month <= 6 ? 31 : ($month <= 11 ? 30 : 29);
            [$endGy, $endGm, $endGd] = $this->jalaliToGregorian($year, $month, $lastDay);

            $startDate = Carbon::create($startGy, $startGm, $startGd)->startOfDay();
            $endDate   = Carbon::create($endGy, $endGm, $endGd)->endOfDay();

            $query->whereBetween($dateField, [$startDate, $endDate]);

            return $query;
        }

        if ($year) {
            [$startGy, $startGm, $startGd] = $this->jalaliToGregorian($year, 1, 1);
            [$endGy, $endGm, $endGd] = $this->jalaliToGregorian($year, 12, 29);

            $startDate = Carbon::create($startGy, $startGm, $startGd)->startOfDay();
            $endDate   = Carbon::create($endGy, $endGm, $endGd)->endOfDay();

            $query->whereBetween($dateField, [$startDate, $endDate]);

            return $query;
        }

        if ($month) {
            $query->whereMonth($dateField, $month);
        }

        return $query;
    }

    private function baseListQuery()
    {
        $table = (new Information())->getTable();
        $resultPendingDaysSql = $this->resultPendingDaysSql($table);

        return Information::query()
            ->select("{$table}.*")
            ->selectRaw("
                CASE
                    WHEN CHAR_LENGTH(TRIM(COALESCE({$table}.result, ''))) > 0 THEN NULL
                    ELSE {$resultPendingDaysSql}
                END AS result_pending_days
            ")
            ->selectRaw("
                CASE
                    WHEN CHAR_LENGTH(TRIM(COALESCE({$table}.result, ''))) > 0 THEN 'done'
                    WHEN {$resultPendingDaysSql} >= 30 THEN 'month'
                    WHEN {$resultPendingDaysSql} >= 14 THEN 'two_weeks'
                    WHEN {$resultPendingDaysSql} >= 7 THEN 'week'
                    ELSE 'pending'
                END AS result_pending_bucket
            ")
            ->selectRaw("
                CASE
                    WHEN CHAR_LENGTH(TRIM(COALESCE({$table}.result, ''))) > 0 THEN 'Done'
                    WHEN {$resultPendingDaysSql} >= 30 THEN '1 month or more'
                    WHEN {$resultPendingDaysSql} >= 14 THEN '2 weeks'
                    WHEN {$resultPendingDaysSql} >= 7 THEN '1 week'
                    ELSE 'Pending'
                END AS result_pending_message
            ")
            ->with([
                'resource',
                'officeEmployee',
                'officeEmployeeInfo',
                'matchedInformation',
                'documents',
                'account:id,name,price',
                'finances',
            ]);
    }

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
                $q->whereIn('syasi_info_type', ['جاسوسی', ' جاسوسي' , 'جواسیس'])
                  ->orWhereIn('jenayee_info_type', ['جاسوسی', ' جاسوسي' , 'جواسیس']);
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

    private function buildStats(
        Request $request,
        string $dateField,
        string $search,
        ?string $searchField,
        ?int $year,
        ?int $month,
        string $date,
        string $dateFrom,
        string $dateTo,
        ?string $type
    ): array {
        $base = Information::query();

        $this->applySearchFilter($base, $search, $searchField);
        $this->applyMonthYearFilter($base, $year, $month, $dateField);
        $this->applyRelatedFilters($base, $request);
        $this->applyTypeFilter($base, $type);

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
                $q->whereIn('syasi_info_type', ['جاسوسی', ' جاسوسي', 'جواسیس'])
                  ->orWhereIn('jenayee_info_type', ['جاسوسی', ' جاسوسي', 'جواسیس']);
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

    public function dateOptions(Request $request)
    {
        $request->validate([
            'date_field' => 'nullable|string',
            'year'       => 'nullable|integer|min:1|max:2500',
        ]);

        $dateField  = $this->resolveDateField($request->input('date_field'));
        $shamsiYear = $request->filled('year') ? (int) $request->input('year') : null;

        $dates = Information::query()
            ->whereNotNull($dateField)
            ->pluck($dateField);

        $yearsMap = [];
        $monthsMap = [];

        foreach ($dates as $date) {
            if (!$date) {
                continue;
            }

            $carbon = Carbon::parse($date);

            [$jy, $jm, $jd] = $this->gregorianToJalali(
                (int) $carbon->format('Y'),
                (int) $carbon->format('m'),
                (int) $carbon->format('d')
            );

            $yearsMap[$jy] = $jy;

            if ($shamsiYear && $jy === $shamsiYear) {
                $monthsMap[$jm] = [
                    'value' => $jm,
                    'label' => $this->shamsiMonthName($jm),
                ];
            }
        }

        $years = collect($yearsMap)
            ->sortDesc()
            ->map(fn ($year) => [
                'value' => $year,
                'label' => (string) $year,
            ])
            ->values()
            ->all();

        $months = collect($monthsMap)
            ->sortBy('value')
            ->values()
            ->all();

        return response()->json([
            'date_field' => $dateField,
            'years'      => $years,
            'months'     => $months,
        ]);
    }

    private function applyTypeFilter($query, ?string $type)
    {
        $type = trim((string) $type);

        if ($type === '') {
            return $query;
        }

        $query->where(function ($q) use ($type) {
            $q->where('syasi_info_type', $type)
            ->orWhere('jenayee_info_type', $type);
        });

        return $query;
    }
    public function index(Request $request)
    {
        $request->validate([
            'page'                 => 'sometimes|integer|min:1',
            'perPage'              => 'sometimes',
            'search'               => 'nullable|string',
            'search_field'         => 'nullable|string',
            'year'                 => 'nullable|integer|min:1|max:2500',
            'month'                => 'nullable|integer|min:1|max:12',
            'date_field'           => 'nullable|string',
            'date'                 => 'nullable|string',
            'date_from'            => 'nullable|string',
            'date_to'              => 'nullable|string',
            'from_date'            => 'nullable|string',
            'to_date'              => 'nullable|string',
            'stat'                 => 'nullable|string',
            'stats_only'           => 'nullable',
            'information_id'       => 'nullable|integer',
            'resource_id'          => 'nullable|integer',
            'office_employee_id'   => 'nullable|integer',
            'event_id'             => 'nullable|integer',
            'fifty_six_format_id'  => 'nullable|integer',
            'type'                 => 'nullable|string',
            ]);

        $search      = trim((string) $request->input('search', ''));
        $searchField = $this->resolveSearchField($request->input('search_field'));
        $rawPerPage  = $request->input('perPage', null);
        $page        = (int) $request->input('page', 1);

        $year      = $request->filled('year') ? (int) $request->input('year') : null;
        $month     = $request->filled('month') ? (int) $request->input('month') : null;
        $dateField = $this->resolveDateField($request->input('date_field'));

        $date      = trim((string) $request->input('date', ''));
        $dateFrom  = trim((string) $request->input('date_from', $request->input('from_date', '')));
        $dateTo    = trim((string) $request->input('date_to', $request->input('to_date', '')));
        $stat      = trim((string) $request->input('stat', ''));
        $statsOnly = $request->boolean('stats_only');
        $type = trim((string) $request->input('type', ''));

        $stats = $this->buildStats(
            $request,
            $dateField,
            $search,
            $searchField,
            $year,
            $month,
            $date,
            $dateFrom,
            $dateTo,
            $type
        );

        if ($statsOnly) {
            return response()->json([
                'stats' => $stats,
            ]);
        }

        $query = $this->baseListQuery();

        $this->applySearchFilter($query, $search, $searchField);
        $this->applyMonthYearFilter($query, $year, $month, $dateField);
        $this->applyRelatedFilters($query, $request);

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

        $this->applyTypeFilter($query, $type);
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

    private function resultPendingDaysSql(string $table): string
    {
        return "DATEDIFF(CURDATE(), DATE(COALESCE({$table}.information_saved_date, {$table}.created_at)))";
    }
}