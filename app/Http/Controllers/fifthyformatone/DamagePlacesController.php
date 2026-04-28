<?php

namespace App\Http\Controllers\fifthyformatone;

use App\Http\Controllers\Controller;
use App\Http\Requests\fifthyformatone\StoreDamagePlacesRequest;
use App\Http\Requests\fifthyformatone\UpdateDamagePlacesRequest;
use App\Http\Resources\fifthyformatone\DamagePlacesResource;
use App\Models\fifthyformatone\DamagePlaces;
use App\Services\NotificationService; // 🔔 notifications
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DamagePlacesController extends Controller
{
    private function duplicateErrorPashto(): array
    {
        return [
            'message' => 'دا ریکارډ مخکې ثبت شوی دی. مهرباني وکړئ تکراري معلومات مه داخلوی.',
            'errors'  => [
                'duplicate' => [
                    'دا ځای مخکې ثبت شوی دی (موقعیت، د تهدید ډول او د مسؤل معلومات یو شان دي).',
                ],
            ],
        ];
    }

    private function isDuplicateKeyException(QueryException $e): bool
    {
        return (int) ($e->errorInfo[1] ?? 0) === 1062;
    }

    // ✅ SaeenFasad-style picture delete helper (handles "storage/..." too)
    private function deletePictureIfStored(?string $picture): void
    {
        if (!$picture) return;

        $path = $picture;
        if (str_starts_with($path, 'storage/')) {
            $path = str_replace('storage/', '', $path);
        }

        Storage::disk('public')->delete($path);
    }

    /** GET /damage-places */
    public function index(Request $request)
    {
        $perPage = (int) $request->input('perPage', 10);
        $search  = trim((string) $request->input('search', ''));
        $rid     = $request->input('resource_id');
        $searchField = trim((string) $request->input('search_field', ''));
        $searchValue = trim((string) $request->input('search_value', ''));

        $sortBy  = $request->input('sort_by', 'created_at');
        $sortDir = in_array($request->input('sort_dir'), ['asc', 'desc'], true)
            ? $request->input('sort_dir')
            : 'desc';

        $allowedSearchFields = [
            
                'province',
        'zone',
        'picture',
        'district',
        'village',
        'specific_place',
        'specific_dot',
        'coordinates',
        'tahdid_type',
        'percentage',
        'tahdid_place',
        'Province_addr',
        'amnyat_khala',
        'cameesa_tafseel',
        'guard_quantity',
        'masool_name',
        'masool_f_name',
        'masool_position',
        'masool_phone',
        'note',
        'resource_id',
        ];

        $q = DamagePlaces::query()->with([
            'resource' => fn ($qr) => $qr->select('id', 'name', 'code_number', 'phone_number'),
        ]);

        if ($search !== '' && $searchField === '') {
            $q->where(function ($w) use ($search) {
                $w->where('province', 'like', "%{$search}%")
                  ->orWhere('district', 'like', "%{$search}%")
                  ->orWhere('village', 'like', "%{$search}%")
                  ->orWhere('tahdid_type', 'like', "%{$search}%");
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

        return DamagePlacesResource::collection($q->paginate($perPage));
    }

    public function filterOptions(Request $request)
    {
        $field = trim((string) $request->query('field', ''));
        $q = trim((string) $request->query('q', ''));

        $allowedFields = [
            
                'province',
        'zone',
        'picture',
        'district',
        'village',
        'specific_place',
        'specific_dot',
        'coordinates',
        'tahdid_type',
        'percentage',
        'tahdid_place',
        'Province_addr',
        'amnyat_khala',
        'cameesa_tafseel',
        'guard_quantity',
        'masool_name',
        'masool_f_name',
        'masool_position',
        'masool_phone',
        'note',
        'resource_id',
        ];

        if ($field === '' || !in_array($field, $allowedFields, true)) {
            return response()->json([
                'data' => [
                    'options' => [],
                ],
            ]);
        }

        $query = DamagePlaces::query()
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

    /** POST /damage-places
     * Accepts:
     * - picture as FILE or STRING
     */
    public function store(StoreDamagePlacesRequest $request)
    {
        $user = $request->user(); // 🔔
        $data = $request->validated();

        try {
            return DB::transaction(function () use ($request, $data, $user) {

                // ✅ picture: file OR string (same style as SaeenFasad)
                if ($request->hasFile('picture')) {
                    $path = $request->file('picture')->store('uploads/damage-places', 'public');
                    $data['picture'] = "storage/{$path}";
                } else {
                    if (array_key_exists('picture', $data) && !is_string($data['picture'])) {
                        unset($data['picture']);
                    }
                }

                $record = DamagePlaces::create($data);

                $record->load([
                    'resource' => fn ($qr) => $qr->select('id', 'name', 'code_number', 'phone_number'),
                ]);

                NotificationService::record(
                    $user,
                    module: 'آسیب پذیره ساحې',
                    action: 'created',
                    title: 'نوی ځای',
                    message: 'تهدید: ' . ($record->tahdid_type ?? '') . ' | ولایت: ' . ($record->province ?? ''),
                    routeName: 'damage-places', // ⚠️ set your real route name
                    routeParams: ['id' => $record->id],
                    level: 'success',
                    changes: null
                );

                return (new DamagePlacesResource($record))
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

    /** GET /damage-places/{damage_place} */
    public function show(DamagePlaces $damage_place)
    {
        $damage_place->load([
            'resource' => fn ($qr) => $qr->select('id', 'name', 'code_number', 'phone_number'),
        ]);

        return DamagePlacesResource::make($damage_place);
    }

    /** PUT /damage-places/{damage_place}
     * Accepts:
     * - picture as FILE or STRING (replaces old file if new uploaded)
     */
    public function update(UpdateDamagePlacesRequest $request, DamagePlaces $damage_place)
    {
        $user = $request->user(); // 🔔
        $validated = $request->validated();

        try {
            return DB::transaction(function () use ($request, $validated, $damage_place, $user) {

                // 🧮 changes BEFORE update
                $original = $damage_place->getOriginal();
                $changes  = [];

                foreach ($validated as $field => $newValue) {
                    if (array_key_exists($field, $original) && $original[$field] != $newValue) {
                        $changes[$field] = [
                            'old' => $original[$field],
                            'new' => $newValue,
                        ];
                    }
                }

                // ✅ picture: replace file if uploaded (same style as SaeenFasad)
                if ($request->hasFile('picture')) {
                    $this->deletePictureIfStored($damage_place->picture);

                    $path = $request->file('picture')->store('uploads/damage-places', 'public');
                    $validated['picture'] = "storage/{$path}";

                    $changes['picture'] = [
                        'old' => $original['picture'] ?? null,
                        'new' => $validated['picture'],
                    ];
                } else {
                    if (array_key_exists('picture', $validated) && !is_string($validated['picture'])) {
                        unset($validated['picture']);
                    }
                }

                $damage_place->update($validated);

                $damage_place->load([
                    'resource' => fn ($qr) => $qr->select('id', 'name', 'code_number', 'phone_number'),
                ]);

                NotificationService::record(
                    $user,
                    module: 'آسیب پذیره ساحې',
                    action: 'updated',
                    title: 'ځای اصلاح شوو',
                    message: 'تهدید: ' . ($damage_place->tahdid_type ?? '') . ' | ولایت: ' . ($damage_place->province ?? ''),
                    routeName: 'damage-places', // ⚠️ set your real route name
                    routeParams: ['id' => $damage_place->id],
                    level: 'info',
                    changes: !empty($changes) ? $changes : null
                );

                return DamagePlacesResource::make($damage_place);
            });
        } catch (QueryException $e) {
            if ($this->isDuplicateKeyException($e)) {
                return response()->json($this->duplicateErrorPashto(), 422);
            }
            throw $e;
        }
    }

    /** DELETE /damage-places/{damage_place} */
    public function destroy(Request $request, DamagePlaces $damage_place)
    {
        $user = $request->user(); // 🔔

        return DB::transaction(function () use ($damage_place, $user) {
            $tahdid = $damage_place->tahdid_type;

            // ✅ delete picture file (same style as SaeenFasad destroy)
            $this->deletePictureIfStored($damage_place->picture);

            $damage_place->delete();

            NotificationService::record(
                $user,
                module: 'آسیب پذیره ساحې',
                action: 'deleted',
                title: 'ځای حذف شوو',
                message: 'تهدید: ' . ($tahdid ?? ''),
                routeName: null,
                routeParams: ['damage-places'],
                level: 'warning',
                changes: null
            );

            return response()->noContent();
        });
    }

    /** POST /damage-places/bulk-delete  { "ids": [1,2,3] } */
    public function bulkDelete(Request $request)
    {
        $user = $request->user(); // 🔔

        $ids = $request->input('ids', []);
        $ids = is_array($ids) ? array_values(array_filter($ids)) : [];

        return DB::transaction(function () use ($ids, $user) {
            if (empty($ids)) {
                return response()->noContent();
            }

            // ✅ delete pictures first (so files don't remain)
            $rows = DamagePlaces::whereIn('id', $ids)->get(['id', 'picture']);
            foreach ($rows as $row) {
                $this->deletePictureIfStored($row->picture);
            }

            DamagePlaces::whereIn('id', $ids)->delete();

            NotificationService::record(
                $user,
                module: 'آسیب پذیره ساحې',
                action: 'deleted',
                title: 'محلات به‌صورت گروهی حذف شد',
                message: 'تعداد: ' . count($ids),
                routeName: null,
                routeParams: ['damage-places'],
                level: 'warning',
                changes: null
            );

            return response()->noContent();
        });
    }
    private function printSearchFields(): array
{
    return [
        'province',
        'zone',
        'picture',
        'district',
        'village',
        'specific_place',
        'specific_dot',
        'coordinates',
        'tahdid_type',
        'percentage',
        'tahdid_place',
        'Province_addr',
        'amnyat_khala',
        'cameesa_tafseel',
        'guard_quantity',
        'masool_name',
        'masool_f_name',
        'masool_position',
        'masool_phone',
        'note',
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
    $resourceId       = $request->query('resource_id');
    $cardFilter       = trim((string) $request->query('card_filter', 'all'));
    $sortBy           = trim((string) $request->query('sort_by', 'created_at'));
    $sortDir          = in_array($request->query('sort_dir'), ['asc', 'desc'], true)
        ? $request->query('sort_dir')
        : 'desc';

    $allowedFields = $this->printSearchFields();

    if ($search !== '' && $searchField === '') {
        $query->where(function ($w) use ($search) {
            $w->where('province', 'like', "%{$search}%")
                ->orWhere('zone', 'like', "%{$search}%")
                ->orWhere('district', 'like', "%{$search}%")
                ->orWhere('village', 'like', "%{$search}%")
                ->orWhere('specific_place', 'like', "%{$search}%")
                ->orWhere('specific_dot', 'like', "%{$search}%")
                ->orWhere('coordinates', 'like', "%{$search}%")
                ->orWhere('tahdid_type', 'like', "%{$search}%")
                ->orWhere('percentage', 'like', "%{$search}%")
                ->orWhere('tahdid_place', 'like', "%{$search}%")
                ->orWhere('Province_addr', 'like', "%{$search}%")
                ->orWhere('amnyat_khala', 'like', "%{$search}%")
                ->orWhere('cameesa_tafseel', 'like', "%{$search}%")
                ->orWhere('guard_quantity', 'like', "%{$search}%")
                ->orWhere('masool_name', 'like', "%{$search}%")
                ->orWhere('masool_f_name', 'like', "%{$search}%")
                ->orWhere('masool_position', 'like', "%{$search}%")
                ->orWhere('masool_phone', 'like', "%{$search}%")
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

    if ($resourceId) {
        $query->where('resource_id', $resourceId);
    }

    if ($cardFilter === 'withCoordinates') {
        $query->whereNotNull('coordinates')
            ->whereRaw("TRIM(coordinates) != ''");
    } elseif ($cardFilter === 'highThreat') {
        $query->where('percentage', '>=', 50);
    } elseif ($cardFilter === 'withGuards') {
        $query->where('guard_quantity', '>', 0);
    }

    return [
        'query' => $query,
        'sort_by' => $sortBy,
        'sort_dir' => $sortDir,
    ];
}

public function printView(Request $request)
{
    $query = DamagePlaces::query()->with([
        'resource' => fn ($qr) => $qr->select('id', 'name', 'code_number', 'phone_number'),
    ]);

    $filterResult = $this->applyPrintFilters($query, $request);

    $rows = $filterResult['query']
        ->orderBy($filterResult['sort_by'], $filterResult['sort_dir'])
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
    $title           = $request->input('title', 'چاپ نقاط آسیب');

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
    $damagePlace = DamagePlaces::findOrFail($id);

    return $this->removePictureFileAndClear($damagePlace);
}  
}