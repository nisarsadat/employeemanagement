<?php

namespace App\Http\Controllers\fifthyformatsthree;

use App\Http\Controllers\Controller;
use App\Models\fifthyformatsthree\Mujahedino;
use App\Http\Resources\fifthyformatsthree\MujahedinoResource;
use App\Http\Requests\fifthyformatsthree\StoreMujahedinoRequest;
use App\Http\Requests\fifthyformatsthree\UpdateMujahedinoRequest;
use App\Services\NotificationService; // 🔔 notifications
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class MujahedinoController extends Controller
{
    /**
     * Pashto duplicate error message (unique constraint)
     */
    private function duplicateErrorPashto(): array
    {
        return [
            'message' => 'دا ریکارډ مخکې ثبت شوی دی. مهرباني وکړئ تکراري معلومات مه داخلوی.',
            'errors'  => [
                'duplicate' => [
                    'د همدې معلوماتو سره (لقب/نوم، د پلار نوم او اصلي ادرس) ریکارډ مخکې له مخکې موجود دی.',
                ],
            ],
        ];
    }

    private function isDuplicateKeyException(QueryException $e): bool
    {
        return (int) ($e->errorInfo[1] ?? 0) === 1062;
    }

    /**
     * GET /mujahedinos
     */
    public function index(Request $request)
    {
        $page    = $request->query('page');
        $perPage = $request->query('perPage', $request->query('per_page'));

        $isValidPage    = is_numeric($page) && (int) $page > 0;
        $isValidPerPage = is_numeric($perPage) && (int) $perPage > 0;

        $search      = trim((string) $request->input('search', ''));
        $searchField = trim((string) $request->input('search_field', ''));
        $searchValue = trim((string) $request->input('search_value', ''));

        $sortBy  = $request->input('sort_by', 'created_at');
        $sortDir = in_array($request->input('sort_dir'), ['asc', 'desc'], true)
            ? $request->input('sort_dir')
            : 'desc';

        $allowedSearchFields = [
            
         'picture',
        'nickname',
        'name',
        'father_name',

        'main_province',
        'main_district',
        'main_village',

        'now_province',
        'now_zone',
        'Province_addr',
        'now_district',
        'now_village',
        'now_specific_place',
        'now_street_number',
        'now_home_number',

        'duty_type',
        'now_job',
        'organ',
        'friends_quantity',
        'phone_number',
        'note',
        'coordinates',
        'resource_id',
        ];

        $q = Mujahedino::query()->with([
            'resource' => fn ($qr) => $qr->select('id', 'name', 'code_number', 'phone_number'),
        ]);

        if ($search !== '' && $searchField === '') {
            $q->where(function ($qq) use ($search) {
                $qq->where('name', 'like', "%{$search}%")
                    ->orWhere('nickname', 'like', "%{$search}%")
                    ->orWhere('father_name', 'like', "%{$search}%")
                    ->orWhere('organ', 'like', "%{$search}%")
                    ->orWhere('now_province', 'like', "%{$search}%")
                    ->orWhere('now_district', 'like', "%{$search}%")
                    ->orWhere('now_village', 'like', "%{$search}%");
            });
        }

        if ($searchField !== '' && in_array($searchField, $allowedSearchFields, true)) {
            $value = $searchValue !== '' ? $searchValue : $search;

            if ($value !== '') {
                $q->where($searchField, 'like', "%{$value}%");
            }
        }

        $q->orderBy($sortBy, $sortDir);

        if ($isValidPage && $isValidPerPage) {
            return MujahedinoResource::collection(
                $q->paginate((int) $perPage, ['*'], 'page', (int) $page)
            );
        }

        return MujahedinoResource::collection($q->get());
    }

    /**
     * GET /mujahedinos/filter-options
     */
    public function filterOptions(Request $request)
    {
        $field = trim((string) $request->query('field', ''));
        $q     = trim((string) $request->query('q', ''));

        $allowedFields = [
            
         'picture',
        'nickname',
        'name',
        'father_name',

        'main_province',
        'main_district',
        'main_village',

        'now_province',
        'now_zone',
        'Province_addr',
        'now_district',
        'now_village',
        'now_specific_place',
        'now_street_number',
        'now_home_number',

        'duty_type',
        'now_job',
        'organ',
        'friends_quantity',
        'phone_number',
        'note',
        'coordinates',
        'resource_id',
        ];

        if ($field === '' || !in_array($field, $allowedFields, true)) {
            return response()->json([
                'data' => [
                    'options' => [],
                ],
            ]);
        }

        $query = Mujahedino::query()
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

    /**
     * POST /mujahedinos
     */
    public function store(StoreMujahedinoRequest $request)
    {
        $user = $request->user(); // 🔔

        try {
            return DB::transaction(function () use ($request, $user) {
                $item = Mujahedino::create($request->validated());

                $item->load([
                    'resource' => fn ($qr) => $qr->select('id', 'name', 'code_number', 'phone_number'),
                ]);

                // 🔔 Notification: CREATED
                NotificationService::record(
                    $user,
                    module: 'مجاهدین',
                    action: 'created',
                    title: 'نوی مجاهد ثبت شو',
                    message: 'نوم: ' . ($item->name ?? ''),
                    routeName: 'mujahedinos',
                    routeParams: ['id' => $item->id],
                    level: 'success',
                    changes: null
                );

                return (new MujahedinoResource($item))
                    ->additional(['message' => 'Created'])
                    ->response()
                    ->setStatusCode(201);
            });
        } catch (QueryException $e) {
            if ($this->isDuplicateKeyException($e)) {
                return response()->json($this->duplicateErrorPashto(), 422);
            }
            throw $e;
        }
    }

    /**
     * GET /mujahedinos/{mujahedino}
     */
    public function show(Mujahedino $mujahedino)
    {
        $mujahedino->load([
            'resource' => fn ($qr) => $qr->select('id', 'name', 'code_number', 'phone_number'),
        ]);

        return MujahedinoResource::make($mujahedino);
    }

    /**
     * PUT /mujahedinos/{mujahedino}
     */
    public function update(UpdateMujahedinoRequest $request, Mujahedino $mujahedino)
    {
        $user = $request->user(); // 🔔

        try {
            return DB::transaction(function () use ($request, $mujahedino, $user) {
                $validated = $request->validated();

                // 🧮 changes BEFORE update
                $original = $mujahedino->getOriginal();
                $changes  = [];

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

                $mujahedino->update($validated);

                $mujahedino->load([
                    'resource' => fn ($qr) => $qr->select('id', 'name', 'code_number', 'phone_number'),
                ]);

                // 🔔 Notification: UPDATED
                NotificationService::record(
                    $user,
                    module: 'مجاهدین',
                    action: 'updated',
                    title: 'مجاهد اصلاح شو',
                    message: 'نوم: ' . ($mujahedino->name ?? ''),
                    routeName: 'mujahedinos',
                    routeParams: ['id' => $mujahedino->id],
                    level: 'info',
                    changes: !empty($changes) ? $changes : null
                );

                return MujahedinoResource::make($mujahedino);
            });
        } catch (QueryException $e) {
            if ($this->isDuplicateKeyException($e)) {
                return response()->json($this->duplicateErrorPashto(), 422);
            }
            throw $e;
        }
    }

    /**
     * DELETE /mujahedinos/{mujahedino}
     */
    public function destroy(Request $request, Mujahedino $mujahedino)
    {
        $user = $request->user(); // 🔔

        return DB::transaction(function () use ($mujahedino, $user) {
            $name = $mujahedino->name;

            $mujahedino->delete();

            // 🔔 Notification: DELETED
            NotificationService::record(
                $user,
                module: 'مجاهدین',
                action: 'deleted',
                title: 'مجاهد حذف شو',
                message: 'نوم: ' . ($name ?? ''),
                routeName: null,
                routeParams: ['mujahedinos'],
                level: 'warning',
                changes: null
            );

            return response()->noContent();
        });
    }

    /**
     * Optional bulk delete:
     * POST /mujahedinos/bulk-delete  with { "ids": [1,2,3] }
     */
    public function bulkDelete(Request $request)
    {
        $user = $request->user(); // 🔔

        $ids = $request->input('ids', []);
        $ids = is_array($ids) ? array_values(array_filter($ids)) : [];

        return DB::transaction(function () use ($ids, $user) {
            if (empty($ids)) {
                return response()->noContent();
            }

            Mujahedino::whereIn('id', $ids)->delete();

            // 🔔 Notification: BULK DELETED
            NotificationService::record(
                $user,
                module: 'مجاهدین',
                action: 'deleted',
                title: 'ډله‌ییز حذف ترسره شو',
                message: 'حذف شوي ریکارډونه: ' . count($ids),
                routeName: null,
                routeParams: ['mujahedinos'],
                level: 'warning',
                changes: null
            );

            return response()->noContent();
        });
    }
    private function printSearchFields(): array
{
    return [
        'picture',
        'nickname',
        'name',
        'father_name',

        'main_province',
        'main_district',
        'main_village',

        'now_province',
        'now_zone',
        'Province_addr',
        'now_district',
        'now_village',
        'now_specific_place',
        'now_street_number',
        'now_home_number',

        'duty_type',
        'now_job',
        'organ',
        'friends_quantity',
        'phone_number',
        'note',
        'coordinates',

        'resource_id',
        'resource_code',
        'resource_phone',
    ];
}

private function applyPrintFilters($query, Request $request)
{
    $search           = trim((string) $request->query('search', ''));
    $searchField      = trim((string) $request->query('search_field', ''));
    $searchValue      = trim((string) $request->query('search_value', ''));
    $cardFilter       = trim((string) $request->query('card_filter', 'all'));
    $fiftySixFormatId = $request->query('fifty_six_format_id');

    $allowedFields = $this->printSearchFields();

    if ($search !== '' && $searchField === '') {
        $query->where(function ($qq) use ($search) {
            $qq->where('name', 'like', "%{$search}%")
                ->orWhere('nickname', 'like', "%{$search}%")
                ->orWhere('father_name', 'like', "%{$search}%")
                ->orWhere('main_province', 'like', "%{$search}%")
                ->orWhere('main_district', 'like', "%{$search}%")
                ->orWhere('main_village', 'like', "%{$search}%")
                ->orWhere('now_province', 'like', "%{$search}%")
                ->orWhere('now_zone', 'like', "%{$search}%")
                ->orWhere('Province_addr', 'like', "%{$search}%")
                ->orWhere('now_district', 'like', "%{$search}%")
                ->orWhere('now_village', 'like', "%{$search}%")
                ->orWhere('now_specific_place', 'like', "%{$search}%")
                ->orWhere('now_street_number', 'like', "%{$search}%")
                ->orWhere('now_home_number', 'like', "%{$search}%")
                ->orWhere('duty_type', 'like', "%{$search}%")
                ->orWhere('now_job', 'like', "%{$search}%")
                ->orWhere('organ', 'like', "%{$search}%")
                ->orWhere('friends_quantity', 'like', "%{$search}%")
                ->orWhere('phone_number', 'like', "%{$search}%")
                ->orWhere('note', 'like', "%{$search}%")
                ->orWhere('coordinates', 'like', "%{$search}%")
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
            } elseif ($searchField === 'resource_phone') {
                $query->whereHas('resource', function ($r) use ($value) {
                    $r->where('phone_number', 'like', "%{$value}%");
                });
            } else {
                $query->where($searchField, 'like', "%{$value}%");
            }
        }
    }

    /**
     * Important:
     * Your current index passes fifty_six_format_id from related route filters.
     * In your current index logic, this is usually for opening/peeking a related row.
     * If your mujahedinos table has a real fifty_six_format_id column, uncomment this:
     */
    // if (is_numeric($fiftySixFormatId) && (int) $fiftySixFormatId > 0) {
    //     $query->where('fifty_six_format_id', (int) $fiftySixFormatId);
    // }

    if ($cardFilter === 'withCoordinates') {
        $query->whereNotNull('coordinates')
            ->whereRaw("TRIM(coordinates) != ''");
    } elseif ($cardFilter === 'withPhone') {
        $query->whereNotNull('phone_number')
            ->whereRaw("TRIM(phone_number) != ''");
    } elseif ($cardFilter === 'withJob') {
        $query->whereNotNull('now_job')
            ->whereRaw("TRIM(now_job) != ''");
    }

    return $query;
}

public function printView(Request $request)
{
    $query = Mujahedino::query()
        ->with([
            'resource' => fn ($qr) => $qr->select('id', 'name', 'code_number', 'phone_number'),
        ]);

    $this->applyPrintFilters($query, $request);

    $rows = $query
        ->orderByDesc('created_at')
        ->get()
        ->map(function ($row) {
            $row->resource_code =
                $row->resource?->code_number
                ?? $row->resource_id
                ?? '-';

            $row->resource_phone =
                $row->resource?->phone_number
                ?? '-';

            return $row;
        });

    $selectedColumns = $request->input('selected_columns', []);
    $columnLabels    = $request->input('column_labels', []);
    $printLabels     = $request->input('print_labels', []);
    $locale          = $request->input('locale', 'fa');
    $title           = $request->input('title', 'چاپ مجاهدینو');

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
    $mujahedino = Mujahedino::findOrFail($id);

    return $this->removePictureFileAndClear($mujahedino);
}

public function exportExcel(Request $request)
{
    $search = trim((string) $request->get('search', ''));
    $searchField = $request->get('search_field');
    $searchValue = $request->get('search_value');
    $fileName = 'mujahedinos_' . now()->format('Y_m_d_H_i_s') . '.xlsx';
    return Excel::download(new \App\Exports\MujahedinoExport(['search' => $search, 'search_field' => $searchField, 'search_value' => $searchValue, 'resource_id' => $request->get('resource_id')]), $fileName);
}
}