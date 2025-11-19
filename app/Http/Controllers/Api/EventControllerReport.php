<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\OfficeEmployee;
use App\Models\Resource;
use Illuminate\Http\Request;
use Carbon\Carbon;

class EventControllerReport extends Controller
{
    // -------------------------------------------------------------
    // 🔹 Simple list of events with search & filters (for raw table)
    // GET /events-report
    // -------------------------------------------------------------
    public function index(Request $request)
    {
        $request->validate([
            'page'             => 'sometimes|integer|min:1',
            'perPage'          => 'sometimes', // int | "all" | missing
            'search'           => 'nullable|string',
            'province_addr'    => 'nullable|string',
            'zone'             => 'nullable|string',
            'hawza'            => 'nullable|string',
            'event_type'       => 'nullable|string',
            'event_specific_type' => 'nullable|string',
            'target_person'    => 'nullable|string',
            'criminal'         => 'nullable|string', // "yes" | "no" | bool-like
            'office_employee_id' => 'nullable|integer',
            'resource_id'      => 'nullable|integer',

            'period'           => 'nullable|string', // daily|weekly|monthly|yearly|custom|all
            'from_date'        => 'nullable|date',
            'to_date'          => 'nullable|date',
        ]);

        $search        = $request->input('search');
        $provinceAddr  = $request->input('province_addr');
        $zone          = $request->input('zone');
        $hawza         = $request->input('hawza');
        $eventType     = $request->input('event_type');
        $eventSpecific = $request->input('event_specific_type');
        $targetPerson  = $request->input('target_person');
        $criminal      = $request->input('criminal');
        $officeEmpId   = $request->input('office_employee_id');
        $resourceId    = $request->input('resource_id');

        $rawPerPage    = $request->input('perPage', null);
        $page          = (int) $request->input('page', 1);

        // ---- Date range (same logic as report) ----
        $period        = strtolower($request->input('period', 'all'));
        $fromDateInput = $request->input('from_date');
        $toDateInput   = $request->input('to_date');

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

        $query = Event::query()
            ->with([
                'resource:id,name,code_number,phone_number',
                'officeEmployee:id,name,father_name,code_number,phone_number,Province_addr,zone,hawza',
            ])
            ->when($search, function ($q) use ($search) {
                $q->where(function ($qq) use ($search) {
                    $qq->where('tafseel', 'like', "%{$search}%")
                        ->orWhere('event_specific_place', 'like', "%{$search}%")
                        ->orWhere('target_person', 'like', "%{$search}%")
                        ->orWhere('note', 'like', "%{$search}%");
                });
            })
            ->when($provinceAddr, fn ($q) => $q->where('Province_addr', $provinceAddr))
            ->when($zone, fn ($q) => $q->where('event_zone', $zone))
            ->when($hawza, fn ($q) => $q->where('event_hawza', $hawza))
            ->when($eventType, fn ($q) => $q->where('event_type', $eventType))
            ->when($eventSpecific, fn ($q) => $q->where('event_specific_type', $eventSpecific))
            ->when($targetPerson, fn ($q) => $q->where('target_person', 'like', "%{$targetPerson}%"))
            ->when($officeEmpId, fn ($q) => $q->where('office_employee_id', $officeEmpId))
            ->when($resourceId, fn ($q) => $q->where('resource_id', $resourceId))
            ->when($criminal !== null && $criminal !== '',
                function ($q) use ($criminal) {
                    if (in_array(strtolower((string) $criminal), ['1', 'true', 'yes', 'بلی', 'هو'], true)) {
                        $q->whereNotNull('criminal')->where('criminal', '!=', '');
                    } elseif (in_array(strtolower((string) $criminal), ['0', 'false', 'no', 'نه'], true)) {
                        $q->where(function ($qq) {
                            $qq->whereNull('criminal')->orWhere('criminal', '');
                        });
                    }
                }
            )
            ->when($dateFrom && $dateTo, function ($q) use ($dateFrom, $dateTo) {
                $q->whereBetween('event_date', [$dateFrom, $dateTo]);
            })
            ->orderByDesc('event_date')
            ->orderByDesc('id');

        // Return ALL if perPage missing / "all" / 0
        if (is_null($rawPerPage) || $rawPerPage === '' || strtolower((string) $rawPerPage) === 'all' || (int) $rawPerPage === 0) {
            $items = $query->get();

            $rows = $items->map(fn (Event $e) => $this->mapEventRow($e));

            return response()->json([
                'meta' => [
                    'period'    => $period,
                    'date_from' => $dateFrom ? $dateFrom->toDateString() : null,
                    'date_to'   => $dateTo ? $dateTo->toDateString() : null,
                ],
                'data' => $rows,
            ]);
        }

        $perPage = max(1, (int) $rawPerPage);

        $pageResult = $query->paginate($perPage, ['*'], 'page', $page);

        $rows = collect($pageResult->items())->map(fn (Event $e) => $this->mapEventRow($e));

        return response()->json([
            'meta' => [
                'period'     => $period,
                'date_from'  => $dateFrom ? $dateFrom->toDateString() : null,
                'date_to'    => $dateTo ? $dateTo->toDateString() : null,
                'pagination' => [
                    'total'        => $pageResult->total(),
                    'per_page'     => $pageResult->perPage(),
                    'current_page' => $pageResult->currentPage(),
                    'last_page'    => $pageResult->lastPage(),
                ],
            ],
            'data' => $rows,
        ]);
    }

    // Small helper to normalize event payload
    protected function mapEventRow(Event $e): array
    {
        $now = Carbon::now();
        $daysSince = $e->event_date ? Carbon::parse($e->event_date)->diffInDays($now) : null;

        return [
            'id'                  => $e->id,
            'event_date'          => $e->event_date,
            'event_hour'          => $e->event_hour,
            'event_type'          => $e->event_type,
            'event_specific_type' => $e->event_specific_type,
            'target_person'       => $e->target_person,
            'criminal'            => $e->criminal,
            'event_zone'          => $e->event_zone,
            'event_hawza'         => $e->event_hawza,
            'Province_addr'       => $e->Province_addr,
            'event_specific_place'=> $e->event_specific_place,
            'talafat_jani'        => $e->talafat_jani,
            'talafat_mali'        => $e->talafat_mali,
            'tafseel'             => $e->tafseel,
            'talks'               => $e->talks,
            'coordinates'         => $e->coordinates,
            'note'                => $e->note,

            'has_talafat_jani'    => !empty($e->talafat_jani),
            'has_talafat_mali'    => !empty($e->talafat_mali),
            'days_since_event'    => $daysSince,

            'resource'            => $e->resource ? [
                'id'           => $e->resource->id,
                'name'         => $e->resource->name,
                'code_number'  => $e->resource->code_number,
                'phone_number' => $e->resource->phone_number,
            ] : null,

            'office_employee'     => $e->officeEmployee ? [
                'id'           => $e->officeEmployee->id,
                'name'         => $e->officeEmployee->name,
                'father_name'  => $e->officeEmployee->father_name,
                'code_number'  => $e->officeEmployee->code_number,
                'phone_number' => $e->officeEmployee->phone_number,
                'province'     => $e->officeEmployee->Province_addr,
                'zone'         => $e->officeEmployee->zone,
                'hawza'        => $e->officeEmployee->hawza,
            ] : null,
        ];
    }

    // -------------------------------------------------------------
    // 📊 Quick counts for summary cards
    // GET /events-report/counts
    // -------------------------------------------------------------
    public function counts(Request $request)
    {
        $request->validate([
            'period'    => 'nullable|string', // daily|weekly|monthly|yearly|custom|all
            'from_date' => 'nullable|date',
            'to_date'   => 'nullable|date',
        ]);

        $period        = strtolower($request->input('period', 'all'));
        $fromDateInput = $request->input('from_date');
        $toDateInput   = $request->input('to_date');

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

        $base = Event::query()
            ->when($dateFrom && $dateTo, function ($q) use ($dateFrom, $dateTo) {
                $q->whereBetween('event_date', [$dateFrom, $dateTo]);
            });

        $totalEvents      = (clone $base)->count();
        $withCriminal     = (clone $base)->whereNotNull('criminal')->where('criminal', '!=', '')->count();
        $withTalafatJani  = (clone $base)->whereNotNull('talafat_jani')->where('talafat_jani', '!=', '')->count();
        $withTalafatMali  = (clone $base)->whereNotNull('talafat_mali')->where('talafat_mali', '!=', '')->count();

        return response()->json([
            'meta' => [
                'period'    => $period,
                'date_from' => $dateFrom ? $dateFrom->toDateString() : null,
                'date_to'   => $dateTo ? $dateTo->toDateString() : null,
            ],
            'data' => [
                'events'             => $totalEvents,
                'with_criminal'      => $withCriminal,
                'with_talafat_jani'  => $withTalafatJani,
                'with_talafat_mali'  => $withTalafatMali,
            ],
        ]);
    }

    // -------------------------------------------------------------
    // 📊 Full Event Activity Report (grouped by Province_addr)
    // GET /events-report/activity-report
    // -------------------------------------------------------------
    public function activityReport(Request $request)
    {
        $request->validate([
            'search'              => 'nullable|string',
            'province_addr'       => 'nullable|string',
            'zone'                => 'nullable|string',
            'hawza'               => 'nullable|string',
            'event_type'          => 'nullable|string',
            'event_specific_type' => 'nullable|string',
            'target_person'       => 'nullable|string',
            'criminal'            => 'nullable|string',
            'office_employee_id'  => 'nullable|integer',
            'resource_id'         => 'nullable|integer',

            'period'              => 'nullable|string', // daily|weekly|monthly|yearly|custom|all
            'from_date'           => 'nullable|date',
            'to_date'             => 'nullable|date',
        ]);

        $search        = $request->input('search');
        $provinceAddr  = $request->input('province_addr');
        $zone          = $request->input('zone');
        $hawza         = $request->input('hawza');
        $eventType     = $request->input('event_type');
        $eventSpecific = $request->input('event_specific_type');
        $targetPerson  = $request->input('target_person');
        $criminal      = $request->input('criminal');
        $officeEmpId   = $request->input('office_employee_id');
        $resourceId    = $request->input('resource_id');

        $period        = strtolower($request->input('period', 'all'));
        $fromDateInput = $request->input('from_date');
        $toDateInput   = $request->input('to_date');

        // ---------------------------------------------------------
        // ⏱️ Resolve date range
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

        // ---------------------------------------------------------
        // 🧮 Base query with eager loading and filters
        // ---------------------------------------------------------
        $query = Event::query()
            ->with([
                'resource:id,name,code_number,phone_number',
                'officeEmployee:id,name,father_name,code_number,phone_number,Province_addr,zone,hawza',
            ])
            ->when($search, function ($q) use ($search) {
                $q->where(function ($qq) use ($search) {
                    $qq->where('tafseel', 'like', "%{$search}%")
                        ->orWhere('event_specific_place', 'like', "%{$search}%")
                        ->orWhere('target_person', 'like', "%{$search}%")
                        ->orWhere('criminal', 'like', "%{$search}%")
                        ->orWhere('note', 'like', "%{$search}%");
                });
            })
            ->when($provinceAddr, fn ($q) => $q->where('Province_addr', $provinceAddr))
            ->when($zone, fn ($q) => $q->where('event_zone', $zone))
            ->when($hawza, fn ($q) => $q->where('event_hawza', $hawza))
            ->when($eventType, fn ($q) => $q->where('event_type', $eventType))
            ->when($eventSpecific, fn ($q) => $q->where('event_specific_type', $eventSpecific))
            ->when($targetPerson, fn ($q) => $q->where('target_person', 'like', "%{$targetPerson}%"))
            ->when($officeEmpId, fn ($q) => $q->where('office_employee_id', $officeEmpId))
            ->when($resourceId, fn ($q) => $q->where('resource_id', $resourceId))
            ->when($criminal !== null && $criminal !== '',
                function ($q) use ($criminal) {
                    if (in_array(strtolower((string) $criminal), ['1', 'true', 'yes', 'بلی', 'هو'], true)) {
                        $q->whereNotNull('criminal')->where('criminal', '!=', '');
                    } elseif (in_array(strtolower((string) $criminal), ['0', 'false', 'no', 'نه'], true)) {
                        $q->where(function ($qq) {
                            $qq->whereNull('criminal')->orWhere('criminal', '');
                        });
                    }
                }
            )
            ->when($dateFrom && $dateTo, function ($q) use ($dateFrom, $dateTo) {
                $q->whereBetween('event_date', [$dateFrom, $dateTo]);
            })
            ->orderBy('Province_addr')
            ->orderByDesc('event_date');

        // For reports we take ALL; frontend can group visually
        $events = $query->get();

        $now = Carbon::now();

        // Global totals
        $globalTotals = [
            'events'            => $events->count(),
            'with_criminal'     => $events->filter(function (Event $e) {
                return !empty($e->criminal);
            })->count(),
            'with_talafat_jani' => $events->filter(function (Event $e) {
                return !empty($e->talafat_jani);
            })->count(),
            'with_talafat_mali' => $events->filter(function (Event $e) {
                return !empty($e->talafat_mali);
            })->count(),
        ];

        // Group by Province_addr
        $grouped = $events->groupBy('Province_addr');

        $data = $grouped->map(function ($group, $province) use ($now) {
            /** @var \Illuminate\Support\Collection $group */
            $province = $province ?: '---';

            $totalEvents       = $group->count();
            $withCriminal      = $group->filter(fn (Event $e) => !empty($e->criminal))->count();
            $withTalafatJani   = $group->filter(fn (Event $e) => !empty($e->talafat_jani))->count();
            $withTalafatMali   = $group->filter(fn (Event $e) => !empty($e->talafat_mali))->count();

            return [
                'province_addr' => $province,
                'totals'        => [
                    'events'            => $totalEvents,
                    'with_criminal'     => $withCriminal,
                    'with_talafat_jani' => $withTalafatJani,
                    'with_talafat_mali' => $withTalafatMali,
                ],
                'events' => $group->map(function (Event $e) use ($now) {
                    $daysSince = $e->event_date
                        ? Carbon::parse($e->event_date)->diffInDays($now)
                        : null;

                    return [
                        'id'                  => $e->id,
                        'event_date'          => $e->event_date,
                        'event_hour'          => $e->event_hour,
                        'event_type'          => $e->event_type,
                        'event_specific_type' => $e->event_specific_type,
                        'target_person'       => $e->target_person,
                        'criminal'            => $e->criminal,
                        'event_zone'          => $e->event_zone,
                        'event_hawza'         => $e->event_hawza,
                        'Province_addr'       => $e->Province_addr,
                        'event_specific_place'=> $e->event_specific_place,
                        'talafat_jani'        => $e->talafat_jani,
                        'talafat_mali'        => $e->talafat_mali,
                        'tafseel'             => $e->tafseel,
                        'talks'               => $e->talks,
                        'coordinates'         => $e->coordinates,
                        'note'                => $e->note,

                        'has_talafat_jani'    => !empty($e->talafat_jani),
                        'has_talafat_mali'    => !empty($e->talafat_mali),
                        'days_since_event'    => $daysSince,

                        'resource'            => $e->resource ? [
                            'id'           => $e->resource->id,
                            'name'         => $e->resource->name,
                            'code_number'  => $e->resource->code_number,
                            'phone_number' => $e->resource->phone_number,
                        ] : null,

                        'office_employee'     => $e->officeEmployee ? [
                            'id'           => $e->officeEmployee->id,
                            'name'         => $e->officeEmployee->name,
                            'father_name'  => $e->officeEmployee->father_name,
                            'code_number'  => $e->officeEmployee->code_number,
                            'phone_number' => $e->officeEmployee->phone_number,
                            'province'     => $e->officeEmployee->Province_addr,
                            'zone'         => $e->officeEmployee->zone,
                            'hawza'        => $e->officeEmployee->hawza,
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
                    'search'              => $search,
                    'province_addr'       => $provinceAddr,
                    'zone'                => $zone,
                    'hawza'               => $hawza,
                    'event_type'          => $eventType,
                    'event_specific_type' => $eventSpecific,
                    'target_person'       => $targetPerson,
                    'criminal'            => $criminal,
                    'office_employee_id'  => $officeEmpId,
                    'resource_id'         => $resourceId,
                ],
            ],
            'data' => $data,
        ]);
    }

    // -------------------------------------------------------------
    // 🔍 Single event (for detail view if you need it)
    // GET /events-report/{id}
    // -------------------------------------------------------------
    public function show($id)
    {
        $event = Event::with([
            'resource:id,name,code_number,phone_number',
            'officeEmployee:id,name,father_name,code_number,phone_number,Province_addr,zone,hawza',
            'documents',
        ])->findOrFail($id);

        return response()->json([
            'data' => $this->mapEventRow($event),
        ]);
    }
}
