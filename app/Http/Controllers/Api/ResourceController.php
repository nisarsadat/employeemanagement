<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\AppliesRelatedFilters;
use App\Http\Requests\ResourceRequest;
use App\Http\Resources\ResourceResource;
use App\Models\Resource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Services\NotificationService;
use Illuminate\Database\QueryException;
use App\Exports\ResourcesExport;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;

class ResourceController extends Controller
{
    use AppliesRelatedFilters;

    protected function relatedFilterRules(): array
    {
        return [
            'resource_id' => [
                'column' => 'id',
            ],

            'office_employee_id' => [
                'column' => 'office_employee_id',
            ],

            'information_id' => [
                'callback' => function ($query, $value) {
                    $query->whereHas('informations', fn ($q) => $q->where('id', $value));
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

    private function isDuplicateKey(QueryException $e): bool
    {
        return (int) ($e->errorInfo[1] ?? 0) === 1062;
    }

    private function duplicateResponsePashto()
    {
        return response()->json([
            'message' => 'دا منبع مخکې ثبت شوی دی.',
            'errors'  => [
                'duplicate' => [
                    'په همدې نوم، د پلار نوم، د نيکه نوم، مستعار نوم، کوډ نمبر او د اصلي استوګنې ځای (ولايت/ولسوالي/کلی) سره منبع مخکې په سیستم کې موجوده ده.',
                ],
            ],
        ], 422);
    }

   private function searchableColumns(): array
{
    return [
        'letry_format_name',
        'resedint_code',
        'code_number',
        'name',
        'father_name',
        'grand_father_name',
        'nick_name',
        'tazkira',
        'main_province',
        'main_district',
        'main_village',
        'now_province',
        'now_district',
        'office_employee_id',
        'office_employee_code',
        'now_village',
        'now_specific_place',
        'now_street_number',
        'now_home_number',
        'old_job',
        'now_job',
        'job_place',
        'zone',
        'hawza',
        'specefic_place',
        'coordinates',
        'activity',
        'halat_madani',
        'age',
        'elmi_level',
        'faculty',
        'specific_skills',
        'resource_saved_date',
        'resource_activity_type',
        'resource_cover',
        'language',
        'nation',
        'blood_group',
        'train',
        'project',
        'phone_number',
        'trust_percentage',
        'note',
    ];
}

    private function resolveSearchField(?string $field): ?string
    {
        $field = trim((string) $field);

        return in_array($field, $this->searchableColumns(), true) ? $field : null;
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

    private function applyShamsiDateFilter($query, ?int $year, ?int $month, string $dateField)
    {
        if ($year && $month) {
            [$startGy, $startGm, $startGd] = $this->jalaliToGregorian($year, $month, 1);
            $lastDay = $month <= 6 ? 31 : ($month <= 11 ? 30 : 29);
            [$endGy, $endGm, $endGd] = $this->jalaliToGregorian($year, $month, $lastDay);

            $startDate = Carbon::create($startGy, $startGm, $startGd)->startOfDay();
            $endDate   = Carbon::create($endGy, $endGm, $endGd)->endOfDay();

            $query->whereNotNull($dateField)->whereBetween($dateField, [$startDate, $endDate]);

            return $query;
        }

        if ($year) {
            [$startGy, $startGm, $startGd] = $this->jalaliToGregorian($year, 1, 1);
            [$endGy, $endGm, $endGd] = $this->jalaliToGregorian($year, 12, 29);

            $startDate = Carbon::create($startGy, $startGm, $startGd)->startOfDay();
            $endDate   = Carbon::create($endGy, $endGm, $endGd)->endOfDay();

            $query->whereNotNull($dateField)->whereBetween($dateField, [$startDate, $endDate]);

            return $query;
        }

        if ($month && $month >= 1 && $month <= 12) {
            $query->whereMonth($dateField, $month);
        }

        return $query;
    }

    public function index(Request $req)
    {
        $req->validate([
            'search'               => 'nullable|string',
            'search_field'         => 'nullable|string',
            'year'                 => 'nullable|integer|min:1|max:2500',
            'month'                => 'nullable|integer|min:1|max:12',
            'stat'                 => 'nullable|string',
            'date_field'           => 'nullable|string',
            'perPage'              => 'nullable',
            'resource_id'          => 'nullable|integer',
            'search_value'         => 'nullable',
            'office_employee_id'   => 'nullable|integer',
            'information_id'       => 'nullable|integer',
            'event_id'             => 'nullable|integer',
            'fifty_six_format_id'  => 'nullable|integer',
            'date'                 => 'nullable|string',
            'date_from'            => 'nullable|string',
            'date_to'              => 'nullable|string',
            'from_date'            => 'nullable|string',
            'to_date'              => 'nullable|string',     
            ]);

        $search = trim((string) $req->get('search', ''));
        $searchField = $this->resolveSearchField($req->get('search_field'));

        $year  = $req->filled('year') ? (int) $req->get('year') : null;
        $month = $req->filled('month') ? (int) $req->get('month') : null;
        $searchValue = $req->get('search_value');
        $stat = trim((string) $req->get('stat', 'all'));
        $allowedStats = ['all', 'today', 'active', 'inactive'];
        if (!in_array($stat, $allowedStats, true)) {
            $stat = 'all';
        }

        $dateField = $req->get('date_field', 'resource_saved_date');
        $allowedDateFields = ['resource_saved_date', 'created_at', 'updated_at'];
        if (!in_array($dateField, $allowedDateFields, true)) {
            $dateField = 'resource_saved_date';
        }

        $baseQuery = Resource::query();

        $this->applyCommonFilters($baseQuery, $search, $searchField, $searchValue);
        $this->applyShamsiDateFilter($baseQuery, $year, $month, $dateField);
        $this->applyRelatedFilters($baseQuery, $req);
        $this->applyDateRangeFilter($baseQuery, $req, $dateField);

        $today = now()->toDateString();

        $totalCount = (clone $baseQuery)->count();

        $todayAddedCount = (clone $baseQuery)
            ->whereNotNull($dateField)
            ->whereDate($dateField, $today)
            ->count();

        $activeCount = (clone $baseQuery)
            ->where('activity', 'فعال')
            ->count();

        $inactiveCount = (clone $baseQuery)
            ->where('activity', 'غیر فعال')
            ->count();

            $q = Resource::query()
    ->with(['officeEmployee:id,name,code_number,father_name'])
    ->withSum('finances as payed_money', 'money_amount_number')
    ->withCount([
        'events',
        'informations',
        'khayratReports',
    ])
    ->when($stat === 'today', function ($w) use ($dateField, $today) {
        $w->whereNotNull($dateField)->whereDate($dateField, $today);
    })
    ->when($stat === 'active', function ($w) {
        $w->where('activity', 'فعال');
    })
    ->when($stat === 'inactive', function ($w) {
        $w->where('activity', 'غیر فعال');
    })
    ->latest('id');

    $this->applyCommonFilters($q, $search, $searchField, $searchValue);
    $this->applyShamsiDateFilter($q, $year, $month, $dateField);
    $this->applyRelatedFilters($q, $req);
    $this->applyDateRangeFilter($q, $req, $dateField);

        $perPage = $req->filled('perPage') ? (int) $req->get('perPage') : null;

        if ($perPage !== null && $perPage > 0) {
            $data = $q->paginate($perPage);

            return ResourceResource::collection($data)->additional([
                'meta' => [
                    'stats' => [
                        'total_count'       => $totalCount,
                        'active_count'      => $activeCount,
                        'inactive_count'    => $inactiveCount,
                        'today_added_count' => $todayAddedCount,
                    ],
                ],
            ]);
        }

        $rows = $q->get();

        return ResourceResource::collection($rows)
            ->additional([
                'meta' => [
                    'total' => $rows->count(),
                    'stats' => [
                        'total_count'       => $totalCount,
                        'active_count'      => $activeCount,
                        'inactive_count'    => $inactiveCount,
                        'today_added_count' => $todayAddedCount,
                    ],
                ],
            ]);
    }

    public function show($id)
    {
        $row = Resource::query()
            ->with(['officeEmployee:id,name,code_number,father_name,nick_name'])
            ->withSum('finances as payed_money', 'money_amount_number')
            ->withCount([
                'events',
                'informations',
                'khayratReports',
            ])
            ->findOrFail($id);

        return new ResourceResource($row);
    }

    public function store(ResourceRequest $req)
    {
        try {
            $data = $req->validated();
            $user = $req->user();

            unset($data['code_number']);

            if ($req->hasFile('picture')) {
                $path = $req->file('picture')->store('uploads/resources', 'public');
                $data['picture'] = "storage/{$path}";
            }

            $row = Resource::create($data);

            $row->code_number = $row->id;
            $row->save();

            NotificationService::record(
                $user,
                module: 'resources',
                action: 'created',
                title: 'نوی منبع ثبت شوو',
                message: 'د منبع کوډ' . ($row->code_number ?? ''),
                routeName: 'resources.edit',
                routeParams: ['id' => $row->id],
                level: 'success',
            );

            return (new ResourceResource(
                $row->loadMissing('officeEmployee:id,name,code_number,father_name')->loadCount(['events', 'informations', 'khayratReports'])
            ))
                ->additional(['message' => 'Created'])
                ->response()
                ->setStatusCode(201);
        } catch (QueryException $e) {
            if ($this->isDuplicateKey($e)) {
                return $this->duplicateResponsePashto();
            }

            return response()->json([
                'message' => 'Resource creation failed',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

//     public function store(ResourceRequest $req)
// {
//     try {
//         $data = $req->validated();
//         $user = $req->user();

//         unset($data['code_number']);

//         // ✅ FORCE all resources active
//         $data['activity'] = 'فعال';

//         if ($req->hasFile('picture')) {
//             $path = $req->file('picture')->store('uploads/resources', 'public');
//             $data['picture'] = "storage/{$path}";
//         }

//         $row = Resource::create($data);

//         $row->code_number = $row->id;
//         $row->save();

//         NotificationService::record(
//             $user,
//             module: 'resources',
//             action: 'created',
//             title: 'نوی منبع ثبت شوو',
//             message: 'د منبع کوډ' . ($row->code_number ?? ''),
//             routeName: 'resources.edit',
//             routeParams: ['id' => $row->id],
//             level: 'success',
//         );

//         return (new ResourceResource(
//             $row->loadMissing('officeEmployee:id,name,code_number,father_name')
//                 ->loadCount(['events', 'informations', 'khayratReports'])
//         ))
//             ->additional(['message' => 'Created'])
//             ->response()
//             ->setStatusCode(201);
//     } catch (QueryException $e) {
//         if ($this->isDuplicateKey($e)) {
//             return $this->duplicateResponsePashto();
//         }

//         return response()->json([
//             'message' => 'Resource creation failed',
//             'error'   => $e->getMessage(),
//         ], 500);
//     }
// }


       public function update(ResourceRequest $req, $id)
{
    try {
        $row  = Resource::findOrFail($id);
        $data = $req->validated();
        $user = $req->user();

        unset($data['code_number']);

        if ($req->input('remove_picture') == '1') {
            if ($row->picture && str_starts_with($row->picture, 'storage/')) {
                Storage::disk('public')->delete(str_replace('storage/', '', $row->picture));
            }

            $data['picture'] = null;
        }

        if ($req->hasFile('picture')) {
            if ($row->picture && str_starts_with($row->picture, 'storage/')) {
                Storage::disk('public')->delete(str_replace('storage/', '', $row->picture));
            }

            $path = $req->file('picture')->store('uploads/resources', 'public');
            $data['picture'] = "storage/{$path}";
        }

        $row->fill($data)->save();

        if ((string) $row->code_number !== (string) $row->id) {
            $row->code_number = $row->id;
            $row->save();
        }

        NotificationService::record(
            $user,
            module: 'resources',
            action: 'updated',
            title: 'منبع اصلاح شوو',
            message: 'د منبع کوډ' . ($row->code_number ?? ''),
            routeName: 'resources',
            routeParams: ['id' => $row->id],
            level: 'info',
        );

        return new ResourceResource(
            $row->loadMissing('officeEmployee:id,name,code_number,father_name')
                ->loadCount(['events', 'informations', 'khayratReports'])
        );
    } catch (QueryException $e) {
        if ($this->isDuplicateKey($e)) {
            return $this->duplicateResponsePashto();
        }

        return response()->json([
            'message' => 'Resource update failed',
            'error'   => $e->getMessage(),
        ], 500);
    }
}

//     public function update(ResourceRequest $req, $id)
// {
//     try {
//         $row  = Resource::findOrFail($id);
//         $data = $req->validated();
//         $user = $req->user();

//         unset($data['code_number']);

//         // ✅ FORCE all resources active
//         $data['activity'] = 'فعال';

//         if ($req->hasFile('picture')) {
//             if ($row->picture && str_starts_with($row->picture, 'storage/')) {
//                 Storage::disk('public')->delete(str_replace('storage/', '', $row->picture));
//             }

//             $path = $req->file('picture')->store('uploads/resources', 'public');
//             $data['picture'] = "storage/{$path}";
//         } else {
//             unset($data['picture']);
//         }

//         $row->fill($data)->save();

//         if ((string) $row->code_number !== (string) $row->id) {
//             $row->code_number = $row->id;
//             $row->save();
//         }

//         NotificationService::record(
//             $user,
//             module: 'resources',
//             action: 'updated',
//             title: 'منبع اصلاح شوو',
//             message: 'د منبع کوډ' . ($row->code_number ?? ''),
//             routeName: 'resources',
//             routeParams: ['id' => $row->id],
//             level: 'info',
//         );

//         return new ResourceResource(
//             $row->loadMissing('officeEmployee:id,name,code_number,father_name')
//                 ->loadCount(['events', 'informations', 'khayratReports'])
//         );
//     } catch (QueryException $e) {
//         if ($this->isDuplicateKey($e)) {
//             return $this->duplicateResponsePashto();
//         }

//         return response()->json([
//             'message' => 'Resource update failed',
//             'error'   => $e->getMessage(),
//         ], 500);
//     }
// }
    public function destroy(Request $request, $id)
    {
        $row  = Resource::findOrFail($id);
        $user = $request->user();

        $code = $row->code_number;

        if ($row->picture && str_starts_with($row->picture, 'storage/')) {
            Storage::disk('public')->delete(str_replace('storage/', '', $row->picture));
        }

        $row->delete();

        NotificationService::record(
            $user,
            module: 'resources',
            action: 'deleted',
            title: 'منبع حذف شوو',
            message: 'د منبع کوډ' . ($code ?? ''),
            routeName: null,
            routeParams: [],
            level: 'warning',
        );

        return response()->json(['message' => 'Deleted']);
    }


    public function exportExcel(Request $req)
{
    $fileName = 'resources_' . now()->format('Y_m_d_H_i_s') . '.xlsx';

    return Excel::download(
        new ResourcesExport($req->all()),
        $fileName
    );
}

    public function dateOptions(Request $req)
    {
        $dateField = $req->get('date_field', 'resource_saved_date');

        if (!in_array($dateField, ['resource_saved_date', 'created_at', 'updated_at'], true)) {
            $dateField = 'resource_saved_date';
        }

        $shamsiYear = $req->filled('year') ? (int) $req->get('year') : null;

        $dates = Resource::query()
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


    private function applyCommonFilters(
    $query,
    ?string $search,
    ?string $searchField = null,
    $searchValue = null
) {
    $query
        ->when(
            $searchValue !== null && $searchValue !== '' && $searchField,
            function ($q) use ($searchField, $searchValue) {
                if ($searchField === 'office_employee_code') {
                    $q->whereHas('officeEmployee', function ($qq) use ($searchValue) {
                        $qq->where('code_number', $searchValue);
                    });
                } else {
                    $q->where($searchField, $searchValue);
                }
            }
        )
        ->when($search, function ($q) use ($search, $searchField) {
            $resolvedField = $this->resolveSearchField($searchField);

            if ($resolvedField === 'office_employee_code') {
                $q->whereHas('officeEmployee', function ($qq) use ($search) {
                    $qq->where('code_number', 'like', "%{$search}%");
                });
            } elseif ($resolvedField) {
                $q->where($resolvedField, 'like', "%{$search}%");
            } else {
                $q->where(function ($qq) use ($search) {
                    foreach ($this->searchableColumns() as $index => $column) {
                        if ($column === 'office_employee_code') {
                            continue;
                        }

                        if ($index === 0) {
                            $qq->where($column, 'like', "%{$search}%");
                        } else {
                            $qq->orWhere($column, 'like', "%{$search}%");
                        }
                    }

                    $qq->orWhereHas('officeEmployee', function ($sub) use ($search) {
                        $sub->where('code_number', 'like', "%{$search}%");
                    });
                });
            }
        });

    return $query;
}

public function filterOptions(Request $request)
{
    $request->validate([
        'field' => 'required|string',
        'q'     => 'nullable|string',
    ]);

    $field = $this->resolveSearchField($request->input('field'));

    if (!$field) {
        return response()->json([
            'options' => [],
        ]);
    }

    $q = trim((string) $request->input('q', ''));

    if ($field === 'office_employee_code') {
        $options = \App\Models\OfficeEmployee::query()
            ->whereNotNull('code_number')
            ->where('code_number', '!=', '')
            ->when($q !== '', function ($query) use ($q) {
                $query->where('code_number', 'like', "%{$q}%");
            })
            ->select('code_number')
            ->distinct()
            ->orderBy('code_number')
            ->pluck('code_number')
            ->map(fn ($value) => [
                'title' => (string) $value,
                'value' => $value,
            ])
            ->values();

        return response()->json([
            'options' => $options,
        ]);
    }

    $query = Resource::query()
        ->whereNotNull($field)
        ->where($field, '!=', '');

    if ($q !== '') {
        $query->where($field, 'like', "%{$q}%");
    }

    $options = $query
        ->select($field)
        ->distinct()
        ->orderBy($field)
        ->pluck($field)
        ->map(fn ($value) => [
            'title' => (string) $value,
            'value' => $value,
        ])
        ->values();

    return response()->json([
        'options' => $options,
    ]);
}
}