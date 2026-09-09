<?php

namespace App\Http\Controllers\fifthyFormats;

use App\Exports\ClubsExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\fifthyFormats\StoreClubsRequest;
use App\Http\Requests\fifthyFormats\UpdateClubsRequest;
use App\Http\Resources\fifthyFormats\ClubsResource;
use App\Models\fifthyFormats\Clubs;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\QueryException;
use App\Services\NotificationService; // 🔔 notifications
use Maatwebsite\Excel\Facades\Excel;

class ClubsController extends Controller
{
    public function index(Request $request)
    {
        $page    = $request->query('page');
        $perPage = $request->query('perPage', $request->query('per_page'));

        $isValidPage    = is_numeric($page) && (int) $page > 0;
        $isValidPerPage = is_numeric($perPage) && (int) $perPage > 0;

        $search      = trim((string) $request->query('search', ''));
        $rid         = $request->query('resource_id');
        $searchField = trim((string) $request->query('search_field', ''));
        $searchValue = trim((string) $request->query('search_value', ''));

        $allowedSearchFields = [
           
         'name',
        'club_name','club_type','father_name','position','phone_number',
        'province','zone','district','specific_place','cordinate',
        'am_students','pm_students',
        'picture',
        'Province_addr',
        'notes',
        'resource_id',
        ];

        $sortable = ['id', 'name', 'created_at', 'updated_at'];
        $sortBy   = in_array($request->query('sort_by', 'created_at'), $sortable, true)
            ? $request->query('sort_by', 'created_at')
            : 'created_at';

        $sortDir = strtolower((string) $request->query('sort_dir', 'desc'));
        if (!in_array($sortDir, ['asc', 'desc'], true)) {
            $sortDir = 'desc';
        }

        $q = Clubs::query()
            ->with(['resource:id,name,code_number,phone_number'])
            ->when($search !== '' && $searchField === '', function ($sub) use ($search) {
                $sub->where(function ($x) use ($search) {
                    $x->where('name', 'like', "%{$search}%")
                        ->orWhere('club_name', 'like', "%{$search}%")
                        ->orWhere('club_type', 'like', "%{$search}%")
                        ->orWhere('father_name', 'like', "%{$search}%")
                        ->orWhere('position', 'like', "%{$search}%")
                        ->orWhere('phone_number', 'like', "%{$search}%")
                        ->orWhere('province', 'like', "%{$search}%")
                        ->orWhere('zone', 'like', "%{$search}%")
                        ->orWhere('district', 'like', "%{$search}%")
                        ->orWhere('specific_place', 'like', "%{$search}%");
                });
            })
            ->when(
                $searchField !== '' && in_array($searchField, $allowedSearchFields, true),
                function ($sub) use ($searchField, $searchValue, $search) {
                    $value = $searchValue !== '' ? $searchValue : $search;

                    if ($value !== '') {
                        $sub->where($searchField, 'like', "%{$value}%");
                    }
                }
            )
            ->when($rid, fn ($sub) => $sub->where('resource_id', $rid))
            ->orderBy($sortBy, $sortDir);

        if ($isValidPage && $isValidPerPage) {
            return ClubsResource::collection(
                $q->paginate((int) $perPage, ['*'], 'page', (int) $page)
            );
        }

        return ClubsResource::collection($q->get());
    }

    /**
     * GET /clubs/filter-options
     */
    public function filterOptions(Request $request)
    {
        $field = trim((string) $request->query('field', ''));
        $q = trim((string) $request->query('q', ''));

        $allowedFields = [
           
         'name',
        'club_name','club_type','father_name','position','phone_number',
        'province','zone','district','specific_place','cordinate',
        'am_students','pm_students',
        'picture',
        'Province_addr',
        'notes',
        'resource_id',
        ];

        if ($field === '' || !in_array($field, $allowedFields, true)) {
            return response()->json([
                'data' => [
                    'options' => [],
                ],
            ]);
        }

        $query = Clubs::query()
            ->select($field)
            ->whereNotNull($field)
            ->where($field, '!=', '');

        if ($q !== '') {
            $query->where($field, 'like', "%{$q}%");
        }

        $options = $query
            ->distinct()
            ->pluck($field)
            ->filter(fn ($v) => $v !== null && trim((string) $v) !== '')
            ->values()
            ->map(fn ($v) => [
                'title' => (string) $v,
                'value' => (string) $v,
            ]);

        return response()->json([
            'data' => [
                'options' => $options,
            ],
        ]);
    }

    public function show(Clubs $club)
    {
        $club->load(['resource:id,name,code_number,phone_number']);
        return new ClubsResource($club);
    }

    /**
     * POST /clubs
     * Accepts:
     * - picture as FILE or STRING
     */
    public function store(StoreClubsRequest $request)
    {
        $user = $request->user(); // 🔔
        $data = $request->validated();

        try {
            return DB::transaction(function () use ($request, $data, $user) {

                // ✅ picture: file OR string (same style as SaeenFasad)
                if ($request->hasFile('picture')) {
                    $path = $request->file('picture')->store('uploads/clubs', 'public');
                    $data['picture'] = "storage/{$path}";
                } else {
                    if (array_key_exists('picture', $data) && !is_string($data['picture'])) {
                        unset($data['picture']);
                    }
                }

                $club = Clubs::create($data);
                $club->load(['resource:id,name,code_number,phone_number']);

                // 🔔 Notification: CREATED
                NotificationService::record(
                    $user,
                    module: 'ورزشي کلبونه',
                    action: 'created',
                    title: 'نوی ریکارډ ثبت شو',
                    message: 'نوم: ' . ($club->name ?? ''),
                    routeName: 'clubs', // ⚠️ adjust if your route name differs
                    routeParams: ['id' => $club->id],
                    level: 'success',
                    changes: null
                );

                return (new ClubsResource($club))
                    ->additional(['message' => 'Created'])
                    ->response()
                    ->setStatusCode(201);
            });
        } catch (QueryException $e) {
            if ((int) ($e->errorInfo[1] ?? 0) === 1062) {
                return response()->json([
                    'message' => 'دا ریکارډ له مخکې نه موجود دی (تکراري ثبت اجازه نشته).',
                    'errors'  => [
                        'duplicate' => ['دا ریکارډ له مخکې نه موجود دی (تکراري ثبت اجازه نشته).'],
                    ],
                ], 422);
            }

            throw $e;
        }
    }

    /**
     * PUT /clubs/{club}
     * Accepts:
     * - picture as FILE or STRING (replaces old file if new uploaded)
     */
    public function update(UpdateClubsRequest $request, Clubs $club)
    {
        $user = $request->user(); // 🔔
        $data = $request->validated();

        try {
            return DB::transaction(function () use ($request, $data, $club, $user) {

                // 🧮 changes BEFORE update
                $original = $club->getOriginal();
                $changes  = [];

                foreach ($data as $field => $newValue) {
                    if (array_key_exists($field, $original) && $original[$field] != $newValue) {
                        $changes[$field] = [
                            'old' => $original[$field],
                            'new' => $newValue,
                        ];
                    }
                }

                // ✅ picture: replace file if uploaded
                if ($request->hasFile('picture')) {
                    if ($club->picture && str_starts_with($club->picture, 'storage/')) {
                        $old = str_replace('storage/', '', $club->picture);
                        Storage::disk('public')->delete($old);
                    }

                    $path = $request->file('picture')->store('uploads/clubs', 'public');
                    $data['picture'] = "storage/{$path}";

                    $changes['picture'] = [
                        'old' => $original['picture'] ?? null,
                        'new' => $data['picture'],
                    ];
                } else {
                    if (array_key_exists('picture', $data) && !is_string($data['picture'])) {
                        unset($data['picture']);
                    }
                }

                $club->update($data);
                $club->load(['resource:id,name,code_number,phone_number']);

                // 🔔 Notification: UPDATED
                NotificationService::record(
                    $user,
                    module: 'ورزشي کلبونه',
                    action: 'updated',
                    title: 'ریکارډ اصلاح شو',
                    message: 'نوم: ' . ($club->name ?? ''),
                    routeName: 'clubs',
                    routeParams: ['id' => $club->id],
                    level: 'info',
                    changes: !empty($changes) ? $changes : null
                );

                return new ClubsResource($club);
            });
        } catch (QueryException $e) {
            if ((int) ($e->errorInfo[1] ?? 0) === 1062) {
                return response()->json([
                    'message' => 'دا ریکارډ له مخکې نه موجود دی (تکراري ثبت اجازه نشته).',
                    'errors'  => [
                        'duplicate' => ['دا ریکارډ له مخکې نه موجود دی (تکراري ثبت اجازه نشته).'],
                    ],
                ], 422);
            }

            throw $e;
        }
    }

    public function destroy(Request $request, Clubs $club)
    {
        $user = $request->user(); // 🔔

        return DB::transaction(function () use ($club, $user) {

            $name = $club->name;

            // ✅ delete picture file (same style as SaeenFasad destroy)
            if ($club->picture && str_starts_with($club->picture, 'storage/')) {
                $old = str_replace('storage/', '', $club->picture);
                Storage::disk('public')->delete($old);
            }

            $club->delete();

            // 🔔 Notification: DELETED
            NotificationService::record(
                $user,
                module: 'ورزشي کلبونه',
                action: 'deleted',
                title: 'ریکارډ حذف شو',
                message: 'نوم: ' . ($name ?? ''),
                routeName: null,
                routeParams: [],
                level: 'warning',
                changes: null
            );

            return response()->noContent();
        });
    }
    private function printSearchFields(): array
{
    return [
        'name',
        'club_name',
        'club_type',
        'father_name',
        'position',
        'phone_number',
        'province',
        'zone',
        'district',
        'specific_place',
        'cordinate',
        'am_students',
        'pm_students',
        'picture',
        'Province_addr',
        'notes',
        'resource_id',
        'resource_code',
        'resource_phone_number',
    ];
}

private function applyPrintFilters($query, Request $request)
{
    $search      = trim((string) $request->query('search', ''));
    $searchField = trim((string) $request->query('search_field', ''));
    $searchValue = trim((string) $request->query('search_value', ''));
    $resourceId  = $request->query('resource_id');

    $allowedFields = $this->printSearchFields();

    if ($search !== '' && $searchField === '') {
        $query->where(function ($x) use ($search) {
            $x->where('name', 'like', "%{$search}%")
                ->orWhere('club_name', 'like', "%{$search}%")
                ->orWhere('club_type', 'like', "%{$search}%")
                ->orWhere('father_name', 'like', "%{$search}%")
                ->orWhere('position', 'like', "%{$search}%")
                ->orWhere('phone_number', 'like', "%{$search}%")
                ->orWhere('province', 'like', "%{$search}%")
                ->orWhere('zone', 'like', "%{$search}%")
                ->orWhere('district', 'like', "%{$search}%")
                ->orWhere('specific_place', 'like', "%{$search}%")
                ->orWhere('cordinate', 'like', "%{$search}%")
                ->orWhere('am_students', 'like', "%{$search}%")
                ->orWhere('pm_students', 'like', "%{$search}%")
                ->orWhere('Province_addr', 'like', "%{$search}%")
                ->orWhere('notes', 'like', "%{$search}%")
                ->orWhereHas('resource', function ($r) use ($search) {
                    $r->where('code_number', 'like', "%{$search}%")
                        ->orWhere('phone_number', 'like', "%{$search}%");
                });
        });
    }

    if ($searchField !== '' && in_array($searchField, $allowedFields, true)) {
        $value = $searchValue !== '' ? $searchValue : $search;

        if ($value !== '') {
            if ($searchField === 'resource_code') {
                $query->whereHas('resource', function ($r) use ($value) {
                    $r->where('code_number', 'like', "%{$value}%");
                });
            } elseif ($searchField === 'resource_phone_number') {
                $query->whereHas('resource', function ($r) use ($value) {
                    $r->where('phone_number', 'like', "%{$value}%");
                });
            } else {
                $query->where($searchField, 'like', "%{$value}%");
            }
        }
    }

    if ($resourceId !== null && $resourceId !== '') {
        $query->where('resource_id', $resourceId);
    }

    return $query;
}

public function printView(Request $request)
{
    $query = Clubs::query()
        ->with([
            'resource:id,name,code_number,phone_number',
        ]);

    $this->applyPrintFilters($query, $request);

    $rows = $query
        ->orderByDesc('id')
        ->get()
        ->map(function ($row) {
            $row->resource_code =
                $row->resource?->code_number
                ?? $row->resource_id
                ?? '-';

            $row->resource_phone_number =
                $row->resource?->phone_number
                ?? '-';

            return $row;
        });

    $selectedColumns = $request->input('selected_columns', []);
    $columnLabels    = $request->input('column_labels', []);
    $printLabels     = $request->input('print_labels', []);
    $locale          = $request->input('locale', 'fa');
    $title           = $request->input('title', 'چاپ کلب‌ها');

    return response()->view('prints.table-print', [
        'title'           => $title,
        'rows'            => $rows,
        'selectedColumns' => $selectedColumns,
        'columnLabels'    => $columnLabels,
        'printLabels'     => $printLabels,
        'locale'          => $locale,
    ]);
}
private function removePictureFileAndClear($model)
{
    if (!$model->picture) {
        return response()->json([
            'success' => false,
            'message' => 'عکس نشته',
        ], 404);
    }

    if (Storage::disk('public')->exists($model->picture)) {
        Storage::disk('public')->delete($model->picture);
    }

    $model->update([
        'picture' => null,
    ]);

    return response()->json([
        'success' => true,
        'message' => 'عکس حذف شو',
        'data' => $model->fresh(),
    ]);
}
public function deletePicture($id)
{
    $club = Clubs::findOrFail($id);

    return $this->removePictureFileAndClear($club);
}

public function exportExcel(Request $request)
{
    $search = trim((string) $request->get('search', ''));
    $searchField = $request->get('search_field');
    $searchValue = $request->get('search_value');
    $cardFilter = trim((string) $request->get('card_filter', 'all'));

    $fileName = 'clubs_' . now()->format('Y_m_d_H_i_s') . '.xlsx';

    return Excel::download(
        new ClubsExport([
            'search'       => $search,
            'search_field' => $searchField,
            'search_value' => $searchValue,
            'card_filter'  => $cardFilter,
            'resource_id'  => $request->get('resource_id'),
        ]),
        $fileName
    );
}

}