<?php

namespace App\Http\Controllers\fifthyformatone;

use App\Http\Controllers\Controller;
use App\Http\Requests\fifthyformatone\StoreLuchakanoRequest;
use App\Http\Requests\fifthyformatone\UpdateLuchakanoRequest;
use App\Http\Resources\fifthyformatone\LuchakanoResource;
use App\Models\fifthyformatone\Luchakano;
use App\Services\NotificationService;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\LuchakanoExport;

class LuchakanoController extends Controller
{
    private function duplicateErrorPashto(): array
    {
        return [
            'message' => 'دا ریکارډ مخکې ثبت شوی دی. مهرباني وکړئ تکراري معلومات مه داخلوی.',
            'errors'  => [
                'duplicate' => [
                    'دا کس مخکې ثبت شوی دی (لقب/نوم، د پلار نوم او اصلي پته یو شان دي).',
                ],
            ],
        ];
    }

    private function isDuplicateKeyException(QueryException $e): bool
    {
        return (int) ($e->errorInfo[1] ?? 0) === 1062;
    }

    private function allowedSearchFields(): array
    {
        $table = (new Luchakano())->getTable();

        $candidates = [
            'information_id',

        'picture',
        'nickname',
        'name',
        'father_name',
        'main_province',
        'main_district',
        'main_village',
        'now_province',
        'now_zone',
        'now_district',
        'now_village',
        'now_specific_place',
        'now_street_number',
        'Province_addr',
        'now_home_number',
        'old_job',
        'coordinates',
        'job',
        'gun_type',
        'phone_number',
        'history_jorm',
        'supplier',
        'takhlof_type',
        'note',
        'resource_id',
        ];

        return array_values(array_filter($candidates, fn ($field) => Schema::hasColumn($table, $field)));
    }

    // ✅ Mawad style: delete by public disk path
    private function deletePictureIfStored(?string $picture): void
    {
        if (!$picture) return;

        $path = $picture;

        // normalize if it was stored as "storage/..."
        if (str_starts_with($path, 'storage/')) {
            $path = substr($path, strlen('storage/'));
        }

        Storage::disk('public')->delete($path);
    }

    /** GET /luchakano */
    public function index(Request $request)
    {
        $perPage = (int) $request->input('perPage', 10);
        $search  = trim((string) $request->input('search', ''));
        $rid     = $request->input('resource_id');

        $searchField = trim((string) $request->input('search_field', ''));
        $searchValue = trim((string) $request->input('search_value', ''));

        $sortBy  = $request->input('sort_by', 'created_at');
        $sortDir = in_array($request->input('sort_dir'), ['asc', 'desc'], true) ? $request->input('sort_dir') : 'desc';

        $allowedSearchFields = $this->allowedSearchFields();

        $q = Luchakano::query()->with([
            'resource' => fn ($qr) => $qr->select('id', 'name', 'code_number', 'phone_number'),
        ]);

        if ($search !== '' && $searchField === '') {
            $q->where(function ($w) use ($search) {
                $w->where('nickname', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%")
                  ->orWhere('main_province', 'like', "%{$search}%")
                  ->orWhere('now_province', 'like', "%{$search}%")
                  ->orWhere('job', 'like', "%{$search}%")
                  ->orWhere('gun_type', 'like', "%{$search}%")
                  ->orWhere('phone_number', 'like', "%{$search}%");
            });
        }

        if ($searchField !== '' && in_array($searchField, $allowedSearchFields, true)) {
            $value = $searchValue !== '' ? $searchValue : $search;

            if ($value !== '') {
                $q->where($searchField, 'like', "%{$value}%");
            }
        }

        if ($rid) {
            $q->where('resource_id', $rid);
        }

        $q->orderBy($sortBy, $sortDir);

        return LuchakanoResource::collection($q->paginate($perPage));
    }

    /**
     * GET /luchakano/filter-options
     */
    public function filterOptions(Request $request)
    {
        $field = trim((string) $request->query('field', ''));
        $q     = trim((string) $request->query('q', ''));

        $allowedFields = $this->allowedSearchFields();

        if ($field === '' || !in_array($field, $allowedFields, true)) {
            return response()->json([
                'data' => [
                    'options' => [],
                ],
            ]);
        }

        $query = Luchakano::query()
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

    /** POST /luchakano */
    public function store(StoreLuchakanoRequest $request)
    {
        $user = $request->user();

        try {
            return DB::transaction(function () use ($request, $user) {
                $data = $request->validated();

                // ✅ picture upload (store ONLY disk path)
                if ($request->hasFile('picture')) {
                    $data['picture'] = $request->file('picture')->store('luchakanos', 'public');
                }

                $record = Luchakano::create($data);

                $record->load([
                    'resource' => fn ($qr) => $qr->select('id', 'name', 'code_number', 'phone_number'),
                ]);

                NotificationService::record(
                    $user,
                    module: 'لوچکانو',
                    action: 'created',
                    title: 'ریکارډ جدید ثبت شد',
                    message: 'نام: ' . ($record->name ?? '') . ' | لقب: ' . ($record->nickname ?? ''),
                    routeName: 'luchakano',
                    routeParams: ['id' => $record->id],
                    level: 'success',
                    changes: null
                );

                return (new LuchakanoResource($record))
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

    /** GET /luchakano/{luchakano} */
    public function show(Luchakano $luchakano)
    {
        $luchakano->load([
            'resource' => fn ($qr) => $qr->select('id', 'name', 'code_number', 'phone_number'),
        ]);

        return LuchakanoResource::make($luchakano);
    }

    /** PUT /luchakano/{luchakano} */
    public function update(UpdateLuchakanoRequest $request, Luchakano $luchakano)
    {
        $user = $request->user();

        try {
            return DB::transaction(function () use ($request, $luchakano, $user) {
                $validated = $request->validated();

                // 🧮 changes BEFORE update
                $original = $luchakano->getOriginal();
                $changes  = [];

                foreach ($validated as $field => $newValue) {
                    if (array_key_exists($field, $original) && $original[$field] != $newValue) {
                        $changes[$field] = [
                            'old' => $original[$field],
                            'new' => $newValue,
                        ];
                    }
                }

                // ✅ picture update
                if ($request->hasFile('picture')) {
                    $this->deletePictureIfStored($luchakano->picture);

                    $validated['picture'] = $request->file('picture')->store('luchakanos', 'public');

                    $changes['picture'] = [
                        'old' => $original['picture'] ?? null,
                        'new' => $validated['picture'],
                    ];
                }

                $luchakano->update($validated);

                $luchakano->load([
                    'resource' => fn ($qr) => $qr->select('id', 'name', 'code_number', 'phone_number'),
                ]);

                NotificationService::record(
                    $user,
                    module: 'لوچکانو',
                    action: 'updated',
                    title: 'ریکارډ اصلاح شد',
                    message: 'نام: ' . ($luchakano->name ?? '') . ' | لقب: ' . ($luchakano->nickname ?? ''),
                    routeName: 'luchakano',
                    routeParams: ['id' => $luchakano->id],
                    level: 'info',
                    changes: !empty($changes) ? $changes : null
                );

                return LuchakanoResource::make($luchakano);
            });
        } catch (QueryException $e) {
            if ($this->isDuplicateKeyException($e)) {
                return response()->json($this->duplicateErrorPashto(), 422);
            }
            throw $e;
        }
    }

    /** DELETE /luchakano/{luchakano} */
    public function destroy(Request $request, Luchakano $luchakano)
    {
        $user = $request->user();

        return DB::transaction(function () use ($luchakano, $user) {
            $name = $luchakano->name;

            $this->deletePictureIfStored($luchakano->picture);

            $luchakano->delete();

            NotificationService::record(
                $user,
                module: 'لوچکانو',
                action: 'deleted',
                title: 'ریکارډ حذف شد',
                message: 'نام: ' . ($name ?? ''),
                routeName: null,
                routeParams: ['luchakano'],
                level: 'warning',
                changes: null
            );

            return response()->noContent();
        });
    }

    /** POST /luchakano/bulk-delete  { "ids": [1,2,3] } */
    public function bulkDelete(Request $request)
    {
        $user = $request->user();

        $ids = $request->input('ids', []);
        $ids = is_array($ids) ? array_values(array_filter($ids)) : [];

        return DB::transaction(function () use ($ids, $user) {
            if (empty($ids)) {
                return response()->noContent();
            }

            // ✅ delete pictures first
            $items = Luchakano::whereIn('id', $ids)->get();
            foreach ($items as $item) {
                $this->deletePictureIfStored($item->picture);
            }

            Luchakano::whereIn('id', $ids)->delete();

            NotificationService::record(
                $user,
                module: 'لوچکانو',
                action: 'deleted',
                title: 'ریکارډونه په ډله‌ییز ډول حذف شول',
                message: 'تعداد: ' . count($ids),
                routeName: null,
                routeParams: ['luchakano'],
                level: 'warning',
                changes: null
            );

            return response()->noContent();
        });
    }
    private function printSearchFields(): array
{
    $fields = $this->allowedSearchFields();

    $extraFields = [
        'resource_code',
        'resource_phone',
    ];

    return array_values(array_unique(array_merge($fields, $extraFields)));
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
        $query->where(function ($q) use ($search) {
            $q->where('nickname', 'like', "%{$search}%")
                ->orWhere('name', 'like', "%{$search}%")
                ->orWhere('father_name', 'like', "%{$search}%")
                ->orWhere('main_province', 'like', "%{$search}%")
                ->orWhere('main_district', 'like', "%{$search}%")
                ->orWhere('main_village', 'like', "%{$search}%")
                ->orWhere('now_province', 'like', "%{$search}%")
                ->orWhere('now_zone', 'like', "%{$search}%")
                ->orWhere('now_district', 'like', "%{$search}%")
                ->orWhere('now_village', 'like', "%{$search}%")
                ->orWhere('now_specific_place', 'like', "%{$search}%")
                ->orWhere('now_street_number', 'like', "%{$search}%")
                ->orWhere('now_home_number', 'like', "%{$search}%")
                ->orWhere('old_job', 'like', "%{$search}%")
                ->orWhere('coordinates', 'like', "%{$search}%")
                ->orWhere('job', 'like', "%{$search}%")
                ->orWhere('gun_type', 'like', "%{$search}%")
                ->orWhere('phone_number', 'like', "%{$search}%")
                ->orWhere('history_jorm', 'like', "%{$search}%")
                ->orWhere('supplier', 'like', "%{$search}%")
                ->orWhere('takhlof_type', 'like', "%{$search}%")
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
            } elseif ($searchField === 'resource_id') {
                $query->where('resource_id', $value);
            } else {
                $query->where($searchField, 'like', "%{$value}%");
            }
        }
    }

    if ($resourceId) {
        $query->where('resource_id', $resourceId);
    }

    if ($cardFilter === 'withCoordinates') {
        $query->whereNotNull('coordinates')
            ->whereRaw("TRIM(coordinates) != ''");
    } elseif ($cardFilter === 'withPhone') {
        $query->whereNotNull('phone_number')
            ->whereRaw("TRIM(phone_number) != ''");
    } elseif ($cardFilter === 'withResource') {
        $query->whereNotNull('resource_id')
            ->whereRaw("TRIM(resource_id) != ''");
    }

    return $query;
}

public function printView(Request $request)
{
    $query = Luchakano::query()
        ->with([
            'resource' => fn ($qr) => $qr->select('id', 'name', 'code_number', 'phone_number'),
        ]);

    $this->applyPrintFilters($query, $request);

    $rows = $query
        ->orderByDesc('id')
        ->get()
        ->map(function ($row) {
            $resourceCode = $row->resource?->code_number;
            $resourcePhone = $row->resource?->phone_number;

            $row->resource_code = $resourceCode ?? '-';
            $row->resource_phone = $resourcePhone ?? '-';

            // Because your header key is resource_id but title says "کود منبع"
            $row->resource_id = $resourceCode ?? $row->resource_id ?? '-';

            return $row;
        });

    $selectedColumns = $request->input('selected_columns', []);
    $columnLabels    = $request->input('column_labels', []);
    $printLabels     = $request->input('print_labels', []);
    $locale          = $request->input('locale', 'fa');
    $title           = $request->input('title', 'چاپ لچکانو');

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
    $luchakano = Luchakano::findOrFail($id);

    return $this->removePictureFileAndClear($luchakano);
}

public function exportExcel(Request $request)
{
    $search = trim((string) $request->get('search', ''));
    $searchField = $request->get('search_field');
    $searchValue = $request->get('search_value');
    $fileName = 'luchakanos_' . now()->format('Y_m_d_H_i_s') . '.xlsx';
    return Excel::download(new LuchakanoExport(['search' => $search, 'search_field' => $searchField, 'search_value' => $searchValue, 'resource_id' => $request->get('resource_id')]), $fileName);
}
}