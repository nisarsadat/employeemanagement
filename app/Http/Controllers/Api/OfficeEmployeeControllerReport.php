<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\OfficeEmployeeRequest;
use App\Models\OfficeEmployee;
use App\Models\Event;
use App\Models\Resource;
use App\Models\Information;
use Illuminate\Http\Request;
use App\Http\Requests\StoreOfficeEmployeeRequest;
use App\Http\Requests\UpdateOfficeEmployeeRequest;
use App\Http\Resources\OfficeEmployeeResource;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class OfficeEmployeeControllerReport extends Controller
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

    /**
     * Show a single office employee with counts & documents.
     */
    public function show($id)
    {
        $officeEmployee = OfficeEmployee::with(['documents'])
            ->withCount(['events', 'resources', 'informations', 'documents'])
            ->findOrFail($id);

        return new OfficeEmployeeResource($officeEmployee);
    }

    /**
     * ✅ Quick counts for UI badges.
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
                    $path = $file->store("office_employees/{$employee->id}", 'public');
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
                if ($officeEmployee->picture && str_starts_with($officeEmployee->picture, 'storage/')) {
                    $diskPath = str_replace('storage/', '', $officeEmployee->picture);
                    Storage::disk('public')->delete($diskPath);
                }
                $path = $request->file('picture')->store('uploads/office-employees', 'public');
                $officeEmployee->update(['picture' => "storage/{$path}"]);
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
                $officeEmployee->load(['documents'])
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
    public function destroy($id)
    {
        $officeEmployee = OfficeEmployee::with('documents')->findOrFail($id);

        // Delete files from storage
        foreach ($officeEmployee->documents as $doc) {
            Storage::disk('public')->delete($doc->file_path);
        }

        // Delete DB records
        $officeEmployee->documents()->delete();

        // Delete profile picture if present
        if ($officeEmployee->picture && str_starts_with($officeEmployee->picture, 'storage/')) {
            $diskPath = str_replace('storage/', '', $officeEmployee->picture);
            Storage::disk('public')->delete($diskPath);
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

    // -------------------------------------------------------------
    // 📊 Full Office Employee Activity Report (grouped by Province)
    // -------------------------------------------------------------
    public function activityReport(Request $request)
    {
        $request->validate([
            'search'        => 'nullable|string',

            'status'        => 'nullable|string', // 'active' | 'inactive' | 'فعال' | 'غیر فعال' | 'all'

            // grouping/filter
            'province_addr' => 'nullable|string',

            // home address filters
            'main_province' => 'nullable|string',
            'main_district' => 'nullable|string',

            // current address filters
            'now_province'  => 'nullable|string',
            'now_district'  => 'nullable|string',

            // structure filters
            'zone'          => 'nullable|string',
            'hawza'         => 'nullable|string',
            'madani_halat'  => 'nullable|string',

            // time filters
            'period'        => 'nullable|string', // daily|weekly|monthly|yearly|custom|all
            'from_date'     => 'nullable|date',
            'to_date'       => 'nullable|date',
        ]);

        $search        = $request->input('search');
        $status        = $request->input('status', 'all');
        $provinceAddr  = $request->input('province_addr');

        $mainProvince  = $request->input('main_province');
        $mainDistrict  = $request->input('main_district');
        $nowProvince   = $request->input('now_province');
        $nowDistrict   = $request->input('now_district');

        $zone          = $request->input('zone');
        $hawza         = $request->input('hawza');
        $madaniHalat   = $request->input('madani_halat');

        $period        = strtolower($request->input('period', 'all'));
        $fromDateInput = $request->input('from_date');
        $toDateInput   = $request->input('to_date');

        $activeVals   = ['active', OfficeEmployee::ACTIVE];     // 'active' | 'فعال'
        $inactiveVals = ['inactive', OfficeEmployee::INACTIVE]; // 'inactive' | 'غیر فعال'

        // ---------------------------------------------------------
        // ⏱️ Resolve date range for time filter (events/resources/info)
        // ---------------------------------------------------------
        $dateFrom = null;
        $dateTo   = null;

        if ($period === 'daily') {
            $dateFrom = Carbon::today()->startOfDay();
            $dateTo   = Carbon::today()->endOfDay();
        } elseif ($period === 'weekly') {
            $dateFrom = Carbon::now()->startOfWeek();
            $dateTo   = Carbon::now()->endOfWeek();
        } elseif ($period === 'monthly') {
            $dateFrom = Carbon::now()->startOfMonth();
            $dateTo   = Carbon::now()->endOfMonth();
        } elseif ($period === 'yearly') {
            $dateFrom = Carbon::now()->startOfYear();
            $dateTo   = Carbon::now()->endOfYear();
        } elseif ($period === 'custom') {
            if ($fromDateInput) {
                $dateFrom = Carbon::parse($fromDateInput)->startOfDay();
            }
            if ($toDateInput) {
                $dateTo = Carbon::parse($toDateInput)->endOfDay();
            }
        }
        // if period == 'all' => no date filter

        // ---------------------------------------------------------
        // 🧮 Base query with counts and last-activity subselects
        // ---------------------------------------------------------
        $query = OfficeEmployee::query()
            // 🔢 counts (restricted by period if provided)
            ->withCount([
                'events as events_count' => function ($q) use ($dateFrom, $dateTo) {
                    if ($dateFrom && $dateTo) {
                        $q->whereBetween('event_date', [$dateFrom, $dateTo]);
                    }
                },
                'resources as resources_count' => function ($q) use ($dateFrom, $dateTo) {
                    if ($dateFrom && $dateTo) {
                        $q->whereBetween('resource_saved_date', [$dateFrom, $dateTo]);
                    }
                },
                'informations as informations_count' => function ($q) use ($dateFrom, $dateTo) {
                    if ($dateFrom && $dateTo) {
                        $q->whereBetween('information_saved_date', [$dateFrom, $dateTo]);
                    }
                },
            ])
            // 🔗 singular "resource" relation for minimal summary card
            ->with([
                'resource:id,name,code_number,phone_number',
            ])
            // 🕒 last activity dates from related tables
            ->addSelect([
                'last_event_date' => Event::selectRaw('MAX(event_date)')
                    ->when($dateFrom && $dateTo, function ($q) use ($dateFrom, $dateTo) {
                        $q->whereBetween('event_date', [$dateFrom, $dateTo]);
                    })
                    ->whereColumn('office_employee_id', 'office_employees.id'),

                'last_resource_date' => Resource::selectRaw('MAX(resource_saved_date)')
                    ->when($dateFrom && $dateTo, function ($q) use ($dateFrom, $dateTo) {
                        $q->whereBetween('resource_saved_date', [$dateFrom, $dateTo]);
                    })
                    ->whereColumn('office_employee_id', 'office_employees.id'),

                'last_information_date' => Information::selectRaw('MAX(information_saved_date)')
                    ->when($dateFrom && $dateTo, function ($q) use ($dateFrom, $dateTo) {
                        $q->whereBetween('information_saved_date', [$dateFrom, $dateTo]);
                    })
                    ->whereColumn('office_employee_id', 'office_employees.id'),
            ])
            // 🔍 search
            ->when($search, function ($q) use ($search) {
                $q->where(function ($qq) use ($search) {
                    $qq->where('name', 'like', "%{$search}%")
                       ->orWhere('code_number', 'like', "%{$search}%")
                       ->orWhere('phone_number', 'like', "%{$search}%");
                });
            })
            // 🎛 status
            ->when($status && strtolower($status) !== 'all', function ($q) use ($status, $activeVals, $inactiveVals) {
                if (in_array($status, $activeVals, true)) {
                    $q->where('activity', OfficeEmployee::ACTIVE);
                } elseif (in_array($status, $inactiveVals, true)) {
                    $q->where('activity', OfficeEmployee::INACTIVE);
                }
            })
            // 🎛 grouping key / identity province
            ->when($provinceAddr, function ($q) use ($provinceAddr) {
                // ⚠ column name is EXACTLY 'Province_addr'
                $q->where('Province_addr', $provinceAddr);
            })
            // 🏠 main address filters
            ->when($mainProvince, function ($q) use ($mainProvince) {
                $q->where('main_province', $mainProvince);
            })
            ->when($mainDistrict, function ($q) use ($mainDistrict) {
                $q->where('main_district', $mainDistrict);
            })
            // 🏠 current address filters
            ->when($nowProvince, function ($q) use ($nowProvince) {
                $q->where('now_province', $nowProvince);
            })
            ->when($nowDistrict, function ($q) use ($nowDistrict) {
                $q->where('now_district', $nowDistrict);
            })
            // 🕌 structure / organization filters
            ->when($zone, function ($q) use ($zone) {
                $q->where('zone', $zone);
            })
            ->when($hawza, function ($q) use ($hawza) {
                $q->where('hawza', $hawza);
            })
            ->when($madaniHalat, function ($q) use ($madaniHalat) {
                $q->where('madani_halat', $madaniHalat);
            })
            ->orderBy('Province_addr')
            ->orderBy('name');

        // For reports we take ALL; frontend can group/paginate visually
        $employees = $query->get();

        $now = Carbon::now();

        // Global totals (for header badges)
        $globalTotals = [
            'employees'    => $employees->count(),
            'active'       => $employees->where('activity', OfficeEmployee::ACTIVE)->count(),
            'inactive'     => $employees->where('activity', OfficeEmployee::INACTIVE)->count(),
            'events'       => $employees->sum('events_count'),
            'resources'    => $employees->sum('resources_count'),
            'informations' => $employees->sum('informations_count'),
        ];

        // Group by Province_addr
        $grouped = $employees->groupBy('Province_addr');

        $data = $grouped->map(function ($group, $province) use ($now) {
            $province = $province ?: '---';

            // Totals per province
            $totalEmployees  = $group->count();
            $totalActive     = $group->where('activity', OfficeEmployee::ACTIVE)->count();
            $totalInactive   = $group->where('activity', OfficeEmployee::INACTIVE)->count();
            $totalEvents     = $group->sum('events_count');
            $totalResources  = $group->sum('resources_count');
            $totalInfos      = $group->sum('informations_count');

            return [
                'province_addr' => $province,
                'totals' => [
                    'employees'    => $totalEmployees,
                    'active'       => $totalActive,
                    'inactive'     => $totalInactive,
                    'events'       => $totalEvents,
                    'resources'    => $totalResources,
                    'informations' => $totalInfos,
                ],
                'employees' => $group->map(function ($emp) use ($now) {
                    // last activity across all three sources
                    $lastActivity = collect([
                        $emp->last_event_date,
                        $emp->last_resource_date,
                        $emp->last_information_date,
                    ])->filter()->max();

                    $daysSinceLast = $lastActivity
                        ? Carbon::parse($lastActivity)->diffInDays($now)
                        : null;

                    return [
                        'id'          => $emp->id,
                        'name'        => $emp->name,
                        'father_name' => $emp->father_name,
                        'code_number' => $emp->code_number,
                        'phone_number'=> $emp->phone_number,

                        'Province_addr' => $emp->Province_addr,
                        'main_province' => $emp->main_province,
                        'main_district' => $emp->main_district,
                        'now_province'  => $emp->now_province,
                        'now_district'  => $emp->now_district,

                        'zone'          => $emp->zone,
                        'hawza'         => $emp->hawza,
                        'madani_halat'  => $emp->madani_halat,

                        'activity'    => $emp->activity,
                        'join_date'   => $emp->join_date,
                        'left_date'   => $emp->left_date,

                        // raw counts (within selected period, if any)
                        'events_count'        => $emp->events_count,
                        'resources_count'     => $emp->resources_count,
                        'informations_count'  => $emp->informations_count,

                        // last activity details
                        'last_event_date'        => $emp->last_event_date,
                        'last_resource_date'     => $emp->last_resource_date,
                        'last_information_date'  => $emp->last_information_date,
                        'last_activity_date'     => $lastActivity,
                        'days_since_last_activity' => $daysSinceLast,

                        // small summary of main resource (for chips/cards in UI)
                        'main_resource' => $emp->resource ? [
                            'id'           => $emp->resource->id,
                            'name'         => $emp->resource->name,
                            'code_number'  => $emp->resource->code_number,
                            'phone_number' => $emp->resource->phone_number,
                        ] : null,
                    ];
                })->values(),
            ];
        })->values();

        return response()->json([
            'meta' => [
                'period'      => $period,
                'date_from'   => $dateFrom ? $dateFrom->toDateString() : null,
                'date_to'     => $dateTo ? $dateTo->toDateString() : null,
                'totals'      => $globalTotals,
                'filters'     => [
                    'status'        => $status,
                    'province_addr' => $provinceAddr,
                    'main_province' => $mainProvince,
                    'main_district' => $mainDistrict,
                    'now_province'  => $nowProvince,
                    'now_district'  => $nowDistrict,
                    'zone'          => $zone,
                    'hawza'         => $hawza,
                    'madani_halat'  => $madaniHalat,
                    'search'        => $search,
                ],
            ],
            'data' => $data,
        ]);
    }

    // -------------------------------------------------------------
    // 🔍 NEW: Detail view for one employee’s relation (events/resources/informations)
    // GET /office-employees-report/{employee}/{relation}
    // relation = events | resources | informations
    // -------------------------------------------------------------
    public function relationItems(Request $request, $employeeId, $relation)
    {
        $request->validate([
            'period'    => 'nullable|string', // daily|weekly|monthly|yearly|custom|all
            'from_date' => 'nullable|date',
            'to_date'   => 'nullable|date',
        ]);

        $relation = strtolower($relation);

        /** @var OfficeEmployee $employee */
        $employee = OfficeEmployee::findOrFail($employeeId);

        $period        = strtolower($request->input('period', 'all'));
        $fromDateInput = $request->input('from_date');
        $toDateInput   = $request->input('to_date');

        // Resolve date range
        $dateFrom = null;
        $dateTo   = null;

        if ($period === 'daily') {
            $dateFrom = Carbon::today()->startOfDay();
            $dateTo   = Carbon::today()->endOfDay();
        } elseif ($period === 'weekly') {
            $dateFrom = Carbon::now()->startOfWeek();
            $dateTo   = Carbon::now()->endOfWeek();
        } elseif ($period === 'monthly') {
            $dateFrom = Carbon::now()->startOfMonth();
            $dateTo   = Carbon::now()->endOfMonth();
        } elseif ($period === 'yearly') {
            $dateFrom = Carbon::now()->startOfYear();
            $dateTo   = Carbon::now()->endOfYear();
        } elseif ($period === 'custom') {
            if ($fromDateInput) {
                $dateFrom = Carbon::parse($fromDateInput)->startOfDay();
            }
            if ($toDateInput) {
                $dateTo = Carbon::parse($toDateInput)->endOfDay();
            }
        }

        $rows = collect();
        $dateColumn = null;

        switch ($relation) {
            case 'events':
                $dateColumn = 'event_date';
                $query = Event::query()
                    ->where('office_employee_id', $employee->id)
                    ->when($dateFrom && $dateTo, function ($q) use ($dateFrom, $dateTo, $dateColumn) {
                        $q->whereBetween($dateColumn, [$dateFrom, $dateTo]);
                    })
                    ->orderByDesc($dateColumn);

                // ⚠️ Adjust these fields to your real Event columns
                $rows = $query->get()->map(function (Event $e) {
                    return [
                        'id'         => $e->id,
                        'event_date' => $e->event_date,
                        'type'       => $e->event_type ?? null,
                        'title'      => $e->title ?? null,
                        'zone'       => $e->zone ?? null,
                        'hawza'      => $e->hawza ?? null,
                        'details'    => $e->details ?? null,
                    ];
                });
                break;

            case 'resources':
                $dateColumn = 'resource_saved_date';
                $query = Resource::query()
                    ->where('office_employee_id', $employee->id)
                    ->when($dateFrom && $dateTo, function ($q) use ($dateFrom, $dateTo, $dateColumn) {
                        $q->whereBetween($dateColumn, [$dateFrom, $dateTo]);
                    })
                    ->orderByDesc($dateColumn);

                // ⚠️ Adjust these fields to your real Resource columns
                $rows = $query->get()->map(function (Resource $r) {
                    return [
                        'id'                  => $r->id,
                        'resource_saved_date' => $r->resource_saved_date,
                        'name'                => $r->name ?? null,
                        'code_number'         => $r->code_number ?? null,
                        'phone_number'        => $r->phone_number ?? null,
                        'type'                => $r->type ?? null,
                        'details'             => $r->details ?? null,
                    ];
                });
                break;

            case 'informations':
                $dateColumn = 'information_saved_date';
                $query = Information::query()
                    ->where('office_employee_id', $employee->id)
                    ->when($dateFrom && $dateTo, function ($q) use ($dateFrom, $dateTo, $dateColumn) {
                        $q->whereBetween($dateColumn, [$dateFrom, $dateTo]);
                    })
                    ->orderByDesc($dateColumn);

                // ⚠️ Adjust these fields to your real Information columns
                $rows = $query->get()->map(function (Information $i) {
                    return [
                        'id'                     => $i->id,
                        'information_saved_date' => $i->information_saved_date,
                        'title'                  => $i->title ?? null,
                        'category'               => $i->category ?? null,
                        'source'                 => $i->source ?? null,
                        'details'                => $i->details ?? null,
                    ];
                });
                break;

            default:
                return response()->json([
                    'message' => 'Unsupported relation type',
                ], 404);
        }

        return response()->json([
            'meta' => [
                'relation'   => $relation,
                'period'     => $period,
                'date_from'  => $dateFrom ? $dateFrom->toDateString() : null,
                'date_to'    => $dateTo ? $dateTo->toDateString() : null,
                'employee'   => [
                    'id'           => $employee->id,
                    'name'         => $employee->name,
                    'father_name'  => $employee->father_name,
                    'code_number'  => $employee->code_number,
                    'phone_number' => $employee->phone_number,
                    'province'     => $employee->Province_addr,
                    'zone'         => $employee->zone,
                    'hawza'        => $employee->hawza,
                    'activity'     => $employee->activity,
                ],
                'total'      => $rows->count(),
            ],
            'data' => $rows->values(),
        ]);

    }
                public function employeeRelation(Request $request, $id, $relation)
    {
        $employee = OfficeEmployee::findOrFail($id);

        $relation = strtolower($relation);
        $rows = [];

        switch ($relation) {
            case 'events':
                $rows = Event::where('office_employee_id', $id)
                    ->orderByDesc('event_date')
                    ->get()
                    ->map(function ($e) {
                        return [
                            'id'          => $e->id,
                            'event_date'  => $e->event_date,
                            'type'        => $e->event_type ?? null,
                            'title'       => $e->title ?? null,
                            'zone'        => $e->zone ?? null,
                            'hawza'       => $e->hawza ?? null,
                            'details'     => $e->details ?? $e->description ?? null,
                        ];
                    })->values();
                break;

            case 'resources':
                $rows = Resource::where('office_employee_id', $id)
                    ->orderByDesc('resource_saved_date')
                    ->get()
                    ->map(function ($r) {
                        return [
                            'id'                  => $r->id,
                            'resource_saved_date' => $r->resource_saved_date,
                            'name'                => $r->name ?? null,
                            'code_number'         => $r->code_number ?? null,
                            'phone_number'        => $r->phone_number ?? null,
                            'type'                => $r->type ?? null,
                            'details'             => $r->details ?? $r->description ?? null,
                        ];
                    })->values();
                break;

            case 'informations':
                $rows = Information::where('office_employee_id', $id)
                    ->orderByDesc('information_saved_date')
                    ->get()
                    ->map(function ($i) {
                        return [
                            'id'                     => $i->id,
                            'information_saved_date' => $i->information_saved_date,
                            'title'                  => $i->title ?? null,
                            'category'               => $i->category ?? null,
                            'source'                 => $i->source ?? null,
                            'details'                => $i->details ?? $i->description ?? null,
                        ];
                    })->values();
                break;

            default:
                return response()->json([
                    'message' => 'Unknown relation type.',
                ], 404);
        }

        return response()->json([
            'meta' => [
                'employee' => [
                    'id'            => $employee->id,
                    'name'          => $employee->name,
                    'father_name'   => $employee->father_name,
                    'code_number'   => $employee->code_number,
                    'phone_number'  => $employee->phone_number,
                    'province_addr' => $employee->Province_addr,
                    'zone'          => $employee->zone,
                    'hawza'         => $employee->hawza,
                    'activity'      => $employee->activity,
                ],
            ],
            'data' => $rows,
        ]);
    }

}
