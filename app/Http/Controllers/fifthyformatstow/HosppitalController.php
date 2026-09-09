<?php

namespace App\Http\Controllers\fifthyformatstow;

use App\Http\Controllers\Controller;
use App\Http\Requests\fifthyformatstow\StoreHosppitalRequest;
use App\Http\Requests\fifthyformatstow\UpdateHosppitalRequest;
use App\Http\Resources\fifthyformatstow\HosppitalResource;
use App\Models\fifthyformatstow\Hosppital;
use App\Services\NotificationService; // 🔔 notifications
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\HosppitalExport;

class HosppitalController extends Controller
{
    private function isDuplicateKey(QueryException $e): bool
    {
        // MySQL duplicate key
        return (int) ($e->errorInfo[1] ?? 0) === 1062;
    }

    private function duplicateResponse()
    {
        return response()->json([
            'message' => 'دا ریکارډ مخکې ثبت شوی دی.',
            'errors'  => [
                'duplicate' => [
                    'د همدې معلوماتو سره (نوم، ډول/مشخصات، او ادرس) ریکارډ مخکې له مخکې موجود دی.',
                ],
            ],
        ], 422);
    }

    public function index(Request $request)
    {
        $page    = $request->query('page');
        $perPage = $request->query('perPage', $request->query('per_page', $request->query('itemsPerPage')));

        $isValidPage    = is_numeric($page) && (int) $page > 0;
        $isValidPerPage = is_numeric($perPage) && (int) $perPage > 0;

        $search      = trim((string) $request->query('search', ''));
        $searchField = trim((string) $request->query('search_field', ''));
        $searchValue = trim((string) $request->query('search_value', ''));

        $allowedSearchFields = [
           
         'name',
        'specific_past',   // as provided
        'hospita_type',    // as provided

        'responsible_name',
        'responsible_position',
        'responsible_phone',
        'responsible_f_name',

        'male_quantity',
        'picture',
        'female_quantity',
        'guard_quantity',

        'Province_addr',
        'province',
        'zone',
        'district',
        'village',
        'specific_place',
        'street_number',
        'coordinates',

        'note',
        'resource_id',
        ];

        $q = Hosppital::query()
            ->with(['resource:id,name,code_number,phone_number'])
            ->latest()
            ->when($search !== '' && $searchField === '', function ($sub) use ($search) {
                $sub->where(function ($qq) use ($search) {
                    $qq->where('name', 'like', "%{$search}%")
                       ->orWhere('hospita_type', 'like', "%{$search}%")
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
            );

        if ($isValidPage && $isValidPerPage) {
            return HosppitalResource::collection(
                $q->paginate((int) $perPage, ['*'], 'page', (int) $page)
            );
        }

        return HosppitalResource::collection($q->get());
    }

    /**
     * GET /hosppitals/filter-options
     */
    public function filterOptions(Request $request)
    {
        $field = trim((string) $request->query('field', ''));
        $q = trim((string) $request->query('q', ''));

        $allowedFields = [
           
         'name',
        'specific_past',   // as provided
        'hospita_type',    // as provided

        'responsible_name',
        'responsible_position',
        'responsible_phone',
        'responsible_f_name',

        'male_quantity',
        'picture',
        'female_quantity',
        'guard_quantity',

        'Province_addr',
        'province',
        'zone',
        'district',
        'village',
        'specific_place',
        'street_number',
        'coordinates',

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

        $query = Hosppital::query()
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

    public function store(StoreHosppitalRequest $request)
    {
        $user = $request->user(); // 🔔 who did this
        $data = $request->validated();

        try {
            // ✅ picture: file OR string (same style as SaeenFasad)
            if ($request->hasFile('picture')) {
                $path = $request->file('picture')->store('uploads/hosppitals', 'public');
                $data['picture'] = "storage/{$path}";
            } else {
                if (array_key_exists('picture', $data) && !is_string($data['picture'])) {
                    unset($data['picture']);
                }
            }

            $hospital = Hosppital::create($data)
                ->load(['resource:id,name,code_number,phone_number']);

            // 🔔 Notification: CREATED
            NotificationService::record(
                $user,
                module: 'شفاخانې او کلینیکونه',
                action: 'created',
                title: 'نوې شفاخانې او کلینیکونه ثبت شوه',
                message: 'نوم: ' . ($hospital->name ?? ''),
                routeName: 'hosppitals',           // ⚠️ set to your real route name
                routeParams: ['id' => $hospital->id],
                level: 'success',
                changes: null
            );

            return HosppitalResource::make($hospital);
        } catch (QueryException $e) {
            if ($this->isDuplicateKey($e)) {
                return $this->duplicateResponse();
            }
            throw $e;
        }
    }

    public function show(Hosppital $hosppital)
    {
        $hosppital->load(['resource:id,name,code_number,phone_number']);
        return HosppitalResource::make($hosppital);
    }

    public function update(UpdateHosppitalRequest $request, Hosppital $hosppital)
    {
        $user = $request->user(); // 🔔
        $data = $request->validated();

        try {
            // 🧮 build changes BEFORE update
            $original = $hosppital->getOriginal();
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
                if ($hosppital->picture && str_starts_with($hosppital->picture, 'storage/')) {
                    $old = str_replace('storage/', '', $hosppital->picture);
                    Storage::disk('public')->delete($old);
                }

                $path = $request->file('picture')->store('uploads/hosppitals', 'public');
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

            $hosppital->update($data);
            $hosppital->load(['resource:id,name,code_number,phone_number']);

            // 🔔 Notification: UPDATED
            NotificationService::record(
                $user,
                module: 'شفاخانې او کلینیکونه',
                action: 'updated',
                title: 'شفاخانې او کلینیکونه اصلاح شوه',
                message: 'نوم: ' . ($hosppital->name ?? ''),
                routeName: 'hosppitals',           // ⚠️ set to your real route name
                routeParams: ['id' => $hosppital->id],
                level: 'info',
                changes: !empty($changes) ? $changes : null
            );

            return HosppitalResource::make($hosppital);
        } catch (QueryException $e) {
            if ($this->isDuplicateKey($e)) {
                return $this->duplicateResponse();
            }
            throw $e;
        }
    }

    public function destroy(Request $request, Hosppital $hosppital)
    {
        $user = $request->user(); // 🔔
        $name = $hosppital->name;

        // ✅ delete picture file (same style as SaeenFasad)
        if ($hosppital->picture && str_starts_with($hosppital->picture, 'storage/')) {
            $old = str_replace('storage/', '', $hosppital->picture);
            Storage::disk('public')->delete($old);
        }

        $hosppital->delete();

        // 🔔 Notification: DELETED
        NotificationService::record(
            $user,
            module: 'شفاخانې او کلینیکونه',
            action: 'deleted',
            title: 'شفاخانې او کلینیکونه حذف شوه',
            message: 'نوم: ' . ($name ?? ''),
            routeName: null,
            routeParams: ['hosppitals'],
            level: 'warning',
            changes: null
        );

        return response()->noContent();
    }

    public function bulkDelete(Request $request)
    {
        $user = $request->user(); // 🔔
        $ids = $request->input('ids', []);

        // (kept your original logic — if you want file cleanup here too, tell me)
        Hosppital::destroy($ids);

        // 🔔 Notification: BULK DELETED
        NotificationService::record(
            $user,
            module: 'شفاخانې او کلینیکونه',
            action: 'bulk_deleted',
            title: 'ډله‌ییز حذف ترسره شو',
            message: 'حذف شوي ریکارډونه: ' . count($ids),
            routeName: null,
            routeParams: ['hosppitals'],
            level: 'warning',
            changes: null
        );

        return response()->noContent();
    }
    private function printSearchFields(): array
{
    return [
        'name',
        'specific_past',
        'hospita_type',

        'responsible_name',
        'responsible_position',
        'responsible_phone',
        'responsible_f_name',

        'male_quantity',
        'picture',
        'female_quantity',
        'guard_quantity',

        'Province_addr',
        'province',
        'zone',
        'district',
        'village',
        'specific_place',
        'street_number',
        'coordinates',

        'note',
        'resource_id',
    ];
}

private function applyPrintFilters($query, Request $request)
{
    $search           = trim((string) $request->query('search', ''));
    $searchField      = trim((string) $request->query('search_field', ''));
    $searchValue      = trim((string) $request->query('search_value', ''));
    $fiftySixFormatId = $request->query('fifty_six_format_id');
    $resourceId       = $request->query('resource_id');
    $cardFilter       = trim((string) $request->query('card_filter', 'all'));

    $allowedFields = $this->printSearchFields();

    // Same broad search as index
    if ($search !== '' && $searchField === '') {
        $query->where(function ($qq) use ($search) {
            $qq->where('name', 'like', "%{$search}%")
                ->orWhere('hospita_type', 'like', "%{$search}%")
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
    } elseif ($cardFilter === 'withStaff') {
        $query->where(function ($q) {
            $q->where('male_quantity', '>', 0)
                ->orWhere('female_quantity', '>', 0);
        });
    } elseif ($cardFilter === 'withGuards') {
        $query->where('guard_quantity', '>', 0);
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
    $query = Hosppital::query()
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
    $title           = $request->input('title', 'چاپ شفاخانه‌ها');

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
    $hosppital = Hosppital::findOrFail($id);

    return $this->removePictureFileAndClear($hosppital);
}

public function exportExcel(Request $request)
{
    $search = trim((string) $request->get('search', ''));
    $searchField = $request->get('search_field');
    $searchValue = $request->get('search_value');
    $cardFilter = trim((string) $request->get('card_filter', 'all'));

    $fileName = 'hosppitals_' . now()->format('Y_m_d_H_i_s') . '.xlsx';

    return Excel::download(
        new HosppitalExport([
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