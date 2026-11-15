<?php

namespace App\Http\Controllers;

use App\Exports\MeetingsExport;
use App\Http\Requests\StoreMeetingRequest;
use App\Http\Requests\UpdateMeetingRequest;
use App\Http\Resources\MeetingResource;
use App\Models\Meeting;
use App\Models\MeetingDayPoint;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class MeetingController extends Controller
{
    private function searchableColumns(): array
    {
        return [
            'meeting_title',
            'organ',
            'province',
            'district',
            'specific_place',
            'coordinates',
            'start_date',
            'end_date',
            'total_time',
            'participant',
            'boss_participant',
            'office_employee_count',
            'leader_name',
            'done_actions',
            'mashkok_situations',
            'problems',
            'meeting_supplier',
            'meeting_supplier_phone',
            'meeting_amniyat_responsable',
            'meeting_amniyat_responsable_phone',
            'amniyat_mansobine_count',
            'note',
            'message_to_finance',
            'money_amount_words',
            'finance_status',
        ];
    }

    private function resolveSearchField(?string $field): ?string
    {
        $field = trim((string) $field);

        return in_array(
            $field,
            $this->searchableColumns(),
            true
        )
            ? $field
            : null;
    }

    private function resolveDateField(?string $field): string
    {
        return in_array(
            $field,
            [
                'start_date',
                'end_date',
                'created_at',
                'updated_at',
            ],
            true
        )
            ? $field
            : 'start_date';
    }

    private function baseListQuery(): Builder
    {
        return Meeting::query()
            ->with('documents')
            ->with('officeEmployees:id,name,code_number')
            ->with('account:id,name,price');
    }

    private function applySearch(
        Builder $query,
        ?string $search,
        ?string $searchField = null,
        ?string $searchValue = null
    ): Builder {
        $search = trim((string) $search);
        $searchValue = trim((string) $searchValue);

        $resolvedField =
            $this->resolveSearchField($searchField);

        if ($resolvedField) {
            $value =
                $searchValue !== ''
                    ? $searchValue
                    : $search;

            if ($value !== '') {
                $query->where(
                    $resolvedField,
                    'like',
                    '%' . $value . '%'
                );
            }

            return $query;
        }

        if ($search !== '') {
            $query->where(
                function (Builder $sub) use ($search) {
                    foreach (
                        $this->searchableColumns()
                        as $index => $column
                    ) {
                        if ($index === 0) {
                            $sub->where(
                                $column,
                                'like',
                                '%' . $search . '%'
                            );
                        } else {
                            $sub->orWhere(
                                $column,
                                'like',
                                '%' . $search . '%'
                            );
                        }
                    }
                }
            );
        }

        return $query;
    }

    private function applyDateFilters(
        Builder $query,
        Request $request
    ): Builder {
        $dateField =
            $this->resolveDateField(
                $request->input('date_field')
            );

        $date = $request->input('date');
        $dateFrom =
            $request->input('date_from')
            ?? $request->input('from_date');

        $dateTo =
            $request->input('date_to')
            ?? $request->input('to_date');

        $year = $request->input('year');
        $month = $request->input('month');

        if ($date) {
            $query->whereDate(
                $dateField,
                $date
            );
        }

        if ($dateFrom) {
            $query->whereDate(
                $dateField,
                '>=',
                $dateFrom
            );
        }

        if ($dateTo) {
            $query->whereDate(
                $dateField,
                '<=',
                $dateTo
            );
        }

        if (is_numeric($year)) {
            $query->whereYear(
                $dateField,
                (int) $year
            );
        }

        if (
            is_numeric($month) &&
            (int) $month >= 1 &&
            (int) $month <= 12
        ) {
            $query->whereMonth(
                $dateField,
                (int) $month
            );
        }

        return $query;
    }

    private function applyStatFilter(
        Builder $query,
        string $stat
    ): Builder {
        $today = now()->toDateString();

        if ($stat === 'today') {
            $query->whereDate(
                'start_date',
                $today
            );
        }

        if ($stat === 'upcoming') {
            $query->whereDate(
                'start_date',
                '>',
                $today
            );
        }

        if ($stat === 'ongoing') {
            $query
                ->whereDate(
                    'start_date',
                    '<=',
                    $today
                )
                ->whereDate(
                    'end_date',
                    '>=',
                    $today
                );
        }

        if ($stat === 'completed') {
            $query->whereDate(
                'end_date',
                '<',
                $today
            );
        }

        if ($stat === 'suspicious') {
            $query->whereRaw("CHAR_LENGTH(TRIM(COALESCE(mashkok_situations, ''))) > 0");
        }

        if ($stat === 'problems') {
            $query->whereRaw("CHAR_LENGTH(TRIM(COALESCE(problems, ''))) > 0");
        }

        return $query;
    }

    private function applyFinanceQueueFilter(
        Builder $query,
        bool $onlyFinanceQueue = false
    ): Builder {
        if (!$onlyFinanceQueue) {
            return $query;
        }

        $query
            ->whereNotNull('message_to_finance')
            ->whereRaw("CHAR_LENGTH(TRIM(message_to_finance)) > 0")
            ->where(function ($q) {
                $q->whereNull('finance_status')
                    ->orWhereIn(DB::raw('LOWER(TRIM(finance_status))'), [
                        '',
                        'unpaid',
                        'pending',
                        'ناپرداخت',
                        'not_paid',
                    ]);
            });

        return $query;
    }

    private function buildCardCounts(
        ?string $search,
        ?string $searchField,
        ?string $searchValue,
        $officeEmployeeId = null
    ): array {
        $today = now()->toDateString();

        $base = Meeting::query();

        $this->applySearch(
            $base,
            $search,
            $searchField,
            $searchValue
        );

        if ($officeEmployeeId) {
            $base->whereHas(
                'officeEmployees',
                fn ($q) => $q->where(
                    'office_employees.id',
                    (int) $officeEmployeeId
                )
            );
        }

        $row = (clone $base)->selectRaw("
            COUNT(*) as total,
            SUM(CASE WHEN DATE(start_date) = ? THEN 1 ELSE 0 END) as today,
            SUM(CASE WHEN DATE(start_date) > ? THEN 1 ELSE 0 END) as upcoming,
            SUM(CASE WHEN DATE(start_date) <= ? AND DATE(end_date) >= ? THEN 1 ELSE 0 END) as ongoing,
            SUM(CASE WHEN DATE(end_date) < ? THEN 1 ELSE 0 END) as completed,
            SUM(CASE WHEN CHAR_LENGTH(TRIM(COALESCE(mashkok_situations, ''))) > 0 THEN 1 ELSE 0 END) as suspicious,
            SUM(CASE WHEN CHAR_LENGTH(TRIM(COALESCE(problems, ''))) > 0 THEN 1 ELSE 0 END) as problems
        ", [$today, $today, $today, $today, $today])->first();

        return [
            'total' => (int) ($row->total ?? 0),
            'today' => (int) ($row->today ?? 0),
            'upcoming' => (int) ($row->upcoming ?? 0),
            'ongoing' => (int) ($row->ongoing ?? 0),
            'completed' => (int) ($row->completed ?? 0),
            'suspicious' => (int) ($row->suspicious ?? 0),
            'problems' => (int) ($row->problems ?? 0),
        ];
    }

    public function index(Request $request)
    {
        $request->validate([
            'page' =>
                'sometimes|integer|min:1',

            'perPage' =>
                'sometimes',

            'search' =>
                'nullable|string',

            'search_field' =>
                'nullable|string',

            'search_value' =>
                'nullable|string',

            'stat' =>
                'nullable|string|in:all,today,upcoming,ongoing,completed,suspicious,problems',

            'year' =>
                'nullable|integer|min:1900|max:2500',

            'month' =>
                'nullable|integer|min:1|max:12',

            'date_field' =>
                'nullable|string|in:start_date,end_date,created_at,updated_at',

            'date' =>
                'nullable|date',

            'date_from' =>
                'nullable|date',

            'date_to' =>
                'nullable|date',

            'from_date' =>
                'nullable|date',

            'to_date' =>
                'nullable|date',

            'only_finance_queue' =>
                'nullable|boolean',

            'office_employee_id' =>
                'nullable|integer|exists:office_employees,id',
        ]);

        $search =
            $request->input('search');

        $searchField =
            $request->input('search_field');

        $searchValue =
            $request->input('search_value');

        $stat =
            $request->input(
                'stat',
                'all'
            );

        $officeEmployeeId =
            $request->input('office_employee_id');

        $page =
            (int) $request->input(
                'page',
                1
            );

        $rawPerPage =
            $request->input(
                'perPage'
            );

        $query =
            $this->baseListQuery();

        $this->applySearch(
            $query,
            $search,
            $searchField,
            $searchValue
        );

        $this->applyDateFilters(
            $query,
            $request
        );

        $this->applyStatFilter(
            $query,
            $stat
        );

        $this->applyFinanceQueueFilter(
            $query,
            $request->boolean('only_finance_queue')
        );

        if ($officeEmployeeId) {
            $query->whereHas(
                'officeEmployees',
                fn ($q) => $q->where(
                    'office_employees.id',
                    (int) $officeEmployeeId
                )
            );
        }

        $query->latest('id');

        $cardCounts =
            $this->buildCardCounts(
                $search,
                $searchField,
                $searchValue,
                $officeEmployeeId
            );

        if (
            is_null($rawPerPage) ||
            $rawPerPage === '' ||
            strtolower(
                (string) $rawPerPage
            ) === 'all' ||
            (int) $rawPerPage === 0
        ) {
            return MeetingResource::collection(
                $query->get()
            )->additional([
                'card_counts' =>
                    $cardCounts,
            ]);
        }

        return MeetingResource::collection(
            $query->paginate(
                max(
                    1,
                    (int) $rawPerPage
                ),
                ['*'],
                'page',
                $page
            )
        )->additional([
            'card_counts' =>
                $cardCounts,
        ]);
    }

    public function filterOptions(
        Request $request
    ) {
        $field =
            $this->resolveSearchField(
                $request->query('field')
            );

        $q =
            trim(
                (string) $request->query(
                    'q',
                    ''
                )
            );

        if (!$field) {
            return response()->json([
                'data' => [
                    'options' => [],
                ],
            ]);
        }

        $query =
            Meeting::query()
                ->select($field)
                ->whereNotNull($field)
                ->where(
                    $field,
                    '!=',
                    ''
                );

        if ($q !== '') {
            $query->where(
                $field,
                'like',
                '%' . $q . '%'
            );
        }

        $options =
            $query
                ->distinct()
                ->orderBy($field)
                ->limit(200)
                ->pluck($field)
                ->filter(
                    fn ($value) =>
                        $value !== null &&
                        trim(
                            (string) $value
                        ) !== ''
                )
                ->values()
                ->map(
                    fn ($value) => [
                        'title' =>
                            (string) $value,

                        'value' =>
                            (string) $value,
                    ]
                );

        return response()->json([
            'data' => [
                'options' =>
                    $options,
            ],
        ]);
    }

    public function dateOptions(
        Request $request
    ) {
        $dateField =
            $this->resolveDateField(
                $request->query(
                    'date_field',
                    'start_date'
                )
            );

        $years =
            Meeting::query()
                ->whereNotNull($dateField)
                ->selectRaw(
                    "YEAR({$dateField}) as year"
                )
                ->distinct()
                ->orderByDesc('year')
                ->pluck('year')
                ->filter()
                ->values();

        return response()->json([
            'data' => [
                'years' => $years,
                'months' => [
                    1,
                    2,
                    3,
                    4,
                    5,
                    6,
                    7,
                    8,
                    9,
                    10,
                    11,
                    12,
                ],
            ],
        ]);
    }

    public function show($id)
    {
        $meeting =
            $this->baseListQuery()
                ->findOrFail($id);

        return new MeetingResource(
            $meeting
        );
    }

    public function store(
        StoreMeetingRequest $request
    ) {
        DB::beginTransaction();

        try {
            $data =
                $request->validated();

            $employeeIds =
                $data['office_employee_ids']
                ?? [];

            unset(
                $data['documents'],
                $data['office_employee_ids']
            );

            $meeting =
                Meeting::create($data);

            // ✅ ترسره کوونکي
            $meeting
                ->officeEmployees()
                ->sync($employeeIds);

            $this->awardDayPoints($meeting, $employeeIds);

            if (
                $request->hasFile(
                    'documents'
                )
            ) {
                foreach (
                    $request->file(
                        'documents'
                    )
                    as $file
                ) {
                    $path =
                        $file->store(
                            "meetings/{$meeting->id}",
                            'public'
                        );

                    $meeting
                        ->documents()
                        ->create([
                            'file_name' =>
                                $file
                                    ->getClientOriginalName(),

                            'file_path' =>
                                $path,

                            'mime_type' =>
                                $file
                                    ->getClientMimeType(),

                            'size_bytes' =>
                                $file->getSize(),
                        ]);
                }
            }

            NotificationService::recordForAllUsers(
                actor: $request->user(),
                module: 'جلسات',
                action: 'created',
                title: 'جلسه جدید ثبت شد',
                message:
                    'جلسه: ' .
                    $meeting->meeting_title,
                routeName: 'meetings',
                routeParams: [
                    'id' =>
                        $meeting->id,
                ],
                level: 'success',
                changes: null,
            );

            DB::commit();

            return new MeetingResource(
                $meeting->load(
                    'documents'
                )
            );
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'message' =>
                    'Meeting creation failed',

                'error' =>
                    $e->getMessage(),
            ], 500);
        }
    }

    public function update(
        UpdateMeetingRequest $request,
        $id
    ) {
        DB::beginTransaction();

        try {
            $meeting =
                Meeting::with(
                    'documents'
                )->findOrFail($id);

            $original =
                $meeting->getOriginal();

            $data =
                $request->validated();

            // Empty selection = clear all (the form always sets the full list).
            $employeeIds =
                $data['office_employee_ids']
                ?? [];

            unset(
                $data['documents'],
                $data[
                    'removed_document_ids'
                ],
                $data['office_employee_ids']
            );

            $meeting->fill($data);

            $dirty =
                $meeting->getDirty();

            $changes = [];

            foreach (
                $dirty
                as $field => $newValue
            ) {
                $changes[$field] = [
                    'old' =>
                        $original[$field]
                        ?? null,

                    'new' =>
                        $newValue,
                ];
            }

            $meeting->save();

            // ✅ ترسره کوونکي
            $meeting
                ->officeEmployees()
                ->sync($employeeIds);

            $this->awardDayPoints($meeting, $employeeIds);

            $removedIds =
                $request->input(
                    'removed_document_ids',
                    []
                );

            if (
                is_array($removedIds) &&
                count($removedIds) > 0
            ) {
                $documents =
                    $meeting
                        ->documents()
                        ->whereIn(
                            'id',
                            $removedIds
                        )
                        ->get();

                foreach (
                    $documents
                    as $document
                ) {
                    $document->delete();
                }

                $changes[
                    'documents_removed'
                ] = [
                    'old' => null,
                    'new' =>
                        $removedIds,
                ];
            }

            if (
                $request->hasFile(
                    'documents'
                )
            ) {
                foreach (
                    $request->file(
                        'documents'
                    )
                    as $file
                ) {
                    $path =
                        $file->store(
                            "meetings/{$meeting->id}",
                            'public'
                        );

                    $meeting
                        ->documents()
                        ->create([
                            'file_name' =>
                                $file
                                    ->getClientOriginalName(),

                            'file_path' =>
                                $path,

                            'mime_type' =>
                                $file
                                    ->getClientMimeType(),

                            'size_bytes' =>
                                $file->getSize(),
                        ]);
                }

                $changes[
                    'documents'
                ] = [
                    'old' => null,
                    'new' => 'updated',
                ];
            }

            NotificationService::recordForAllUsers(
                actor: $request->user(),
                module: 'جلسات',
                action: 'updated',
                title: 'جلسه اصلاح شد',
                message:
                    'جلسه: ' .
                    $meeting->meeting_title,
                routeName: 'meetings',
                routeParams: [
                    'id' =>
                        $meeting->id,
                ],
                level: 'info',
                changes:
                    $changes ?: null,
            );

            DB::commit();

            return new MeetingResource(
                $meeting->load(
                    'documents'
                )
            );
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'message' =>
                    'Meeting update failed',

                'error' =>
                    $e->getMessage(),
            ], 500);
        }
    }

    public function updateFinance(Request $request, $meeting)
    {
        $data = $request->validate([
            'account_id' => ['nullable', 'integer', 'exists:accounts,id'],
            'message_to_finance' => ['nullable', 'string', 'max:255'],
            'money_amount_words' => ['nullable', 'string', 'max:500'],
        ]);

        $meeting = Meeting::with([
            'documents',
            'officeEmployees:id,name,code_number',
            'account:id,name,price',
        ])->findOrFail($meeting);

        $meeting->fill($data);

        if (blank($meeting->finance_status)) {
            $meeting->finance_status = 'pending';
        }

        $meeting->save();

        return new MeetingResource(
            $meeting->load([
                'documents',
                'officeEmployees:id,name,code_number',
                'account:id,name,price',
            ])
        );
    }

    public function destroy(
        Request $request,
        $id
    ) {
        DB::beginTransaction();

        try {
            $meeting =
                Meeting::with(
                    'documents'
                )->findOrFail($id);

            $title =
                $meeting->meeting_title;

            foreach (
                $meeting->documents
                as $document
            ) {
                $document->delete();
            }

            $meeting->delete();

            NotificationService::recordForAllUsers(
                actor: $request->user(),
                module: 'جلسات',
                action: 'deleted',
                title: 'جلسه حذف شد',
                message:
                    'جلسه: ' .
                    $title,
                routeName: null,
                routeParams: null,
                level: 'warning',
                changes: null,
            );

            DB::commit();

            return response()->json([
                'message' =>
                    'Meeting deleted successfully',
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'message' =>
                    'Meeting delete failed',

                'error' =>
                    $e->getMessage(),
            ], 500);
        }
    }

    public function exportExcel(
        Request $request
    ) {
        $request->validate([
            'search' =>
                'nullable|string',

            'search_field' =>
                'nullable|string',

            'search_value' =>
                'nullable|string',

            'stat' =>
                'nullable|string|in:all,today,upcoming,ongoing,completed,suspicious,problems',

            'year' =>
                'nullable|integer',

            'month' =>
                'nullable|integer|min:1|max:12',

            'date_field' =>
                'nullable|string|in:start_date,end_date,created_at,updated_at',

            'date' =>
                'nullable|date',

            'date_from' =>
                'nullable|date',

            'date_to' =>
                'nullable|date',

            'from_date' =>
                'nullable|date',

            'to_date' =>
                'nullable|date',

            'locale' =>
                'nullable|string',

            'column_labels' =>
                'nullable|array',
        ]);

        return Excel::download(
            new MeetingsExport(
                search:
                    $request->input(
                        'search'
                    ),

                searchField:
                    $request->input(
                        'search_field'
                    ),

                searchValue:
                    $request->input(
                        'search_value'
                    ),

                stat:
                    $request->input(
                        'stat',
                        'all'
                    ),

                year:
                    $request->input(
                        'year'
                    ),

                month:
                    $request->input(
                        'month'
                    ),

                dateField:
                    $request->input(
                        'date_field',
                        'start_date'
                    ),

                date:
                    $request->input(
                        'date'
                    ),

                dateFrom:
                    $request->input(
                        'date_from'
                    )
                    ?? $request->input(
                        'from_date'
                    ),

                dateTo:
                    $request->input(
                        'date_to'
                    )
                    ?? $request->input(
                        'to_date'
                    ),

                locale:
                    $request->input(
                        'locale',
                        'fa'
                    ),

                columnLabels:
                    (array) $request->input(
                        'column_labels',
                        []
                    ),
            ),
            'meetings.xlsx'
        );
    }

    public function printView(
        Request $request
    ) {
        $query =
            $this->baseListQuery();

        $this->applySearch(
            $query,
            $request->input('search'),
            $request->input(
                'search_field'
            ),
            $request->input(
                'search_value'
            )
        );

        $this->applyDateFilters(
            $query,
            $request
        );

        $this->applyStatFilter(
            $query,
            $request->input(
                'stat',
                'all'
            )
        );

        $rows =
            $query
                ->latest('id')
                ->get()
                ->map(
                    function ($item) {
                        if (
                            $item->start_date
                        ) {
                            $item->start_date =
                                $this
                                    ->formatJalaliDate(
                                        $item
                                            ->start_date
                                    );
                        }

                        if (
                            $item->end_date
                        ) {
                            $item->end_date =
                                $this
                                    ->formatJalaliDate(
                                        $item
                                            ->end_date
                                    );
                        }

                        return $item;
                    }
                );

        return response()->view(
            'prints.table-print',
            [
                'title' =>
                    $request->input(
                        'title',
                        'چاپ جلسات'
                    ),

                'rows' =>
                    $rows,

                'selectedColumns' =>
                    $request->input(
                        'selected_columns',
                        []
                    ),

                'columnLabels' =>
                    $request->input(
                        'column_labels',
                        []
                    ),

                'printLabels' =>
                    $request->input(
                        'print_labels',
                        []
                    ),

                'locale' =>
                    $request->input(
                        'locale',
                        'fa'
                    ),
            ]
        );
    }

    private function formatJalaliDate(
        $value
    ): string {
        if (!$value) {
            return '';
        }

        try {
            $date =
                $value instanceof Carbon
                    ? $value
                    : Carbon::parse(
                        $value
                    );

            [
                $jy,
                $jm,
                $jd
            ] =
                $this
                    ->gregorianToJalali(
                        (int) $date->format(
                            'Y'
                        ),
                        (int) $date->format(
                            'm'
                        ),
                        (int) $date->format(
                            'd'
                        )
                    );

            return sprintf(
                '%04d/%02d/%02d',
                $jy,
                $jm,
                $jd
            );
        } catch (\Throwable $e) {
            return (string) $value;
        }
    }

    private function gregorianToJalali(
        int $gy,
        int $gm,
        int $gd
    ): array {
        $gdm = [
            0,
            31,
            59,
            90,
            120,
            151,
            181,
            212,
            243,
            273,
            304,
            334,
        ];

        if ($gy > 1600) {
            $jy = 979;
            $gy -= 1600;
        } else {
            $jy = 0;
            $gy -= 621;
        }

        $gy2 =
            $gm > 2
                ? $gy + 1
                : $gy;

        $days =
            365 * $gy
            + intdiv(
                $gy2 + 3,
                4
            )
            - intdiv(
                $gy2 + 99,
                100
            )
            + intdiv(
                $gy2 + 399,
                400
            )
            - 80
            + $gd
            + $gdm[
                $gm - 1
            ];

        $jy +=
            33 *
            intdiv(
                $days,
                12053
            );

        $days %= 12053;

        $jy +=
            4 *
            intdiv(
                $days,
                1461
            );

        $days %= 1461;

        if ($days > 365) {
            $jy +=
                intdiv(
                    $days - 1,
                    365
                );

            $days =
                ($days - 1)
                % 365;
        }

        if ($days < 186) {
            $jm =
                1 +
                intdiv(
                    $days,
                    31
                );

            $jd =
                1 +
                ($days % 31);
        } else {
            $jm =
                7 +
                intdiv(
                    $days - 186,
                    30
                );

            $jd =
                1 +
                (
                    ($days - 186)
                    % 30
                );
        }

        return [
            $jy,
            $jm,
            $jd,
        ];
    }

    /**
     * One point per employee per calendar day. A later meeting on the same day does not add another point.
     */
    private function awardDayPoints(Meeting $meeting, array $employeeIds): void
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $employeeIds))));

        if ($ids === [] || blank($meeting->start_date)) {
            return;
        }

        $day = Carbon::parse($meeting->start_date)->toDateString();

        foreach ($ids as $employeeId) {
            MeetingDayPoint::query()->firstOrCreate(
                [
                    'office_employee_id' => $employeeId,
                    'point_date' => $day,
                ],
                ['points' => 1]
            );
        }
    }
}