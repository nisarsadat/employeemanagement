<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;
use App\Http\Requests\StoreEventRequest;
use App\Http\Requests\UpdateEventRequest;
use App\Http\Resources\EventResource;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use App\Services\NotificationService;

class EventController extends Controller
{
    // =========================
    // ✅ DUPLICATE HELPERS
    // =========================
    private function isDuplicateKey(QueryException $e): bool
    {
        return (int) ($e->errorInfo[1] ?? 0) === 1062;
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

    // =========================
    // ✅ DATE FILTER HELPERS
    // =========================
    private function resolveDateField(?string $dateField): string
    {
        $allowed = ['event_date', 'created_at'];

        $field = trim((string) $dateField);
        if ($field === '' || !in_array($field, $allowed, true)) {
            return 'event_date';
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
        return Event::query()->with([
            'resource:id,code_number',
            'officeEmployee:id,code_number',
            'akhistonkyEmployee:id,code_number',
            'documents',
            'account:id,name,price',
            'finances',
        ]);
    }

    /**
     * ✅ NEW: Return available years/months based on REAL DB data.
     *
     * Query params:
     * - date_field (event_date|created_at) default event_date
     * - year (optional) if provided, returns available months for that year too
     *
     * Response:
     * {
     *   "date_field": "event_date",
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

        $base = Event::query()->whereNotNull($dateField);

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
            'year'       => 'nullable|integer|min:1900|max:2500',
            'month'      => 'nullable|integer|min:1|max:12',
            'date_field' => 'nullable|string',
        ]);

        $search     = $request->input('search');
        $rawPerPage = $request->input('perPage', null);
        $page       = (int) $request->input('page', 1);

        $year      = $request->filled('year') ? (int) $request->input('year') : null;
        $month     = $request->filled('month') ? (int) $request->input('month') : null;
        $dateField = $this->resolveDateField($request->input('date_field'));

        $query = $this->baseListQuery()
            ->when($search, function ($q) use ($search) {
                $s = trim((string) $search);
                $q->where(function ($qq) use ($s) {
                    $qq->where('event_type', 'like', "%{$s}%")
                        ->orWhere('event_specific_type', 'like', "%{$s}%")
                        ->orWhere('target_person', 'like', "%{$s}%")
                        ->orWhere('criminal', 'like', "%{$s}%")
                        ->orWhere('event_zone', 'like', "%{$s}%")
                        ->orWhere('event_hawza', 'like', "%{$s}%")
                        ->orWhere('event_specific_place', 'like', "%{$s}%");
                });
            });

        $this->applyMonthYearFilter($query, $year, $month, $dateField);

        $query->orderByDesc('id');

        if (is_null($rawPerPage) || $rawPerPage === '' || strtolower((string) $rawPerPage) === 'all' || (int) $rawPerPage === 0) {
            return EventResource::collection($query->get());
        }

        return EventResource::collection(
            $query->paginate(max(1, (int) $rawPerPage), ['*'], 'page', $page)
        );
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
                        title: 'رویداد جدید ثبت شد',
                        message: 'نوع رویداد: ' . ($event->event_type ?? ''),
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
                        title: 'رویداد اصلاح شد',
                        message: 'نوع رویداد: ' . ($event->event_type ?? ''),
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
                    title: 'رویداد حذف شد',
                    message: 'نوع رویداد: ' . ($event->event_type ?? ''),
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
