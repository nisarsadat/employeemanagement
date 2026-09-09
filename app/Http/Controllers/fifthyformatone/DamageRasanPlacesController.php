<?php

namespace App\Http\Controllers\fifthyformatone;

use App\Exports\DamageRasanPlacesExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\fifthyformatone\StoreDamageRasanPlacesRequest;
use App\Http\Requests\fifthyformatone\UpdateDamageRasanPlacesRequest;
use App\Http\Resources\fifthyformatone\DamageRasanPlacesResource;
use App\Models\fifthyformatone\DamageRasanPlaces;
use App\Services\NotificationService; // 🔔 notifications
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class DamageRasanPlacesController extends Controller
{
    /** GET /damage-rasan-places */
    public function index(Request $request)
    {
        $perPage = (int) $request->input('perPage', 10);
        $search  = trim((string) $request->input('search', ''));
        $searchField = trim((string) $request->input('search_field', ''));
        $searchValue = trim((string) $request->input('search_value', ''));

        $allowedSearchFields = [
                    'province',
        'zone',
        'district',
        'village',
        'specific_place',
        'specific_dot',
        'picture',
        'coordinates',
        'tahdid_type',
        'percentage',
        'damage_place',
        'Province_addr',
        'note',
        'resource_id',
        ];

        $q = DamageRasanPlaces::query()
            ->with(['resource:id,name,code_number,phone_number'])
            ->latest();

        if ($search !== '' && $searchField === '') {
            $q->where(function ($w) use ($search) {
                $w->where('province', 'like', "%{$search}%")
                  ->orWhere('district', 'like', "%{$search}%")
                  ->orWhere('village', 'like', "%{$search}%")
                  ->orWhere('damage_place', 'like', "%{$search}%");
            });
        }

        if ($searchField !== '' && in_array($searchField, $allowedSearchFields, true)) {
            $value = $searchValue !== '' ? $searchValue : $search;

            if ($value !== '') {
                $q->where($searchField, 'like', "%{$value}%");
            }
        }

        return DamageRasanPlacesResource::collection($q->paginate($perPage));
    }

    public function filterOptions(Request $request)
    {
        $field = trim((string) $request->query('field', ''));
        $q = trim((string) $request->query('q', ''));

        $allowedFields = [
                    'province',
        'zone',
        'district',
        'village',
        'specific_place',
        'specific_dot',
        'picture',
        'coordinates',
        'tahdid_type',
        'percentage',
        'damage_place',
        'Province_addr',
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

        $query = DamageRasanPlaces::query()
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

    /** POST /damage-rasan-places
     * Accepts:
     * - picture as FILE or STRING
     */
    public function store(StoreDamageRasanPlacesRequest $request)
    {
        $user = $request->user(); // 🔔
        $data = $request->validated();

        return DB::transaction(function () use ($request, $data, $user) {

            // ✅ picture: file OR string (same style as SaeenFasad)
            if ($request->hasFile('picture')) {
                $path = $request->file('picture')->store('uploads/damage-rasan-places', 'public');
                $data['picture'] = "storage/{$path}";
            } else {
                if (array_key_exists('picture', $data) && !is_string($data['picture'])) {
                    unset($data['picture']);
                }
            }

            $record = DamageRasanPlaces::create($data);
            $record->load(['resource:id,name,code_number,phone_number']);

            // 🔔 Notification: CREATED
            NotificationService::record(
                $user,
                module: 'آسیب رسانې ساحې',
                action: 'created',
                title: 'ریکارډ ثبت شوو',
                message: 'ولایت: ' . ($record->province ?? '') . ' | ځای: ' . ($record->damage_place ?? ''),
                routeName: 'damage-rasan-places',
                routeParams: ['id' => $record->id],
                level: 'success',
                changes: null
            );

            return (new DamageRasanPlacesResource($record))
                ->additional(['message' => 'Created'])
                ->response()
                ->setStatusCode(201);
        });
    }

    /** GET /damage-rasan-places/{damage_rasan_place} */
    public function show(DamageRasanPlaces $damage_rasan_place)
    {
        $damage_rasan_place->load(['resource:id,name,code_number,phone_number']);
        return DamageRasanPlacesResource::make($damage_rasan_place);
    }

    /** PUT /damage-rasan-places/{damage_rasan_place}
     * Accepts:
     * - picture as FILE or STRING (replaces old file if new uploaded)
     */
    public function update(UpdateDamageRasanPlacesRequest $request, DamageRasanPlaces $damage_rasan_place)
    {
        $user = $request->user(); // 🔔
        $data = $request->validated();

        return DB::transaction(function () use ($request, $data, $damage_rasan_place, $user) {

            // 🧮 changes BEFORE update
            $original = $damage_rasan_place->getOriginal();
            $changes  = [];

            foreach ($data as $field => $newValue) {
                if (array_key_exists($field, $original) && $original[$field] != $newValue) {
                    $changes[$field] = ['old' => $original[$field], 'new' => $newValue];
                }
            }

            // ✅ picture replace (same style as SaeenFasad)
            if ($request->hasFile('picture')) {
                $this->deletePictureIfStored($damage_rasan_place->picture);

                $path = $request->file('picture')->store('uploads/damage-rasan-places', 'public');
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

            $damage_rasan_place->update($data);
            $damage_rasan_place->load(['resource:id,name,code_number,phone_number']);

            // 🔔 Notification: UPDATED
            NotificationService::record(
                $user,
                module: 'آسیب رسانې ساحې',
                action: 'updated',
                title: 'ریکارډ اصلاح شو',
                message: 'ولایت: ' . ($damage_rasan_place->province ?? '') . ' | ځای: ' . ($damage_rasan_place->damage_place ?? ''),
                routeName: 'damage-rasan-places',
                routeParams: ['id' => $damage_rasan_place->id],
                level: 'info',
                changes: !empty($changes) ? $changes : null
            );

            return DamageRasanPlacesResource::make($damage_rasan_place);
        });
    }

    /** DELETE /damage-rasan-places/{damage_rasan_place} */
    public function destroy(Request $request, DamageRasanPlaces $damage_rasan_place)
    {
        $user = $request->user(); // 🔔

        return DB::transaction(function () use ($damage_rasan_place, $user) {

            // ✅ delete picture file (same style as SaeenFasad)
            $this->deletePictureIfStored($damage_rasan_place->picture);

            $damage_rasan_place->delete();

            // 🔔 Notification: DELETED
            NotificationService::record(
                $user,
                module: 'آسیب رسانې ساحې',
                action: 'deleted',
                title: 'ریکارډ حذف شو',
                message: 'ID: ' . ($damage_rasan_place->id ?? ''),
                routeName: null,
                routeParams: ['damage-rasan-places'],
                level: 'warning',
                changes: null
            );

            return response()->noContent();
        });
    }

    /** POST /damage-rasan-places/bulk-delete  { "ids": [1,2,3] } */
    public function bulkDelete(Request $request)
    {
        $user = $request->user(); // 🔔

        $ids = $request->input('ids', []);
        $ids = is_array($ids) ? array_values(array_filter($ids)) : [];

        return DB::transaction(function () use ($ids, $user) {
            if (empty($ids)) {
                return response()->noContent();
            }

            // ✅ delete pictures first
            $rows = DamageRasanPlaces::whereIn('id', $ids)->get(['id', 'picture']);
            foreach ($rows as $row) {
                $this->deletePictureIfStored($row->picture);
            }

            DamageRasanPlaces::whereIn('id', $ids)->delete();

            // 🔔 Notification: BULK DELETED
            NotificationService::record(
                $user,
                module: 'آسیب رسانې ساحې',
                action: 'deleted',
                title: 'حذف گروهی انجام شد',
                message: 'شمیر: ' . count($ids),
                routeName: null,
                routeParams: ['damage-rasan-places'],
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
        'district',
        'village',
        'specific_place',
        'specific_dot',
        'picture',
        'coordinates',
        'tahdid_type',
        'percentage',
        'damage_place',
        'Province_addr',
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
    $fiftySixFormatId = $request->query('fifty_six_format_id');
    $cardFilter       = trim((string) $request->query('card_filter', 'all'));

    $allowedFields = $this->printSearchFields();

    if ($search !== '' && $searchField === '') {
        $query->where(function ($q) use ($search) {
            $q->where('province', 'like', "%{$search}%")
                ->orWhere('zone', 'like', "%{$search}%")
                ->orWhere('district', 'like', "%{$search}%")
                ->orWhere('village', 'like', "%{$search}%")
                ->orWhere('specific_place', 'like', "%{$search}%")
                ->orWhere('specific_dot', 'like', "%{$search}%")
                ->orWhere('coordinates', 'like', "%{$search}%")
                ->orWhere('tahdid_type', 'like', "%{$search}%")
                ->orWhere('percentage', 'like', "%{$search}%")
                ->orWhere('damage_place', 'like', "%{$search}%")
                ->orWhere('Province_addr', 'like', "%{$search}%")
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

    if (is_numeric($fiftySixFormatId) && (int) $fiftySixFormatId > 0) {
        $query->where('id', (int) $fiftySixFormatId);
    }

    if ($cardFilter === 'withCoordinates') {
        $query->whereNotNull('coordinates')
            ->whereRaw("TRIM(coordinates) != ''");
    } elseif ($cardFilter === 'withThreat') {
        $query->whereNotNull('tahdid_type')
            ->whereRaw("TRIM(tahdid_type) != ''");
    } elseif ($cardFilter === 'withResource') {
        $query->whereNotNull('resource_id')
            ->whereRaw("TRIM(resource_id) != ''");
    }

    return $query;
}

public function printView(Request $request)
{
    $query = DamageRasanPlaces::query()
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
                ?? '-';

            // ✅ because your visible header uses resource_id for "کود منبع"
            $row->resource_id =
                $row->resource?->code_number
                ?? $row->resource_id
                ?? '-';

            return $row;
        });

    $selectedColumns = $request->input('selected_columns', []);
    $columnLabels    = $request->input('column_labels', []);
    $printLabels     = $request->input('print_labels', []);
    $locale          = $request->input('locale', 'fa');
    $title           = $request->input('title', 'چاپ نقاط آسیب رسان');

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
    $damageRasanPlace = DamageRasanPlaces::findOrFail($id);

    return $this->removePictureFileAndClear($damageRasanPlace);
}

public function exportExcel(Request $request)
{
    $search = trim((string) $request->get('search', ''));
    $searchField = $request->get('search_field');
    $searchValue = $request->get('search_value');
    $cardFilter = trim((string) $request->get('card_filter', 'all'));

    $fileName = 'damage_rasan_places_' . now()->format('Y_m_d_H_i_s') . '.xlsx';

    return Excel::download(
        new DamageRasanPlacesExport([
            'search' => $search, 'search_field' => $searchField,
            'search_value' => $searchValue, 'card_filter' => $cardFilter,
            'resource_id' => $request->get('resource_id'),
        ]),
        $fileName
    );
}
}