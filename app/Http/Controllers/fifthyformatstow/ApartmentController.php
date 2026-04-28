<?php

namespace App\Http\Controllers\fifthyformatstow;

use App\Http\Controllers\Controller;
use App\Http\Requests\fifthyformatstow\StoreApartmentRequest;
use App\Http\Requests\fifthyformatstow\UpdateApartmentRequest;
use App\Http\Resources\fifthyformatstow\ApartmentResource;
use App\Models\fifthyformatstow\Apartment;
use App\Services\NotificationService; // 🔔 notifications
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\QueryException;

class ApartmentController extends Controller
{
    public function index(Request $request)
    {
        $page    = $request->query('page');
        $perPage = $request->query('perPage', $request->query('per_page'));

        $isValidPage    = is_numeric($page) && (int) $page > 0;
        $isValidPerPage = is_numeric($perPage) && (int) $perPage > 0;

        $search      = trim((string) $request->input('search', ''));
        $searchField = trim((string) $request->input('search_field', ''));
        $searchValue = trim((string) $request->input('search_value', ''));

        $allowedSearchFields = [
           
         'block_name',
        'apartments_quantity',
        'shahrak_name',
        'picture',

        'responsible_name',
        'responsible_f_name',
        'responsible_position',
        'responsible_phone',

        'province',
        'zone',
        'Province_addr',
        'district',
        'village',
        'specific_place',
        'coordinates',

        'guard_quantity',
        'note',
        'resource_id',
        ];

        $q = Apartment::query()
            ->with(['resource:id,name,code_number,phone_number'])
            ->when($search !== '' && $searchField === '', function ($sub) use ($search) {
                $sub->where(function ($qq) use ($search) {
                    $qq->where('block_name', 'like', "%{$search}%")
                        ->orWhere('shahrak_name', 'like', "%{$search}%")
                        ->orWhere('responsible_name', 'like', "%{$search}%")
                        ->orWhere('responsible_phone', 'like', "%{$search}%")
                        ->orWhere('province', 'like', "%{$search}%")
                        ->orWhere('district', 'like', "%{$search}%");
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
            ->latest();

        if ($isValidPage && $isValidPerPage) {
            return ApartmentResource::collection(
                $q->paginate((int) $perPage, ['*'], 'page', (int) $page)
            );
        }

        return ApartmentResource::collection($q->get());
    }

    /**
     * GET /apartments/filter-options
     */
    public function filterOptions(Request $request)
    {
        $field = trim((string) $request->query('field', ''));
        $q     = trim((string) $request->query('q', ''));

        $allowedFields = [
           
         'block_name',
        'apartments_quantity',
        'shahrak_name',
        'picture',

        'responsible_name',
        'responsible_f_name',
        'responsible_position',
        'responsible_phone',

        'province',
        'zone',
        'Province_addr',
        'district',
        'village',
        'specific_place',
        'coordinates',

        'guard_quantity',
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

        $query = Apartment::query()
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

    public function store(StoreApartmentRequest $request)
    {
        $user = $request->user(); // 🔔 who did this
        $data = $request->validated();

        try {
            return DB::transaction(function () use ($request, $user, $data) {

                // ✅ picture: file OR string (same style as SaeenFasad)
                if ($request->hasFile('picture')) {
                    $path = $request->file('picture')->store('uploads/apartments', 'public');
                    $data['picture'] = "storage/{$path}";
                } else {
                    if (array_key_exists('picture', $data) && !is_string($data['picture'])) {
                        unset($data['picture']);
                    }
                }

                $apartment = Apartment::create($data)
                    ->load(['resource:id,name,code_number,phone_number']);

                // 🔔 Notification: CREATED
                NotificationService::record(
                    $user,
                    module: 'رهایشي بلاکونه او اپارټمنټونه',
                    action: 'created',
                    title: 'رهایشي بلاکونه او اپارټمنټونه ثبت شوو',
                    message: 'د بلاک نوم: ' . ($apartment->block_name ?? ''),
                    routeName: 'apartments', // ⚠️ adjust if your route name differs
                    routeParams: ['id' => $apartment->id],
                    level: 'success',
                    changes: null
                );

                return ApartmentResource::make($apartment);
            });
        } catch (QueryException $e) {
            // ✅ Duplicate entry (MySQL error code 1062)
            if ((int) ($e->errorInfo[1] ?? 0) === 1062) {
                $field = $this->guessDuplicateField($e->getMessage());

                return response()->json([
                    'message' => 'Duplicate value. This field must be unique.',
                    'errors'  => [
                        $field => ['This value already exists. Please use another value.'],
                    ],
                ], 422);
            }

            throw $e;
        }
    }

    public function show(Apartment $apartment)
    {
        $apartment->load(['resource:id,name,code_number,phone_number']);
        return ApartmentResource::make($apartment);
    }

    public function update(UpdateApartmentRequest $request, Apartment $apartment)
    {
        $user = $request->user(); // 🔔
        $data = $request->validated();

        try {
            return DB::transaction(function () use ($request, $apartment, $user, $data) {
                // 🧮 build changes BEFORE update
                $original = $apartment->getOriginal();
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

                // ✅ picture: replace file if uploaded (same style as SaeenFasad)
                if ($request->hasFile('picture')) {
                    if ($apartment->picture && str_starts_with($apartment->picture, 'storage/')) {
                        $old = str_replace('storage/', '', $apartment->picture);
                        Storage::disk('public')->delete($old);
                    }

                    $path = $request->file('picture')->store('uploads/apartments', 'public');
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

                $apartment->update($data);
                $apartment->load(['resource:id,name,code_number,phone_number']);

                // 🔔 Notification: UPDATED + changes
                NotificationService::record(
                    $user,
                    module: 'رهایشي بلاکونه او اپارټمنټونه',
                    action: 'updated',
                    title: 'رهایشي بلاکونه او اپارټمنټونه اصلاح شوو',
                    message: 'د بلاک نوم: ' . ($apartment->block_name ?? ''),
                    routeName: 'apartments', // ⚠️ adjust if needed
                    routeParams: ['id' => $apartment->id],
                    level: 'info',
                    changes: !empty($changes) ? $changes : null
                );

                return ApartmentResource::make($apartment);
            });
        } catch (QueryException $e) {
            if ((int) ($e->errorInfo[1] ?? 0) === 1062) {
                $field = $this->guessDuplicateField($e->getMessage());

                return response()->json([
                    'message' => 'Duplicate value. This field must be unique.',
                    'errors'  => [
                        $field => ['This value already exists. Please use another value.'],
                    ],
                ], 422);
            }

            throw $e;
        }
    }

    public function destroy(Request $request, Apartment $apartment)
    {
        $user = $request->user(); // 🔔

        return DB::transaction(function () use ($apartment, $user) {
            $blockName = $apartment->block_name;

            // ✅ delete picture file (same style as SaeenFasad)
            if ($apartment->picture && str_starts_with($apartment->picture, 'storage/')) {
                $old = str_replace('storage/', '', $apartment->picture);
                Storage::disk('public')->delete($old);
            }

            $apartment->delete();

            // 🔔 Notification: DELETED
            NotificationService::record(
                $user,
                module: 'رهایشي بلاکونه او اپارټمنټونه',
                action: 'deleted',
                title: 'رهایشي بلاکونه او اپارټمنټونه حذف شوو',
                message: 'د بلاک نوم: ' . ($blockName ?? ''),
                routeName: null,
                routeParams: ['apartments'],
                level: 'warning',
                changes: null
            );

            return response()->noContent();
        });
    }

    public function bulkDelete(Request $request)
    {
        $ids = $request->input('ids', []);

        // Optional: if you want to delete picture files for bulk delete,
        // you must loop and delete each picture first (like SaeenFasad).
        // (I kept your original logic untouched.)
        Apartment::destroy($ids);

        return response()->noContent();
    }

    /**
     * Try to detect which field caused the UNIQUE constraint error.
     */
    private function guessDuplicateField(string $errorMessage): string
    {
        $map = [
            'apartments_block_name_unique' => 'block_name',
            'apartments_apartments_quantity_unique' => 'apartments_quantity',
            'apartments_shahrak_name_unique' => 'shahrak_name',

            'apartments_responsible_name_unique' => 'responsible_name',
            'apartments_responsible_f_name_unique' => 'responsible_f_name',
            'apartments_responsible_position_unique' => 'responsible_position',
            'apartments_responsible_phone_unique' => 'responsible_phone',

            'apartments_province_unique' => 'province',
            'apartments_zone_unique' => 'zone',
            'apartments_district_unique' => 'district',
        ];

        foreach ($map as $indexName => $field) {
            if (str_contains($errorMessage, $indexName)) {
                return $field;
            }
        }

        return 'block_name'; // safe fallback
    }
    private function printSearchFields(): array
{
    return [
        'block_name',
        'apartments_quantity',
        'shahrak_name',
        'picture',

        'responsible_name',
        'responsible_f_name',
        'responsible_position',
        'responsible_phone',

        'province',
        'zone',
        'Province_addr',
        'district',
        'village',
        'specific_place',
        'coordinates',

        'guard_quantity',
        'note',
        'resource_id',
    ];
}

private function applyPrintFilters($query, Request $request)
{
    $search      = trim((string) $request->query('search', ''));
    $searchField = trim((string) $request->query('search_field', ''));
    $searchValue = trim((string) $request->query('search_value', ''));

    $resourceId = $request->query('resource_id');
    $fiftySixFormatId = $request->query('fifty_six_format_id');
    $cardFilter = trim((string) $request->query('card_filter', 'all'));

    $allowedFields = $this->printSearchFields();

    // Same broad search as index
    if ($search !== '' && $searchField === '') {
        $query->where(function ($qq) use ($search) {
            $qq->where('block_name', 'like', "%{$search}%")
                ->orWhere('shahrak_name', 'like', "%{$search}%")
                ->orWhere('responsible_name', 'like', "%{$search}%")
                ->orWhere('responsible_phone', 'like', "%{$search}%")
                ->orWhere('province', 'like', "%{$search}%")
                ->orWhere('district', 'like', "%{$search}%");
        });
    }

    // Selected-column search
    if ($searchField !== '' && in_array($searchField, $allowedFields, true)) {
        $value = $searchValue !== '' ? $searchValue : $search;

        if ($value !== '') {
            $query->where($searchField, 'like', "%{$value}%");
        }
    }

    // Related resource filter
    if ($resourceId) {
        $query->where('resource_id', $resourceId);
    }

    // Related route/map filter
    if (is_numeric($fiftySixFormatId) && (int) $fiftySixFormatId > 0) {
        $query->where('id', (int) $fiftySixFormatId);
    }

    // Card filters
    if ($cardFilter === 'withCoordinates') {
        $query->whereNotNull('coordinates')
            ->whereRaw("TRIM(coordinates) != ''");
    } elseif ($cardFilter === 'withApartments') {
        $query->whereNotNull('apartments_quantity')
            ->where('apartments_quantity', '>', 0);
    } elseif ($cardFilter === 'withGuards') {
        $query->whereNotNull('guard_quantity')
            ->where('guard_quantity', '>', 0);
    }

    return $query;
}

private function cleanPrintDate($value): ?string
{
    if (!$value) {
        return null;
    }

    $value = trim((string) $value);

    if ($value === '') {
        return null;
    }

    $value = preg_replace('/\s+00:00:00$/', '', $value);
    $value = preg_replace('/T00:00:00(\.000000Z)?$/', '', $value);

    return $value;
}

private function gregorianToJalali($gy, $gm, $gd): array
{
    $g_d_m = [0, 31, 59, 90, 120, 151, 181, 212, 243, 273, 304, 334];

    if ($gy > 1600) {
        $jy = 979;
        $gy -= 1600;
    } else {
        $jy = 0;
        $gy -= 621;
    }

    $gy2 = ($gm > 2) ? ($gy + 1) : $gy;

    $days = (365 * $gy)
        + intdiv($gy2 + 3, 4)
        - intdiv($gy2 + 99, 100)
        + intdiv($gy2 + 399, 400)
        - 80
        + $gd
        + $g_d_m[$gm - 1];

    $jy += 33 * intdiv($days, 12053);
    $days %= 12053;

    $jy += 4 * intdiv($days, 1461);
    $days %= 1461;

    if ($days > 365) {
        $jy += intdiv($days - 1, 365);
        $days = ($days - 1) % 365;
    }

    if ($days < 186) {
        $jm = 1 + intdiv($days, 31);
        $jd = 1 + ($days % 31);
    } else {
        $jm = 7 + intdiv($days - 186, 30);
        $jd = 1 + (($days - 186) % 30);
    }

    return [$jy, $jm, $jd];
}

private function toShamsiDate($value): ?string
{
    if (!$value) {
        return null;
    }

    try {
        $date = \Carbon\Carbon::parse($value);

        [$jy, $jm, $jd] = $this->gregorianToJalali(
            (int) $date->format('Y'),
            (int) $date->format('m'),
            (int) $date->format('d')
        );

        return sprintf('%04d/%02d/%02d', $jy, $jm, $jd);
    } catch (\Throwable $e) {
        return $this->cleanPrintDate($value);
    }
}

public function printView(Request $request)
{
    $query = Apartment::query()
        ->with([
            'resource:id,name,code_number,phone_number',
        ]);

    $this->applyPrintFilters($query, $request);

    $rows = $query
        ->latest()
        ->get()
        ->map(function ($row) {
            // Relation display fields for print
            $row->resource_code =
                $row->resource?->code_number
                ?? $row->resource_id
                ?? '-';

            $row->resource_phone =
                $row->resource?->phone_number
                ?? '-';

            // Optional dates if selected later
            if (isset($row->created_at)) {
                $row->created_at = $this->toShamsiDate($row->created_at);
            }

            if (isset($row->updated_at)) {
                $row->updated_at = $this->toShamsiDate($row->updated_at);
            }

            return $row;
        });

    $selectedColumns = $request->input('selected_columns', []);
    $columnLabels    = $request->input('column_labels', []);
    $printLabels     = $request->input('print_labels', []);
    $locale          = $request->input('locale', 'fa');
    $title           = $request->input('title', 'چاپ اپارتمان‌ها');

    return response()->view('prints.table-print', [
        'title'           => $title,
        'rows'            => $rows,
        'selectedColumns' => $selectedColumns,
        'columnLabels'    => $columnLabels,
        'printLabels'     => $printLabels,
        'locale'          => $locale,
    ]);
}
}