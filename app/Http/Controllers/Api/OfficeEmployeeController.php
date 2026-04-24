<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\AppliesRelatedFilters;
use App\Http\Requests\OfficeEmployeeRequest;
use App\Http\Requests\UpdateOfficeEmployeeRequest;
use App\Http\Resources\OfficeEmployeeResource;
use App\Models\OfficeEmployee;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\QueryException;
use Carbon\Carbon;

class OfficeEmployeeController extends Controller
{
    use AppliesRelatedFilters;

    protected function relatedFilterRules(): array
    {
        return [
            'office_employee_id' => [
                'column' => 'id',
            ],

            'resource_id' => [
                'callback' => function ($query, $value) {
                    $query->whereHas('resources', fn ($q) => $q->where('id', $value));
                },
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
        ];
    }

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
            'name',
            'father_name',
            'grand_father_name',
            'nick_name',
            'code_number',
            'tazkira',
            'age',
            'job_type',
            'specific_job',
            'qisem',
            'bast',
            'level',
            'zone',
            'hawza',
            'history_jahad',
            'dilgay_mesher',
            'jahadi_mawaqaf',
            'jahad_time_responsibility',
            'jahad_place',
            'main_province',
            'main_district',
            'main_village',
            'now_province',
            'now_district',
            'now_village',
            'now_specific_place',
            'now_street_number',
            'now_home_number',
            'relegy_study',
            'new_study',
            'faculty',
            'nizami',
            'gun_knowledge',
            'phone_number',
            'language',
            'nation',
            'specific_skills',
            'activity',
            'coordinates',
            'makhafat',
            'makafat',
            'join_date',
            'left_date',
            'note',
        ];
    }

    private function resolveSearchField(?string $field): ?string
    {
        $field = trim((string) $field);

        return in_array($field, $this->searchableColumns(), true) ? $field : null;
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


    private function applyCommonFilters(
    $query,
    ?string $search,
    ?string $status,
    ?string $jobType,
    ?string $searchField = null,
    $searchValue = null
) {
    $query
        ->when($searchValue !== null && $searchValue !== '' && $searchField, function ($q) use ($searchField, $searchValue) {
            $q->where($searchField, $searchValue);
        })
        ->when($search, function ($q) use ($search, $searchField) {
            $resolvedField = $this->resolveSearchField($searchField);

            if ($resolvedField) {
                $q->where($resolvedField, 'like', "%{$search}%");
            } else {
                $q->where(function ($qq) use ($search) {
                    foreach ($this->searchableColumns() as $index => $column) {
                        if ($index === 0) {
                            $qq->where($column, 'like', "%{$search}%");
                        } else {
                            $qq->orWhere($column, 'like', "%{$search}%");
                        }
                    }
                });
            }
        })
       ->when($status && strtolower($status) !== 'all', function ($q) use ($status) {
    $from = now()->subDays(30)->startOfDay();
    $to   = now()->endOfDay();

    if ($status === 'active') {
        $q->whereHas('resources', function ($r) use ($from, $to) {
            $r->where('activity', 'فعال')
              ->whereBetween('resource_saved_date', [$from, $to]);
        }, '>=', 100);
    }

    if ($status === 'inactive') {
        $q->where(function ($qq) use ($from, $to) {
            $qq->whereDoesntHave('resources', function ($r) use ($from, $to) {
                $r->where('activity', 'فعال')
                  ->whereBetween('resource_saved_date', [$from, $to]);
            })
            ->orWhereHas('resources', function ($r) use ($from, $to) {
                $r->where('activity', 'فعال')
                  ->whereBetween('resource_saved_date', [$from, $to]);
            }, '<', 100);
        });
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


    private function buildCardCounts(
    Request $request,
    ?string $search,
    ?string $status,
    ?string $jobType,
    ?int $year,
    ?int $month,
    string $dateField,
    ?string $searchField = null,
    $searchValue = null
): array {

        $base = OfficeEmployee::query();

        $this->applyCommonFilters($base, $search, $status, $jobType, $searchField, $searchValue);
        $this->applyMonthYearFilter($base, $year, $month, $dateField);
        $this->applyRelatedFilters($base, $request);

        return [
            'total'      => (clone $base)->count(),
            'edari'      => (clone $base)->whereIn('job_type', ['اداری', 'اداري'])->count(),
            'operatifi'  => (clone $base)->whereIn('job_type', ['اوپراتیفی', 'اوپراتیفي'])->count(),
           
            'active' => (clone $base)
    ->whereHas('resources', function ($q) {
        $q->where('activity', 'فعال')
          ->whereDate('resource_saved_date', '>=', now()->subDays(30)->toDateString());
    }, '>=', 100)
    ->count(),

'inactive' => (clone $base)
    ->whereDoesntHave('resources', function ($q) {
        $q->where('activity', 'فعال')
          ->whereDate('resource_saved_date', '>=', now()->subDays(30)->toDateString());
    })
    ->orWhereHas('resources', function ($q) {
        $q->where('activity', 'فعال')
          ->whereDate('resource_saved_date', '>=', now()->subDays(30)->toDateString());
    }, '<', 100)
    ->count(),
        ];
    }

    public function dateOptions(Request $request)
    {
        $request->validate([
            'date_field' => 'nullable|string',
            'year'       => 'nullable|integer|min:1|max:2500',
        ]);

        $dateField   = $this->resolveDateField($request->input('date_field'));
        $shamsiYear  = $request->filled('year') ? (int) $request->input('year') : null;

        $dates = OfficeEmployee::query()
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

    public function index(Request $request)
    {
        $request->validate([
            'page'               => 'sometimes|integer|min:1',
            'perPage'            => 'sometimes',
            'search'             => 'nullable|string',
            'search_field'       => 'nullable|string',
            'status'             => 'nullable|string',
            'job_type'           => 'nullable|string',
            'year'               => 'nullable|integer|min:1|max:2500',
            'month'              => 'nullable|integer|min:1|max:12',
            'date_field'         => 'nullable|string',
            'office_employee_id' => 'nullable|integer',
            'resource_id'        => 'nullable|integer',
            'information_id'     => 'nullable|integer',
            'event_id'           => 'nullable|integer',
            'search_value'       => 'nullable',
        ]);

        $search      = $request->input('search');
        $searchField = $this->resolveSearchField($request->input('search_field'));
        $rawPerPage  = $request->input('perPage', null);
        $page        = (int) $request->input('page', 1);
        $status      = $request->input('status', 'all');
        $jobType     = $request->input('job_type');

        $year      = $request->filled('year') ? (int) $request->input('year') : null;
        $month     = $request->filled('month') ? (int) $request->input('month') : null;
        $dateField = $this->resolveDateField($request->input('date_field'));
        $searchValue = $request->input('search_value');
        

        $cardCounts = $this->buildCardCounts(
    $request,
    $search,
    $status,
    $jobType,
    $year,
    $month,
    $dateField,
    $searchField,
    $searchValue
);

        $query = $this->baseListQuery();

        $this->applyCommonFilters($query, $search, $status, $jobType, $searchField, $searchValue);
        $this->applyMonthYearFilter($query, $year, $month, $dateField);
        $this->applyRelatedFilters($query, $request);

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

    // public function store(OfficeEmployeeRequest $request)
    // {
    //     DB::beginTransaction();

    //     try {
    //         $user = $request->user();

    //         $employee = OfficeEmployee::create($request->validated());

    //         if (empty($employee->code_number)) {
    //             $employee->update(['code_number' => (string) $employee->id]);
    //         }

    //         if ($request->hasFile('picture')) {
    //             $path = $request->file('picture')->store('uploads/office-employees', 'public');
    //             $employee->update(['picture' => $path]);
    //         }

    //         if ($request->hasFile('documents')) {
    //             foreach ($request->file('documents') as $file) {
    //                 $path = $file->store("office_employees/{$employee->id}", 'public');
    //                 $employee->documents()->create([
    //                     'file_name'  => $file->getClientOriginalName(),
    //                     'file_path'  => $path,
    //                     'mime_type'  => $file->getClientMimeType(),
    //                     'size_bytes' => $file->getSize(),
    //                 ]);
    //             }
    //         }

    //         NotificationService::recordForAllUsers(
    //             actor: $user,
    //             module: 'کارمند',
    //             action: 'created',
    //             title: 'کارمند ثبت',
    //             message: 'کارمند: ' . ($employee->code_number ?? ''),
    //             routeName: 'officeemployee',
    //             routeParams: ['id' => $employee->id],
    //             level: 'success',
    //             changes: null,
    //         );

    //         DB::commit();

    //         return new OfficeEmployeeResource(
    //             $employee->load(['documents'])
    //                 ->loadSum('finances as payed_money', 'money_amount_number')
    //                 ->loadCount([
    //                     'events',
    //                     'resources',
    //                     'resources as resources_active_count' => fn ($q) => $q->where('activity', 'فعال'),
    //                     'resources as resources_inactive_count' => fn ($q) => $q->where('activity', 'غیر فعال'),
    //                     'informations',
    //                     'infoInformations',
    //                     'documents',
    //                 ])
    //         );
    //     } catch (QueryException $e) {
    //         DB::rollBack();

    //         if ($this->isDuplicateKey($e)) {
    //             return $this->duplicateResponsePashto();
    //         }

    //         return response()->json([
    //             'message' => 'Office employee creation failed',
    //             'error'   => $e->getMessage(),
    //         ], 500);
    //     } catch (\Throwable $e) {
    //         DB::rollBack();

    //         return response()->json([
    //             'message' => 'Office employee creation failed',
    //             'error'   => $e->getMessage(),
    //         ], 500);
    //     }
    // }

    public function store(OfficeEmployeeRequest $request)
{
    DB::beginTransaction();

    try {
        $user = $request->user();

        $data = $request->validated();

        $employee = OfficeEmployee::create($data);

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


        if ($request->input('remove_picture') == '1') {
            if ($officeEmployee->picture) {
                Storage::disk('public')->delete($officeEmployee->picture);
            }

            $data['picture'] = null;
        }

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
            'search'             => 'nullable|string',
            'search_field'       => 'nullable|string',
            'perPage'            => 'sometimes',
            'year'               => 'nullable|integer|min:1900|max:2500',
            'month'              => 'nullable|integer|min:1|max:12',
            'date_field'         => 'nullable|string',
            'office_employee_id' => 'nullable|integer',
            'resource_id'        => 'nullable|integer',
            'information_id'     => 'nullable|integer',
            'event_id'           => 'nullable|integer',
        ]);

        $year        = $request->filled('year') ? (int) $request->input('year') : null;
        $month       = $request->filled('month') ? (int) $request->input('month') : null;
        $dateField   = $this->resolveDateField($request->input('date_field'));
        $searchField = $this->resolveSearchField($request->input('search_field'));

        $query = $this->baseListQuery()->inactive()->orderByDesc('id');

        if ($search = $request->input('search')) {
            if ($searchField) {
                $query->where($searchField, 'like', "%{$search}%");
            } else {
                $query->where(function ($q) use ($search) {
                    foreach ($this->searchableColumns() as $index => $column) {
                        if ($index === 0) {
                            $q->where($column, 'like', "%{$search}%");
                        } else {
                            $q->orWhere($column, 'like', "%{$search}%");
                        }
                    }
                });
            }
        }

        $this->applyMonthYearFilter($query, $year, $month, $dateField);
        $this->applyRelatedFilters($query, $request);

        $perPage = $request->input('perPage', 0);

        if (!$perPage || strtolower((string) $perPage) === 'all') {
            return OfficeEmployeeResource::collection($query->get());
        }

        return OfficeEmployeeResource::collection($query->paginate((int) $perPage));
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

    $query = OfficeEmployee::query()
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
public function printView(Request $request)
{
    $query = $this->baseListQuery();

    $search      = $request->input('search');
    $searchField = $this->resolveSearchField($request->input('search_field'));
    $searchValue = $request->input('search_value');
    $status      = $request->input('status', 'all');
    $jobType     = $request->input('job_type');

    $year      = $request->filled('year') ? (int) $request->input('year') : null;
    $month     = $request->filled('month') ? (int) $request->input('month') : null;
    $dateField = $this->resolveDateField($request->input('date_field'));

    $this->applyCommonFilters($query, $search, $status, $jobType, $searchField, $searchValue);
    $this->applyMonthYearFilter($query, $year, $month, $dateField);
    $this->applyRelatedFilters($query, $request);

   $rows = $query
    ->latest('id')
    ->get()
    ->map(function ($employee) {
        foreach (['join_date', 'left_date'] as $field) {
            if (!empty($employee->{$field})) {
                try {
                    $carbon = \Carbon\Carbon::parse($employee->{$field});

                    [$jy, $jm, $jd] = $this->gregorianToJalali(
                        (int) $carbon->format('Y'),
                        (int) $carbon->format('m'),
                        (int) $carbon->format('d')
                    );

                    $employee->{$field} = sprintf('%04d/%02d/%02d', $jy, $jm, $jd);
                } catch (\Throwable $e) {
                    //
                }
            }
        }

        $activeResourcesCount = $employee->resources()
            ->where('activity', 'فعال')
            ->whereDate('resource_saved_date', '>=', now()->subDays(30)->toDateString())
            ->count();

        $employee->activity = $activeResourcesCount >= 100 ? 'فعال' : 'غیر فعال';

        return $employee;
    });

    return response()->view('prints.table-print', [
        'title'           => $request->input('title', 'چاپ کارمندان مدیریت'),
        'rows'            => $rows,
        'selectedColumns' => $request->input('selected_columns', []),
        'columnLabels'    => $request->input('column_labels', []),
        'printLabels'     => $request->input('print_labels', []),
        'locale'          => $request->input('locale', 'fa'),
    ]);
}
}