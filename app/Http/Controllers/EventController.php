<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AppliesRelatedFilters;
use App\Models\Event;
use Illuminate\Http\Request;
use App\Http\Requests\StoreEventRequest;
use App\Http\Requests\UpdateEventRequest;
use App\Http\Resources\EventResource;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use App\Services\NotificationService;
use Carbon\Carbon;
use App\Exports\EventsExport;
use Maatwebsite\Excel\Facades\Excel;

class EventController extends Controller
{
    use AppliesRelatedFilters;

    protected function relatedFilterRules(): array
    {
        return [
            'event_id' => [
                'column' => 'id',
            ],

            'resource_id' => [
                'column' => 'resource_id',
            ],

            'office_employee_id' => [
                'callback' => function ($query, $value) {
                    $query->where(function ($q) use ($value) {
                        $q->where('office_employee_id', $value)
                          ->orWhere('office_employee_akhistonky_id', $value);
                    });
                },
            ],

            'information_id' => [
                'callback' => function ($query, $value) {
                    $query->whereHas('informations', fn ($q) => $q->where('id', $value));
                },
            ],

            'fifty_six_format_id' => [
                'callback' => function ($query, $value) {
                    $query->whereHas('fiftySixFormats', fn ($q) => $q->where('id', $value));
                },
            ],
        ];
    }

    private function isDuplicateKey(QueryException $e): bool
    {
        return (int) ($e->errorInfo[1] ?? 0) === 1062;
    }

    public function exportExcel(Request $request)
    {
        $fileName = 'events_' . now()->format('Y_m_d_H_i_s') . '.xlsx';

        return Excel::download(
            new EventsExport(
                $request->input('search'),
                $request->input('search_field'),
                $request->filled('year') ? (int) $request->input('year') : null,
                $request->filled('month') ? (int) $request->input('month') : null,
                $request->input('stat', 'all'),
                $request->input('date_field', 'event_date')
            ),
            $fileName
        );
    }

    private function duplicateResponse()
    {
        return response()->json([
            'message' => 'این رویداد قبلاً ثبت شده است.',
            'errors'  => [
                'duplicate' => [
                    'رویداد با همین مشخصات (نوع رویداد، نوع مشخص، شخص هدف، مجرم و موقعیت) قبلاً در سیستم موجود است.',
                ],
            ],
        ], 422);
    }

    private function resolveDateField(?string $dateField): string
    {
        $allowed = ['event_date', 'created_at'];

        $field = trim((string) $dateField);
        if ($field === '' || !in_array($field, $allowed, true)) {
            return 'event_date';
        }

        return $field;
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
            'office_employee_akhistonky_id',
            'talks',
            'finance_paid_amount',
            'finance_status',
            'event_type',
            'event_specific_type',
            'target_person',
            'criminal',
            'event_date',
            'event_hour',
            'event_zone',
            'event_hawza',
            'event_specific_place',
            'coordinates',
            'talafat_mali',
            'injured',
            'killed',
            'tafseel',
            'money_amount_words',
            'note',
            'docs_note',
        ];
    }

    private function resolveSearchField(?string $field): ?string
    {
        $field = trim((string) $field);

        return in_array($field, $this->searchableColumns(), true) ? $field : null;
    }

    private function applySearchFilter($query, ?string $search, ?string $searchField = null)
    {
        $s = trim((string) $search);
        if ($s === '') {
            return $query;
        }

        $resolvedField = $this->resolveSearchField($searchField);

        if ($resolvedField) {
            $query->where($resolvedField, 'like', "%{$s}%");
            return $query;
        }

        $query->where(function ($qq) use ($s) {
            foreach ($this->searchableColumns() as $index => $column) {
                if ($index === 0) {
                    $qq->where($column, 'like', "%{$s}%");
                } else {
                    $qq->orWhere($column, 'like', "%{$s}%");
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
        return Event::query()->with([
            'resource:id,code_number',
            'officeEmployee:id,code_number',
            'akhistonkyEmployee:id,code_number',
            'documents',
            'account:id,name,price',
            'finances',
        ]);
    }

    private function applyCommonFilters($query, ?string $search, ?string $cardFilter, ?string $searchField = null)
    {
        $this->applySearchFilter($query, $search, $searchField);

        $query->when($cardFilter && $cardFilter !== 'all', function ($q) use ($cardFilter) {
            if ($cardFilter === 'syasi') {
                $q->where(function ($qq) {
                    $qq->where('event_type', 'like', '%سیاسی%')
                      ->orWhere('event_type', 'like', '%سیاسي%')
                      ->orWhere('event_type', 'like', '%syasi%');
                });
            } elseif ($cardFilter === 'jenayee') {
                $q->where(function ($qq) {
                    $qq->where('event_type', 'like', '%جینایی%')
                      ->orWhere('event_type', 'like', '%جنایی%')
                      ->orWhere('event_type', 'like', '%جنایي%')
                      ->orWhere('event_type', 'like', '%jinayee%')
                      ->orWhere('event_type', 'like', '%jenayee%');
                });
            } elseif ($cardFilter === 'today') {
                $q->whereDate('created_at', now()->toDateString());
            } elseif ($cardFilter === 'unpaid') {
                $q->where(function ($qq) {
                    $qq->where('finance_status', 'unpaid')
                       ->orWhere('finance_status', 'pending')
                       ->orWhere('finance_status', 'ناپرداخت')
                       ->orWhere('finance_status', 'not_paid');
                });
            }
        });

        return $query;
    }
    private function buildCardCounts(
    Request $request,
    ?string $search,
    ?string $searchField,
    ?int $year,
    ?int $month,
    string $dateField,
    ?string $type
    ): array
    {
        $base = Event::query();

        $this->applyCommonFilters($base, $search, null, $searchField);
        $this->applyMonthYearFilter($base, $year, $month, $dateField);
        $this->applyRelatedFilters($base, $request);
        $this->applyDateRangeFilter($base, $request, $dateField);
        $this->applyTypeFilter($base, $type);
        if (!empty($exactDate)) {
    $base->whereDate($dateField, $exactDate);
        }

        $this->applyTypeFilter($base, $type);

        return [
            'total'    => (clone $base)->count(),

            'syasi'    => (clone $base)->where(function ($q) {
                $q->where('event_type', 'like', '%سیاسی%')
                  ->orWhere('event_type', 'like', '%سیاسي%')
                  ->orWhere('event_type', 'like', '%syasi%');
            })->count(),

            'jenayee'  => (clone $base)->where(function ($q) {
                $q->where('event_type', 'like', '%جینایی%')
                  ->orWhere('event_type', 'like', '%جنایی%')
                  ->orWhere('event_type', 'like', '%جنایي%')
                  ->orWhere('event_type', 'like', '%jinayee%')
                  ->orWhere('event_type', 'like', '%jenayee%');
            })->count(),

            'today'    => (clone $base)->whereDate('created_at', now()->toDateString())->count(),

            'unpaid'   => (clone $base)->where(function ($q) {
                $q->where('finance_status', 'unpaid')
                  ->orWhere('finance_status', 'pending')
                  ->orWhere('finance_status', 'ناپرداخت')
                  ->orWhere('finance_status', 'not_paid');
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

        $dates = Event::query()
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

        $query->where('event_specific_type', $type);

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
            'card_filter'          => 'nullable|string',
            'event_id'             => 'nullable|integer',
            'resource_id'          => 'nullable|integer',
            'office_employee_id'   => 'nullable|integer',
            'information_id'       => 'nullable|integer',
            'fifty_six_format_id'  => 'nullable|integer',
            'date'                 => 'nullable|string',
            'date_from'            => 'nullable|string',
            'date_to'              => 'nullable|string',
            'from_date'            => 'nullable|string',
            'to_date'              => 'nullable|string',
            'type'                 => 'nullable|string',
        ]);

        $search      = $request->input('search');
        $searchField = $this->resolveSearchField($request->input('search_field'));
        $rawPerPage  = $request->input('perPage', null);
        $page        = (int) $request->input('page', 1);
        $cardFilter  = $request->input('card_filter', 'all');

        $year      = $request->filled('year') ? (int) $request->input('year') : null;
        $month     = $request->filled('month') ? (int) $request->input('month') : null;
        $dateField = $this->resolveDateField($request->input('date_field'));
        $type = trim((string) $request->input('type', ''));
        $exactDate = trim((string) $request->input('date', ''));
        $type      = trim((string) $request->input('type', ''));

        $cardCounts = $this->buildCardCounts(
        $request,
        $search,
        $searchField,
        $year,
        $month,
        $dateField,
        $type
    );
        $query = $this->baseListQuery();

        $this->applyCommonFilters($query, $search, $cardFilter, $searchField);
        $this->applyMonthYearFilter($query, $year, $month, $dateField);
        $this->applyRelatedFilters($query, $request);
        $this->applyDateRangeFilter($query, $request, $dateField);
        $this->applyTypeFilter($query, $type);

        $query->orderByDesc('id');

        if (
            is_null($rawPerPage) ||
            $rawPerPage === '' ||
            strtolower((string) $rawPerPage) === 'all' ||
            (int) $rawPerPage === 0
        ) {
            return EventResource::collection($query->get())
                ->additional([
                    'card_counts' => $cardCounts,
                ]);
        }

        return EventResource::collection(
            $query->paginate(max(1, (int) $rawPerPage), ['*'], 'page', $page)
        )->additional([
            'card_counts' => $cardCounts,
        ]);
    }

    public function store(StoreEventRequest $request)
    {
        $user = $request->user();

        try {
            return DB::transaction(function () use ($request, $user) {
                $event = Event::create($request->validated());

                if ($request->hasFile('documents')) {
                    foreach ($request->file('documents') as $file) {
                        $path = $file->store("events/{$event->id}", 'public');

                        $event->documents()->create([
                            'file_name'  => $file->getClientOriginalName(),
                            'file_path'  => $path,
                            'mime_type'  => $file->getClientMimeType(),
                            'size_bytes' => $file->getSize(),
                        ]);
                    }
                }

                $event->load([
                    'resource',
                    'officeEmployee',
                    'akhistonkyEmployee',
                    'documents',
                    'account',
                    'finances',
                ]);

                if ($user) {
                    NotificationService::record(
                        $user,
                        module: 'event',
                        action: 'created',
                        title: 'نوی پیښه ثبت شوو',
                        message: 'د پیښې نوعیت: ' . ($event->event_type ?? ''),
                        routeName: 'events',
                        routeParams: ['id' => $event->id],
                        level: 'success',
                        changes: null
                    );
                }

                return new EventResource($event);
            });
        } catch (QueryException $e) {
            if ($this->isDuplicateKey($e)) {
                return $this->duplicateResponse();
            }
            throw $e;
        }
    }

    public function show($id)
    {
        $event = $this->baseListQuery()->findOrFail($id);
        return new EventResource($event);
    }

    public function update(UpdateEventRequest $request, $id)
    {
        $user = $request->user();

        try {
            return DB::transaction(function () use ($request, $id, $user) {
                $event = Event::with('documents')->findOrFail($id);

                $original  = $event->getOriginal();
                $validated = $request->validated();
                $changes   = [];

                foreach ($validated as $field => $newValue) {
                    if (array_key_exists($field, $original)) {
                        $oldValue = $original[$field];
                        if ($oldValue != $newValue) {
                            $changes[$field] = [
                                'old' => $oldValue,
                                'new' => $newValue,
                            ];
                        }
                    }
                }

                $event->update($validated);

                if ($request->hasFile('documents')) {
                    foreach ($request->file('documents') as $file) {
                        $path = $file->store("events/{$event->id}", 'public');

                        $event->documents()->create([
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

                $event->load([
                    'resource',
                    'officeEmployee',
                    'akhistonkyEmployee',
                    'documents',
                    'account',
                    'finances',
                ]);

                if ($user) {
                    NotificationService::record(
                        $user,
                        module: 'event',
                        action: 'updated',
                        title: 'پیښه اصلاح شوو',
                        message: 'د پیښې نوعیت: ' . ($event->event_type ?? ''),
                        routeName: 'events',
                        routeParams: ['id' => $event->id],
                        level: 'info',
                        changes: $changes ?: null
                    );
                }

                return new EventResource($event);
            });
        } catch (QueryException $e) {
            if ($this->isDuplicateKey($e)) {
                return $this->duplicateResponse();
            }
            throw $e;
        }
    }

    public function destroy(Request $request, $id)
    {
        DB::beginTransaction();

        try {
            $event = Event::with('documents')->findOrFail($id);
            $user  = $request->user();

            foreach ($event->documents as $doc) {
                Storage::disk('public')->delete($doc->file_path);
            }
            $event->documents()->delete();

            $event->delete();

            if ($user) {
                NotificationService::record(
                    $user,
                    module: 'event',
                    action: 'deleted',
                    title: 'پیښه حذف شوو',
                    message: 'د پیښې نوعیت: ' . ($event->event_type ?? ''),
                    routeName: null,
                    routeParams: ['events'],
                    level: 'warning',
                    changes: null
                );
            }

            DB::commit();

            return response()->json(['message' => 'Event deleted successfully']);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Event delete failed',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}