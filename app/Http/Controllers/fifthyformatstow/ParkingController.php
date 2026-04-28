<?php

namespace App\Http\Controllers\fifthyformatstow;

use App\Http\Controllers\Controller;
use App\Http\Requests\fifthyformatstow\StoreParkingRequest;
use App\Http\Requests\fifthyformatstow\UpdateParkingRequest;
use App\Http\Resources\fifthyformatstow\ParkingResource;
use App\Models\fifthyformatstow\Parking;
use App\Services\NotificationService; // 🔔 notifications
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ParkingController extends Controller
{
    private function duplicateErrorPashto(): array
    {
        return [
            'message' => 'دا پارکینګ مخکې ثبت شوی دی. مهرباني وکړئ تکراري معلومات مه داخلوی.',
            'errors'  => [
                'duplicate' => [
                    'دا پارکینګ مخکې ثبت شوی دی (نوم/ډول، مسؤل معلومات او پته یو شان دي).',
                ],
            ],
        ];
    }

    private function isDuplicateKey(QueryException $e): bool
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

    public function index(Request $request)
    {
        $page    = $request->query('page');
        $perPage = $request->query('perPage', $request->query('per_page', $request->query('itemsPerPage')));

        $isValidPage    = is_numeric($page) && (int) $page > 0;
        $isValidPerPage = is_numeric($perPage) && (int) $perPage > 0;

        $search      = trim((string) $request->query('search', ''));
        $rid         = $request->input('resource_id');
        $searchField = trim((string) $request->query('search_field', ''));
        $searchValue = trim((string) $request->query('search_value', ''));

        $allowedSearchFields = [
            
         'parking_name',
        'parking_type',
        'parking_type_pe',

        'responsible_name',
        'responsible_f_name',
        'responsible_position',
        'responsible_phone',

        'province',
        'picture',
        'zone',
        'district',
        'Province_addr',
        'village',
        'specific_place',
        'coordinates',

        'daily_cars_parking_quantity',
        'note',
        'resource_id',
        ];

        $sortBy  = $request->input('sort_by', 'created_at');
        $sortDir = in_array($request->input('sort_dir'), ['asc', 'desc'], true)
            ? $request->input('sort_dir')
            : 'desc';

        $q = Parking::query()->with([
            'resource' => fn ($qr) => $qr->select('id', 'name', 'code_number', 'phone_number'),
        ]);

        if ($search !== '' && $searchField === '') {
            $q->where(function ($qq) use ($search) {
                $qq->where('parking_name', 'like', "%{$search}%")
                   ->orWhere('responsible_name', 'like', "%{$search}%")
                   ->orWhere('responsible_phone', 'like', "%{$search}%")
                   ->orWhere('province', 'like', "%{$search}%")
                   ->orWhere('district', 'like', "%{$search}%");
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

        if ($isValidPage && $isValidPerPage) {
            return ParkingResource::collection(
                $q->paginate((int) $perPage, ['*'], 'page', (int) $page)
            );
        }

        return ParkingResource::collection($q->get());
    }

    /**
     * GET /parkings/filter-options
     */
    public function filterOptions(Request $request)
    {
        $field = trim((string) $request->query('field', ''));
        $q = trim((string) $request->query('q', ''));

        $allowedFields = [
            
         'parking_name',
        'parking_type',
        'parking_type_pe',

        'responsible_name',
        'responsible_f_name',
        'responsible_position',
        'responsible_phone',

        'province',
        'picture',
        'zone',
        'district',
        'Province_addr',
        'village',
        'specific_place',
        'coordinates',

        'daily_cars_parking_quantity',
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

        $query = Parking::query()
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
     * POST /parkings
     * Accepts:
     * - picture as FILE or STRING
     */
    public function store(StoreParkingRequest $request)
    {
        $user = $request->user(); // 🔔
        $data = $request->validated();

        try {
            return DB::transaction(function () use ($request, $data, $user) {

                // ✅ picture: file OR string (same style as SaeenFasad)
                if ($request->hasFile('picture')) {
                    $path = $request->file('picture')->store('uploads/parkings', 'public');
                    $data['picture'] = "storage/{$path}";
                } else {
                    if (array_key_exists('picture', $data) && !is_string($data['picture'])) {
                        unset($data['picture']);
                    }
                }

                $parking = Parking::create($data);

                $parking->load([
                    'resource' => fn ($qr) => $qr->select('id', 'name', 'code_number', 'phone_number'),
                ]);

                NotificationService::record(
                    $user,
                    module: 'پارکینګ',
                    action: 'created',
                    title: 'پارکینګ جدید  شوو',
                    message: ' پارکینګ: ' . ($parking->parking_name ?? ''),
                    routeName: 'parkings', // ⚠️ adjust to your real route name
                    routeParams: ['id' => $parking->id],
                    level: 'success',
                    changes: null
                );

                return (new ParkingResource($parking))
                    ->additional(['message' => 'Created'])
                    ->response()
                    ->setStatusCode(201);
            });
        } catch (QueryException $e) {
            if ($this->isDuplicateKey($e)) {
                return response()->json($this->duplicateErrorPashto(), 422);
            }
            throw $e;
        }
    }

    public function show(Parking $parking)
    {
        $parking->load([
            'resource' => fn ($qr) => $qr->select('id', 'name', 'code_number', 'phone_number'),
        ]);

        return ParkingResource::make($parking);
    }

    /**
     * PUT /parkings/{id}
     * Accepts:
     * - picture as FILE or STRING (replaces old file if new uploaded)
     */
    public function update(UpdateParkingRequest $request, Parking $parking)
    {
        $user = $request->user(); // 🔔
        $validated = $request->validated();

        try {
            return DB::transaction(function () use ($request, $validated, $parking, $user) {

                // 🧮 changes BEFORE update
                $original = $parking->getOriginal();
                $changes  = [];

                foreach ($validated as $field => $newValue) {
                    if (array_key_exists($field, $original) && $original[$field] != $newValue) {
                        $changes[$field] = [
                            'old' => $original[$field],
                            'new' => $newValue,
                        ];
                    }
                }

                // ✅ picture replace (same style as SaeenFasad)
                if ($request->hasFile('picture')) {
                    $this->deletePictureIfStored($parking->picture);

                    $path = $request->file('picture')->store('uploads/parkings', 'public');
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

                $parking->update($validated);

                $parking->load([
                    'resource' => fn ($qr) => $qr->select('id', 'name', 'code_number', 'phone_number'),
                ]);

                NotificationService::record(
                    $user,
                    module: 'پارکینګ',
                    action: 'updated',
                    title: 'پارکینګ اصلاح شوو',
                    message: ' پارکینګ: ' . ($parking->parking_name ?? ''),
                    routeName: 'parkings',
                    routeParams: ['id' => $parking->id],
                    level: 'info',
                    changes: !empty($changes) ? $changes : null
                );

                return ParkingResource::make($parking);
            });
        } catch (QueryException $e) {
            if ($this->isDuplicateKey($e)) {
                return response()->json($this->duplicateErrorPashto(), 422);
            }
            throw $e;
        }
    }

    public function destroy(Request $request, Parking $parking)
    {
        $user = $request->user(); // 🔔

        return DB::transaction(function () use ($parking, $user) {
            $name = $parking->parking_name;

            // ✅ delete picture file
            $this->deletePictureIfStored($parking->picture);

            $parking->delete();

            NotificationService::record(
                $user,
                module: 'پارکینګ',
                action: 'deleted',
                title: 'پارکینګ حذف شوو',
                message: ' پارکینګ: ' . ($name ?? ''),
                routeName: null,
                routeParams: ['parkings'],
                level: 'warning',
                changes: null
            );

            return response()->noContent();
        });
    }

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
            $rows = Parking::whereIn('id', $ids)->get(['id', 'picture']);
            foreach ($rows as $row) {
                $this->deletePictureIfStored($row->picture);
            }

            Parking::whereIn('id', $ids)->delete();

            NotificationService::record(
                $user,
                module: 'پارکینګ',
                action: 'deleted',
                title: 'پارکینګ‌ها به‌صورت گروهی حذف شوو',
                message: 'تعداد: ' . count($ids),
                routeName: null,
                routeParams: ['parkings'],
                level: 'warning',
                changes: null
            );

            return response()->noContent();
        });
    }
    private function printSearchFields(): array
{
    return [
        'parking_name',
        'parking_type',
        'parking_type_pe',

        'responsible_name',
        'responsible_f_name',
        'responsible_position',
        'responsible_phone',

        'province',
        'picture',
        'zone',
        'district',
        'Province_addr',
        'village',
        'specific_place',
        'coordinates',

        'daily_cars_parking_quantity',
        'note',
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

    $sortBy = trim((string) $request->query('sort_by', 'created_at'));
    $sortDir = in_array($request->query('sort_dir'), ['asc', 'desc'], true)
        ? $request->query('sort_dir')
        : 'desc';

    $allowedFields = $this->printSearchFields();

    if ($search !== '' && $searchField === '') {
        $query->where(function ($qq) use ($search) {
            $qq->where('parking_name', 'like', "%{$search}%")
                ->orWhere('parking_type', 'like', "%{$search}%")
                ->orWhere('parking_type_pe', 'like', "%{$search}%")
                ->orWhere('responsible_name', 'like', "%{$search}%")
                ->orWhere('responsible_f_name', 'like', "%{$search}%")
                ->orWhere('responsible_position', 'like', "%{$search}%")
                ->orWhere('responsible_phone', 'like', "%{$search}%")
                ->orWhere('province', 'like', "%{$search}%")
                ->orWhere('zone', 'like', "%{$search}%")
                ->orWhere('district', 'like', "%{$search}%")
                ->orWhere('Province_addr', 'like', "%{$search}%")
                ->orWhere('village', 'like', "%{$search}%")
                ->orWhere('specific_place', 'like', "%{$search}%")
                ->orWhere('coordinates', 'like', "%{$search}%")
                ->orWhere('daily_cars_parking_quantity', 'like', "%{$search}%")
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
    } elseif ($cardFilter === 'withPhone') {
        $query->whereNotNull('responsible_phone')
            ->whereRaw("TRIM(responsible_phone) != ''");
    } elseif ($cardFilter === 'withCapacity') {
        $query->whereNotNull('daily_cars_parking_quantity')
            ->whereRaw("TRIM(daily_cars_parking_quantity) != ''")
            ->where('daily_cars_parking_quantity', '>', 0);
    }

    return [
        'query' => $query,
        'sort_by' => $sortBy,
        'sort_dir' => $sortDir,
    ];
}

public function printView(Request $request)
{
    $query = Parking::query()->with([
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
    $title           = $request->input('title', 'چاپ پارکینگ‌ها');

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
    $parking = Parking::findOrFail($id);

    return $this->removePictureFileAndClear($parking);
}  
}