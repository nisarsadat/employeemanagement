<?php

namespace App\Http\Controllers\fifthyformatstow;

use App\Http\Controllers\Controller;
use App\Http\Requests\fifthyformatstow\StoreTransportCompanyRequest;
use App\Http\Requests\fifthyformatstow\UpdateTransportCompanyRequest;
use App\Http\Resources\fifthyformatstow\TransportCompanyResource;
use App\Models\fifthyformatstow\TransportCompany;
use App\Services\NotificationService; // 🔔 notifications
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Exports\TransportCompanyExport;
use Maatwebsite\Excel\Facades\Excel;

class TransportCompanyController extends Controller
{
    private function duplicateErrorPashto(): array
    {
        return [
            'message' => 'دا ترانسپورټ شرکت مخکې ثبت شوی دی. مهرباني وکړئ تکراري معلومات مه داخلوی.',
            'errors'  => [
                'duplicate' => [
                    'دا شرکت مخکې ثبت شوی دی (د شرکت نوم/ډول، د مسؤل معلومات او پته یو شان دي).',
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
        $perPage = $request->query('perPage', $request->query('per_page'));

        $isValidPage    = is_numeric($page) && (int) $page > 0;
        $isValidPerPage = is_numeric($perPage) && (int) $perPage > 0;

        $search      = trim((string) $request->input('search', ''));
        $searchField = trim((string) $request->input('search_field', ''));
        $searchValue = trim((string) $request->input('search_value', ''));
        $rid         = $request->input('resource_id');

        $sortBy  = $request->input('sort_by', 'created_at');
        $sortDir = in_array($request->input('sort_dir'), ['asc', 'desc'], true)
            ? $request->input('sort_dir')
            : 'desc';

        $allowedSearchFields = [
           'transport_company_name',
        'type',
        'responsible_name',
        'responsible_f_name',
        'responsible_position',
        'responsible_phone',
        'province',
        'zone',
        'district',
        'village',
        'picture',
        'specific_place',
        'coordinate',
        'Province_addr',
        'ways',
        'note',
        'resource_id', // <— ensure this is present
        ];

        $q = TransportCompany::query()->with([
            'resource' => fn ($qr) => $qr->select('id', 'name', 'code_number', 'phone_number'),
        ]);

        if ($search !== '' && $searchField === '') {
            $q->where(function ($qq) use ($search) {
                $qq->where('transport_company_name', 'like', "%{$search}%")
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
            return TransportCompanyResource::collection(
                $q->paginate((int) $perPage, ['*'], 'page', (int) $page)
            );
        }

        return TransportCompanyResource::collection($q->get());
    }

    /**
     * GET /transport-companies/filter-options
     */
    public function filterOptions(Request $request)
    {
        $field = trim((string) $request->query('field', ''));
        $q     = trim((string) $request->query('q', ''));

        $allowedFields = [
           'transport_company_name',
        'type',
        'responsible_name',
        'responsible_f_name',
        'responsible_position',
        'responsible_phone',
        'province',
        'zone',
        'district',
        'village',
        'picture',
        'specific_place',
        'coordinate',
        'Province_addr',
        'ways',
        'note',
        'resource_id', // <— ensure this is present
        ];

        if ($field === '' || !in_array($field, $allowedFields, true)) {
            return response()->json([
                'data' => [
                    'options' => [],
                ],
            ]);
        }

        $query = TransportCompany::query()
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
     * POST /transport-companies
     * Accepts:
     * - picture as FILE or STRING
     */
    public function store(StoreTransportCompanyRequest $request)
    {
        $user = $request->user(); // 🔔
        $data = $request->validated();

        try {
            return DB::transaction(function () use ($request, $data, $user) {

                // ✅ picture: file OR string (same style as SaeenFasad)
                if ($request->hasFile('picture')) {
                    $path = $request->file('picture')->store('uploads/transport-companies', 'public');
                    $data['picture'] = "storage/{$path}";
                } else {
                    if (array_key_exists('picture', $data) && !is_string($data['picture'])) {
                        unset($data['picture']);
                    }
                }

                $company = TransportCompany::create($data);

                $company->load([
                    'resource' => fn ($qr) => $qr->select('id', 'name', 'code_number', 'phone_number'),
                ]);

                NotificationService::record(
                    $user,
                    module: 'ټرانسپورټي شرکتونه',
                    action: 'created',
                    title: 'شرکت جدید ثبت شوو',
                    message: 'نوم: ' . ($company->transport_company_name ?? ''),
                    routeName: 'transport-companies', // ⚠️ set your real route name
                    routeParams: ['id' => $company->id],
                    level: 'success',
                    changes: null
                );

                return (new TransportCompanyResource($company))
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

    public function show(TransportCompany $transportCompany)
    {
        $transportCompany->load([
            'resource' => fn ($qr) => $qr->select('id', 'name', 'code_number', 'phone_number'),
        ]);

        return TransportCompanyResource::make($transportCompany);
    }

    /**
     * PUT /transport-companies/{id}
     * Accepts:
     * - picture as FILE or STRING (replaces old file if new uploaded)
     */
    public function update(UpdateTransportCompanyRequest $request, TransportCompany $transportCompany)
    {
        $user = $request->user(); // 🔔
        $validated = $request->validated();

        try {
            return DB::transaction(function () use ($request, $validated, $transportCompany, $user) {

                // 🧮 changes BEFORE update
                $original = $transportCompany->getOriginal();
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
                    $this->deletePictureIfStored($transportCompany->picture);

                    $path = $request->file('picture')->store('uploads/transport-companies', 'public');
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

                $transportCompany->update($validated);

                $transportCompany->load([
                    'resource' => fn ($qr) => $qr->select('id', 'name', 'code_number', 'phone_number'),
                ]);

                NotificationService::record(
                    $user,
                    module: 'ټرانسپورټي شرکتونه',
                    action: 'updated',
                    title: 'شرکت اصلاح شوو',
                    message: 'نوم: ' . ($transportCompany->transport_company_name ?? ''),
                    routeName: 'transport-companies',
                    routeParams: ['id' => $transportCompany->id],
                    level: 'info',
                    changes: !empty($changes) ? $changes : null
                );

                return TransportCompanyResource::make($transportCompany);
            });
        } catch (QueryException $e) {
            if ($this->isDuplicateKey($e)) {
                return response()->json($this->duplicateErrorPashto(), 422);
            }
            throw $e;
        }
    }

    public function destroy(Request $request, TransportCompany $transportCompany)
    {
        $user = $request->user(); // 🔔

        return DB::transaction(function () use ($transportCompany, $user) {
            $name = $transportCompany->transport_company_name;

            // ✅ delete picture file
            $this->deletePictureIfStored($transportCompany->picture);

            $transportCompany->delete();

            NotificationService::record(
                $user,
                module: 'ټرانسپورټي شرکتونه',
                action: 'deleted',
                title: 'شرکت حذف شوو',
                message: 'نوم: ' . ($name ?? ''),
                routeName: null,
                routeParams: ['transport-companies'],
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
            $rows = TransportCompany::whereIn('id', $ids)->get(['id', 'picture']);
            foreach ($rows as $row) {
                $this->deletePictureIfStored($row->picture);
            }

            TransportCompany::whereIn('id', $ids)->delete();

            NotificationService::record(
                $user,
                module: 'ټرانسپورټي شرکتونه',
                action: 'deleted',
                title: 'شرکت‌ها به‌صورت گروهی حذف شوو',
                message: 'تعداد: ' . count($ids),
                routeName: null,
                routeParams: ['transport-companies'],
                level: 'warning',
                changes: null
            );

            return response()->noContent();
        });
    }
    private function printSearchFields(): array
{
    return [
        'transport_company_name',
        'type',
        'responsible_name',
        'responsible_f_name',
        'responsible_position',
        'responsible_phone',
        'province',
        'zone',
        'district',
        'village',
        'picture',
        'specific_place',
        'coordinate',
        'Province_addr',
        'ways',
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
            $qq->where('transport_company_name', 'like', "%{$search}%")
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
        $query->whereNotNull('coordinate')
            ->whereRaw("TRIM(coordinate) != ''");
    } elseif ($cardFilter === 'withPhone') {
        $query->whereNotNull('responsible_phone')
            ->whereRaw("TRIM(responsible_phone) != ''");
    } elseif ($cardFilter === 'withResource') {
        $query->whereNotNull('resource_id');
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
    $query = TransportCompany::query()
        ->with([
            'resource' => fn ($qr) => $qr->select('id', 'name', 'code_number', 'phone_number'),
        ]);

    $this->applyPrintFilters($query, $request);

    $rows = $query
        ->orderByDesc('id')
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
    $title           = $request->input('title', 'چاپ شرکت‌های ترانسپورتی');

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
    $transportCompany = TransportCompany::findOrFail($id);

    return $this->removePictureFileAndClear($transportCompany);
}  

    public function exportExcel(Request $request)
    {
        $filters = $request->only(['search', 'search_field', 'search_value', 'resource_id']);
        return Excel::download(new TransportCompanyExport($filters), 'transport_company_' . now()->format('Y_m_d_His') . '.xlsx');
    }
}