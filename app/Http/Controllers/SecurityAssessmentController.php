<?php

namespace App\Http\Controllers;

use App\Exports\SecurityAssessmentsExport;
use App\Http\Requests\StoreSecurityAssessmentRequest;
use App\Http\Requests\UpdateSecurityAssessmentRequest;
use App\Http\Resources\SecurityAssessmentResource;
use App\Models\SecurityAssessment;
use App\Services\NotificationService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class SecurityAssessmentController extends Controller
{
    private function searchableColumns(): array
    {
        return [
            'place_name',
            'responsible_owner_name',
            'province',
            'district',
            'specific_place',
            'coordinates',
            'north',
            'south',
            'east',
            'west',
            'daily_visitors_count',
            'risk_places',
            'vulnerable_places',
            'security_gaps',
            'weak_points',
            'security_incidents',
            'entrance_gate',
            'exit_gate',
            'emergency_gate',
            'secondary_gate',
            'physical_check_status',
            'technical_check_status',
            'security_cameras',
            'guards_count',
            'active_towers_count',
            'active_guard_posts_count',
            'security_responsible_name',
            'security_responsible_phone',
            'other_problems',
            'note',
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

    private function baseListQuery(): Builder
    {
        return SecurityAssessment::query()
            ->with('officeEmployees:id,name,code_number');
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

    private function buildCardCounts(
        ?string $search,
        ?string $searchField,
        ?string $searchValue
    ): array {
        $base = SecurityAssessment::query();

        $this->applySearch(
            $base,
            $search,
            $searchField,
            $searchValue
        );

        return [
            'total' =>
                (clone $base)->count(),
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

            'year' =>
                'nullable|integer|min:1900|max:2500',

            'month' =>
                'nullable|integer|min:1|max:12',

            'date_field' =>
                'nullable|string|in:created_at,updated_at',

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
        ]);

        $search =
            $request->input('search');

        $searchField =
            $request->input('search_field');

        $searchValue =
            $request->input('search_value');

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

        if (is_numeric($request->input('year'))) {
            $query->whereYear(
                'created_at',
                (int) $request->input('year')
            );
        }

        if (
            is_numeric($request->input('month')) &&
            (int) $request->input('month') >= 1 &&
            (int) $request->input('month') <= 12
        ) {
            $query->whereMonth(
                'created_at',
                (int) $request->input('month')
            );
        }

        if ($request->input('date')) {
            $query->whereDate(
                'created_at',
                $request->input('date')
            );
        }

        if ($request->input('date_from') ?? $request->input('from_date')) {
            $query->whereDate(
                'created_at',
                '>=',
                $request->input('date_from')
                    ?? $request->input('from_date')
            );
        }

        if ($request->input('date_to') ?? $request->input('to_date')) {
            $query->whereDate(
                'created_at',
                '<=',
                $request->input('date_to')
                    ?? $request->input('to_date')
            );
        }

        $query->latest('id');

        $cardCounts =
            $this->buildCardCounts(
                $search,
                $searchField,
                $searchValue
            );

        if (
            is_null($rawPerPage) ||
            $rawPerPage === '' ||
            strtolower(
                (string) $rawPerPage
            ) === 'all' ||
            (int) $rawPerPage === 0
        ) {
            return SecurityAssessmentResource::collection(
                $query->get()
            )->additional([
                'card_counts' =>
                    $cardCounts,
            ]);
        }

        return SecurityAssessmentResource::collection(
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
            SecurityAssessment::query()
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

    public function show($id)
    {
        $assessment =
            $this->baseListQuery()
                ->findOrFail($id);

        return new SecurityAssessmentResource(
            $assessment
        );
    }

    public function store(
        StoreSecurityAssessmentRequest $request
    ) {
        DB::beginTransaction();

        try {
            $data =
                $request->validated();

            $employeeIds =
                $data['office_employee_ids']
                ?? [];

            unset(
                $data['office_employee_ids']
            );

            $assessment =
                SecurityAssessment::create($data);

            // ✅ ترسره کوونکي
            $assessment
                ->officeEmployees()
                ->sync($employeeIds);

            NotificationService::recordForAllUsers(
                actor: $request->user(),
                module: 'ارزیابی امنیتی',
                action: 'created',
                title: 'ارزیابی امنیتی جدید ثبت شد',
                message:
                    'ارزیابی امنیتی: ' .
                    $assessment->place_name,
                routeName: 'security-assessments',
                routeParams: [
                    'id' =>
                        $assessment->id,
                ],
                level: 'success',
                changes: null,
            );

            DB::commit();

            return new SecurityAssessmentResource(
                $assessment
            );
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'message' =>
                    'Security assessment creation failed',

                'error' =>
                    $e->getMessage(),
            ], 500);
        }
    }

    public function update(
        UpdateSecurityAssessmentRequest $request,
        $id
    ) {
        DB::beginTransaction();

        try {
            $assessment =
                SecurityAssessment::findOrFail($id);

            $original =
                $assessment->getOriginal();

            $data =
                $request->validated();

            // Empty selection = clear all (the form always sets the full list).
            $employeeIds =
                $data['office_employee_ids']
                ?? [];

            unset(
                $data['office_employee_ids']
            );

            $assessment->fill($data);

            $dirty =
                $assessment->getDirty();

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

            $assessment->save();

            // ✅ ترسره کوونکي
            $assessment
                ->officeEmployees()
                ->sync($employeeIds);

            NotificationService::recordForAllUsers(
                actor: $request->user(),
                module: 'ارزیابی امنیتی',
                action: 'updated',
                title: 'ارزیابی امنیتی اصلاح شد',
                message:
                    'ارزیابی امنیتی: ' .
                    $assessment->place_name,
                routeName: 'security-assessments',
                routeParams: [
                    'id' =>
                        $assessment->id,
                ],
                level: 'info',
                changes:
                    $changes ?: null,
            );

            DB::commit();

            return new SecurityAssessmentResource(
                $assessment
            );
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'message' =>
                    'Security assessment update failed',

                'error' =>
                    $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(
        Request $request,
        $id
    ) {
        DB::beginTransaction();

        try {
            $assessment =
                SecurityAssessment::findOrFail($id);

            $title =
                $assessment->place_name;

            $assessment->delete();

            NotificationService::recordForAllUsers(
                actor: $request->user(),
                module: 'ارزیابی امنیتی',
                action: 'deleted',
                title: 'ارزیابی امنیتی حذف شد',
                message:
                    'ارزیابی امنیتی: ' .
                    $title,
                routeName: null,
                routeParams: null,
                level: 'warning',
                changes: null,
            );

            DB::commit();

            return response()->json([
                'message' =>
                    'Security assessment deleted successfully',
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'message' =>
                    'Security assessment delete failed',

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

            'year' =>
                'nullable|integer',

            'month' =>
                'nullable|integer|min:1|max:12',

            'date_field' =>
                'nullable|string|in:created_at,updated_at',

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
            new SecurityAssessmentsExport(
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
                        'created_at'
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
            'security-assessments.xlsx'
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

        if (is_numeric($request->input('year'))) {
            $query->whereYear(
                'created_at',
                (int) $request->input('year')
            );
        }

        if (
            is_numeric($request->input('month')) &&
            (int) $request->input('month') >= 1 &&
            (int) $request->input('month') <= 12
        ) {
            $query->whereMonth(
                'created_at',
                (int) $request->input('month')
            );
        }

        if ($request->input('date')) {
            $query->whereDate(
                'created_at',
                $request->input('date')
            );
        }

        if ($request->input('date_from') ?? $request->input('from_date')) {
            $query->whereDate(
                'created_at',
                '>=',
                $request->input('date_from')
                    ?? $request->input('from_date')
            );
        }

        if ($request->input('date_to') ?? $request->input('to_date')) {
            $query->whereDate(
                'created_at',
                '<=',
                $request->input('date_to')
                    ?? $request->input('to_date')
            );
        }

        $rows =
            $query
                ->latest('id')
                ->get();

        return response()->view(
            'prints.table-print',
            [
                'title' =>
                    $request->input(
                        'title',
                        'چاپ ارزیابی های امنیتی'
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
}
