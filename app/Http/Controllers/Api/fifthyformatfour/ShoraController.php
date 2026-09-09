<?php

namespace App\Http\Controllers\Api\fifthyformatfour;

use App\Http\Controllers\Controller;
use App\Http\Requests\fifthyformatfour\StoreShoraRequest;
use App\Http\Requests\fifthyformatfour\UpdateShoraRequest;
use App\Http\Resources\fifthyformatfour\ShoraResource;
use App\Models\fifthyformatfour\Shora;
use App\Services\NotificationService; // 🔔 notifications
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Exports\ShoraExport;
use Maatwebsite\Excel\Facades\Excel;

class ShoraController extends Controller
{
    private function isDuplicateKey(QueryException $e): bool
    {
        return (int) ($e->errorInfo[1] ?? 0) === 1062; // MySQL duplicate key
    }

    private function duplicateResponse()
    {
        return response()->json([
            'message' => 'دا محلي شوراګانې مخکې ثبت شوې دي.',
            'errors' => [
                'duplicate' => [
                    'محلي شوراګانې په همدې مشخصاتو (قریه، نوم، هدف، د افرادو/جلسو شمېر، ثبت او د جلسې ځای) سره مخکې په سیستم کې موجودې دي.',
                ],
            ],
        ], 422);
    }

    public function index(Request $request)
    {
        $page    = $request->query('page');
        $perPage = $request->query('perPage', $request->query('per_page'));

        $isValidPage    = is_numeric($page) && (int) $page > 0;
        $isValidPerPage = is_numeric($perPage) && (int) $perPage > 0;

        $search = trim((string) $request->query('search', ''));
        $searchField = trim((string) $request->query('search_field', ''));
        $searchValue = trim((string) $request->query('search_value', ''));

        $allowedSearchFields = [
                    'shora_village','note',
        'shora_name','shora_goal','people_quantity','week_meeting_quantity','regester','shora_meeting_place',
        'responsible_name','responsible_f_name','responsible_job','responsible_phone',
        'picture',
        'supplier_name','supplier_job','supplier_phone',
        'emarat_tarfdary',
        'shora_province','shora_zone','shora_district',
        'Province_addr',
        'coordinates',
        'resource_id',
        ];

        $query = Shora::query()
            ->with(['resource:id,name,code_number,phone_number'])
            ->when($search !== '' && $searchField === '', function (Builder $sub) use ($search) {
                $sub->where(function (Builder $q) use ($search) {
                    $q->where('shora_name', 'like', "%{$search}%")
                        ->orWhere('responsible_name', 'like', "%{$search}%")
                        ->orWhere('shora_province', 'like', "%{$search}%")
                        ->orWhere('shora_village', 'like', "%{$search}%");
                });
            })
            ->when(
                $searchField !== '' && in_array($searchField, $allowedSearchFields, true),
                function (Builder $sub) use ($searchField, $searchValue, $search) {
                    $value = $searchValue !== '' ? $searchValue : $search;

                    if ($value !== '') {
                        $sub->where($searchField, 'like', "%{$value}%");
                    }
                }
            )
            ->orderByDesc('id');

        if ($isValidPage && $isValidPerPage) {
            $rows = $query->paginate((int) $perPage, ['*'], 'page', (int) $page);
            return ShoraResource::collection($rows);
        }

        return ShoraResource::collection($query->get());
    }

    /**
     * GET /shoras/filter-options
     */
    public function filterOptions(Request $request)
    {
        $field = trim((string) $request->query('field', ''));
        $q = trim((string) $request->query('q', ''));

        $allowedFields = [
                    'shora_village','note',
        'shora_name','shora_goal','people_quantity','week_meeting_quantity','regester','shora_meeting_place',
        'responsible_name','responsible_f_name','responsible_job','responsible_phone',
        'picture',
        'supplier_name','supplier_job','supplier_phone',
        'emarat_tarfdary',
        'shora_province','shora_zone','shora_district',
        'Province_addr',
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

        $query = Shora::query()
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
     * POST /shoras
     * Accepts:
     * - picture as FILE or STRING
     */
    public function store(StoreShoraRequest $request)
    {
        $user = $request->user(); // 🔔
        $data = $request->validated();

        try {
            return DB::transaction(function () use ($request, $data, $user) {

                // ✅ picture: FILE OR STRING (same style as SaeenFasadController)
                if ($request->hasFile('picture')) {
                    $path = $request->file('picture')->store('uploads/shoras', 'public');
                    $data['picture'] = "storage/{$path}";
                } else {
                    if (array_key_exists('picture', $data) && !is_string($data['picture'])) {
                        unset($data['picture']);
                    }
                }

                $row = Shora::create($data);
                $row->load(['resource:id,name,code_number,phone_number']);

                // 🔔 Notification: CREATED
                NotificationService::record(
                    $user,
                    module: 'محلي شوراګانې',
                    action: 'created',
                    title: 'محلي شوراګانې  ثبت شوو',
                    message: 'نوم: ' . ($row->shora_name ?? ''),
                    routeName: 'shoras',
                    routeParams: ['id' => $row->id],
                    level: 'success',
                    changes: null
                );

                return (new ShoraResource($row))
                    ->additional(['message' => 'Created'])
                    ->response()
                    ->setStatusCode(201);
            });
        } catch (QueryException $e) {
            if ($this->isDuplicateKey($e)) return $this->duplicateResponse();
            throw $e;
        }
    }

    public function show(Shora $shora)
    {
        $shora->load(['resource:id,name,code_number,phone_number']);
        return ShoraResource::make($shora);
    }

    /**
     * PUT /shoras/{shora}
     * Accepts:
     * - picture as FILE or STRING (replaces old file if new uploaded)
     */
    public function update(UpdateShoraRequest $request, Shora $shora)
    {
        $user = $request->user(); // 🔔
        $data = $request->validated();

        try {
            return DB::transaction(function () use ($request, $shora, $data, $user) {
                // 🧮 changes BEFORE update
                $original = $shora->getOriginal();
                $changes  = [];

                foreach ($data as $field => $newValue) {
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

                // ✅ picture: replace file if uploaded
                if ($request->hasFile('picture')) {
                    if ($shora->picture && str_starts_with($shora->picture, 'storage/')) {
                        $old = str_replace('storage/', '', $shora->picture);
                        Storage::disk('public')->delete($old);
                    }

                    $path = $request->file('picture')->store('uploads/shoras', 'public');
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

                $shora->update($data);
                $shora->load(['resource:id,name,code_number,phone_number']);

                // 🔔 Notification: UPDATED
                NotificationService::record(
                    $user,
                    module: 'محلي شوراګانې',
                    action: 'updated',
                    title: 'محلي شوراګانې اصلاح شوو',
                    message: ' نوم : ' . ($shora->shora_name ?? ''),
                    routeName: 'shoras',
                    routeParams: ['id' => $shora->id],
                    level: 'info',
                    changes: !empty($changes) ? $changes : null
                );

                return new ShoraResource($shora);
            });
        } catch (QueryException $e) {
            if ($this->isDuplicateKey($e)) return $this->duplicateResponse();
            throw $e;
        }
    }

    public function destroy(Request $request, Shora $shora)
    {
        $user = $request->user(); // 🔔

        return DB::transaction(function () use ($shora, $user) {
            $name = $shora->shora_name;

            // ✅ delete picture file (same style as SaeenFasadController)
            if ($shora->picture && str_starts_with($shora->picture, 'storage/')) {
                $old = str_replace('storage/', '', $shora->picture);
                Storage::disk('public')->delete($old);
            }

            $shora->delete();

            // 🔔 Notification: DELETED
            NotificationService::record(
                $user,
                module: 'محلي شوراګانې',
                action: 'deleted',
                title: 'محلي شوراګانې حذف شوږ',
                message: 'نوم: ' . ($name ?? ''),
                routeName: null,
                routeParams: ['shoras'],
                level: 'warning',
                changes: null
            );

            return response()->json(['message' => 'Deleted']);
        });
    }
    private function printSearchFields(): array
{
    return [
        'shora_village',
        'note',

        'shora_name',
        'shora_goal',
        'people_quantity',
        'week_meeting_quantity',
        'regester',
        'shora_meeting_place',

        'responsible_name',
        'responsible_f_name',
        'responsible_job',
        'responsible_phone',

        'picture',

        'supplier_name',
        'supplier_job',
        'supplier_phone',

        'emarat_tarfdary',

        'shora_province',
        'shora_zone',
        'shora_district',

        'Province_addr',
        'coordinates',

        'resource_id',
        'resource_code',
        'resource_phone',
    ];
}

private function applyPrintFilters($query, Request $request)
{
    $search      = trim((string) $request->query('search', ''));
    $searchField = trim((string) $request->query('search_field', ''));
    $searchValue = trim((string) $request->query('search_value', ''));
    $resourceId  = $request->query('resource_id');
    $cardFilter  = trim((string) $request->query('card_filter', 'all'));

    $allowedFields = $this->printSearchFields();

    if ($search !== '' && $searchField === '') {
        $query->where(function (Builder $q) use ($search) {
            $q->where('shora_name', 'like', "%{$search}%")
                ->orWhere('shora_goal', 'like', "%{$search}%")
                ->orWhere('shora_village', 'like', "%{$search}%")
                ->orWhere('people_quantity', 'like', "%{$search}%")
                ->orWhere('week_meeting_quantity', 'like', "%{$search}%")
                ->orWhere('regester', 'like', "%{$search}%")
                ->orWhere('shora_meeting_place', 'like', "%{$search}%")
                ->orWhere('responsible_name', 'like', "%{$search}%")
                ->orWhere('responsible_f_name', 'like', "%{$search}%")
                ->orWhere('responsible_job', 'like', "%{$search}%")
                ->orWhere('responsible_phone', 'like', "%{$search}%")
                ->orWhere('supplier_name', 'like', "%{$search}%")
                ->orWhere('supplier_job', 'like', "%{$search}%")
                ->orWhere('supplier_phone', 'like', "%{$search}%")
                ->orWhere('emarat_tarfdary', 'like', "%{$search}%")
                ->orWhere('shora_province', 'like', "%{$search}%")
                ->orWhere('shora_zone', 'like', "%{$search}%")
                ->orWhere('shora_district', 'like', "%{$search}%")
                ->orWhere('Province_addr', 'like', "%{$search}%")
                ->orWhere('coordinates', 'like', "%{$search}%")
                ->orWhere('note', 'like', "%{$search}%")
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

    if (is_numeric($resourceId) && (int) $resourceId > 0) {
        $query->where('resource_id', (int) $resourceId);
    }

    if ($cardFilter === 'withCoordinates') {
        $query->whereNotNull('coordinates')
            ->whereRaw("TRIM(coordinates) != ''");
    } elseif ($cardFilter === 'withResponsiblePhone') {
        $query->whereNotNull('responsible_phone')
            ->whereRaw("TRIM(responsible_phone) != ''");
    } elseif ($cardFilter === 'withSupplierPhone') {
        $query->whereNotNull('supplier_phone')
            ->whereRaw("TRIM(supplier_phone) != ''");
    }

    return $query;
}

public function printView(Request $request)
{
    $query = Shora::query()
        ->with(['resource:id,name,code_number,phone_number']);

    $this->applyPrintFilters($query, $request);

    $rows = $query
        ->orderByDesc('id')
        ->get()
        ->map(function ($row) {
            $row->resource_code =
                $row->resource?->code_number
                ?? $row->resource_id
                ?? '-';

            $row->resource_phone =
                $row->resource?->phone_number
                ?? $row->resource_phone
                ?? '-';

            return $row;
        });

    $selectedColumns = $request->input('selected_columns', []);
    $columnLabels    = $request->input('column_labels', []);
    $printLabels     = $request->input('print_labels', []);
    $locale          = $request->input('locale', 'fa');
    $title           = $request->input('title', 'چاپ شوراها');

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
    $shora = Shora::findOrFail($id);

    return $this->removePictureFileAndClear($shora);
}

    public function exportExcel(Request $request)
    {
        $filters = $request->only(['search', 'search_field', 'search_value', 'resource_id']);
        return Excel::download(new ShoraExport($filters), 'shora_' . now()->format('Y_m_d_His') . '.xlsx');
    }
}