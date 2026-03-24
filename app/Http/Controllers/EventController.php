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

    private function applyCommonFilters($query, ?string $search, ?string $cardFilter)
    {
        $query->when($search, function ($q) use ($search) {
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

    private function buildCardCounts(?string $search, ?int $year, ?int $month, string $dateField): array
    {
        $base = Event::query();

        $this->applyCommonFilters($base, $search, null);
        $this->applyMonthYearFilter($base, $year, $month, $dateField);

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
            'page'        => 'sometimes|integer|min:1',
            'perPage'     => 'sometimes',
            'search'      => 'nullable|string',
            'year'        => 'nullable|integer|min:1900|max:2500',
            'month'       => 'nullable|integer|min:1|max:12',
            'date_field'  => 'nullable|string',
            'card_filter' => 'nullable|string',
        ]);

        $search     = $request->input('search');
        $rawPerPage = $request->input('perPage', null);
        $page       = (int) $request->input('page', 1);
        $cardFilter = $request->input('card_filter', 'all');

        $year      = $request->filled('year') ? (int) $request->input('year') : null;
        $month     = $request->filled('month') ? (int) $request->input('month') : null;
        $dateField = $this->resolveDateField($request->input('date_field'));

        $cardCounts = $this->buildCardCounts($search, $year, $month, $dateField);

        $query = $this->baseListQuery();

        $this->applyCommonFilters($query, $search, $cardFilter);
        $this->applyMonthYearFilter($query, $year, $month, $dateField);

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