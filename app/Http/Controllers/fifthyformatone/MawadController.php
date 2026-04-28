<?php

namespace App\Http\Controllers\fifthyformatone;

use App\Http\Controllers\Controller;
use App\Http\Requests\fifthyformatone\StoreMawadRequest;
use App\Http\Requests\fifthyformatone\UpdateMawadRequest;
use App\Http\Resources\fifthyformatone\MawadResource;
use App\Models\fifthyformatone\Mawad;
use App\Services\NotificationService; // 🔔 notifications
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class MawadController extends Controller
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
                    'دا مخدره مواد پلورونکي مخکې ثبت شوی دی (نوع، نوم، د پلار نوم او اصلي پته).',
                ],
            ],
        ];
    }

    private function isDuplicateKeyException(QueryException $e): bool
    {
        return (int) ($e->errorInfo[1] ?? 0) === 1062;
    }

    /**
     * Searchable columns for selected-column filter
     */
    private function allowedSearchFields(): array
    {
        $table = (new Mawad())->getTable();

        $candidates = [
           'information_id',

        'mawad_type',
        'nickname',
        'name',
        'father_name',
        'picture',

        'main_province',
        'main_district',
        'main_village',

        'now_province',
        'now_district',
        'Province_addr',
        'now_village',

        'province',
        'zone',
        'district',
        'village',

        'specific_place',
        'coordinates',
        'mawad_sales_phone',

        'history_form',
        'supplier',
        'note',

        'resource_id',
        ];

        return array_values(array_filter($candidates, fn ($field) => Schema::hasColumn($table, $field)));
    }

    /**
     * GET /mawads
     */
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

        $allowedSearchFields = $this->allowedSearchFields();

        $q = Mawad::query()->with([
            'resource' => fn ($qr) => $qr->select('id', 'name', 'code_number', 'phone_number'),
        ]);

        if ($search !== '' && $searchField === '') {
            $q->where('name', 'like', "%{$search}%");
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

        return MawadResource::collection($q->paginate($perPage));
    }

    /**
     * GET /mawads/filter-options
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

        $query = Mawad::query()
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
     * POST /mawads
     */
    public function store(StoreMawadRequest $request)
    {
        $user = $request->user();

        try {
            return DB::transaction(function () use ($request, $user) {
                $data = $request->validated();

                // ✅ STORE picture properly (public disk)
                if ($request->hasFile('picture')) {
                    $data['picture'] = $request->file('picture')->store('mawads', 'public');
                }

                $mawad = Mawad::create($data);

                $mawad->load([
                    'resource' => fn ($qr) => $qr->select('id', 'name', 'code_number', 'phone_number'),
                ]);

                NotificationService::record(
                    $user,
                    module: 'مخدره مواد پلورونکي',
                    action: 'created',
                    title: 'مخدره مواد پلورونکي ثبت شوو',
                    message: 'نوم مخدره مواد پلورونکي: ' . ($mawad->name ?? ''),
                    routeName: 'mawads',
                    routeParams: ['id' => $mawad->id],
                    level: 'success',
                    changes: null
                );

                return (new MawadResource($mawad))
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
     * GET /mawads/{mawad}
     */
    public function show(Mawad $mawad)
    {
        $mawad->load([
            'resource' => fn ($qr) => $qr->select('id', 'name', 'code_number', 'phone_number'),
        ]);

        return MawadResource::make($mawad);
    }

    /**
     * PUT /mawads/{mawad}
     */
    public function update(UpdateMawadRequest $request, Mawad $mawad)
    {
        $user = $request->user();

        try {
            return DB::transaction(function () use ($request, $mawad, $user) {
                $validated = $request->validated();

                // 🧮 changes BEFORE update
                $original = $mawad->getOriginal();
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

                // ✅ UPDATE picture properly + delete old
                if ($request->hasFile('picture')) {
                    if ($mawad->picture) {
                        Storage::disk('public')->delete($mawad->picture);
                    }
                    $validated['picture'] = $request->file('picture')->store('mawads', 'public');
                    $changes['picture'] = ['old' => $original['picture'] ?? null, 'new' => $validated['picture']];
                }

                $mawad->update($validated);

                $mawad->load([
                    'resource' => fn ($qr) => $qr->select('id', 'name', 'code_number', 'phone_number'),
                ]);

                NotificationService::record(
                    $user,
                    module: 'مخدره مواد پلورونکي',
                    action: 'updated',
                    title: 'مخدره مواد پلورونکي اصلاح شوو',
                    message: 'نوم مخدره مواد پلورونکي: ' . ($mawad->name ?? ''),
                    routeName: 'mawads',
                    routeParams: ['id' => $mawad->id],
                    level: 'info',
                    changes: !empty($changes) ? $changes : null
                );

                return MawadResource::make($mawad);
            });
        } catch (QueryException $e) {
            if ($this->isDuplicateKeyException($e)) {
                return response()->json($this->duplicateErrorPashto(), 422);
            }
            throw $e;
        }
    }

    /**
     * DELETE /mawads/{mawad}
     */
    public function destroy(Request $request, Mawad $mawad)
    {
        $user = $request->user();

        return DB::transaction(function () use ($mawad, $user) {
            $name = $mawad->name;

            // ✅ delete picture file too
            if ($mawad->picture) {
                Storage::disk('public')->delete($mawad->picture);
            }

            $mawad->delete();

            NotificationService::record(
                $user,
                module: 'مخدره مواد پلورونکي',
                action: 'deleted',
                title: 'مخدره مواد پلورونکي حذف شوو',
                message: 'نوم مخدره مواد پلورونکي: ' . ($name ?? ''),
                routeName: null,
                routeParams: ['mawads'],
                level: 'warning',
                changes: null
            );

            return response()->noContent();
        });
    }

    /**
     * Optional bulk delete:
     * POST /mawads/bulk-delete  with { "ids": [1,2,3] }
     */
    public function bulkDelete(Request $request)
    {
        $user = $request->user();

        $ids = $request->input('ids', []);
        $ids = is_array($ids) ? array_values(array_filter($ids)) : [];

        return DB::transaction(function () use ($ids, $user) {
            if (empty($ids)) {
                return response()->noContent();
            }

            // ✅ delete pictures for all selected
            $pics = Mawad::whereIn('id', $ids)->pluck('picture')->filter()->values()->all();
            foreach ($pics as $p) {
                Storage::disk('public')->delete($p);
            }

            Mawad::whereIn('id', $ids)->delete();

            NotificationService::record(
                $user,
                module: 'مخدره مواد پلورونکي',
                action: 'deleted',
                title: 'مخدره مواد پلورونکي به‌صورت گروهی حذف شوو',
                message: 'تعداد: ' . count($ids),
                routeName: null,
                routeParams: ['mawads'],
                level: 'warning',
                changes: null
            );

            return response()->noContent();
        });
    }
    private function printSearchFields(): array
{
    return $this->allowedSearchFields();
}

private function applyPrintFilters($query, Request $request)
{
    $search      = trim((string) $request->query('search', ''));
    $searchField = trim((string) $request->query('search_field', ''));
    $searchValue = trim((string) $request->query('search_value', ''));

    $resourceId = $request->query('resource_id');
    $cardFilter = trim((string) $request->query('card_filter', 'all'));

    $allowedFields = $this->printSearchFields();

    // Same broad search as index
    if ($search !== '' && $searchField === '') {
        $query->where('name', 'like', "%{$search}%");
    }

    // Selected-column search
    if ($searchField !== '' && in_array($searchField, $allowedFields, true)) {
        $value = $searchValue !== '' ? $searchValue : $search;

        if ($value !== '') {
            $query->where($searchField, 'like', "%{$value}%");
        }
    }

    // Related resource filter from map/resource page
    if ($resourceId) {
        $query->where('resource_id', $resourceId);
    }

    // Card filters
    if ($cardFilter === 'withCoordinates') {
        $query->whereNotNull('coordinates')
            ->whereRaw("TRIM(coordinates) != ''");
    } elseif ($cardFilter === 'withSalePhone') {
        $query->whereNotNull('mawad_sales_phone')
            ->whereRaw("TRIM(mawad_sales_phone) != ''");
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

    // Remove time if value already comes as string date-time
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
    $query = Mawad::query()
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

            // If later you add these date columns to print headers,
            // they will print as Hijri Shamsi and without 00:00:00.
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
    $title           = $request->input('title', 'چاپ مواد');

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
    $mawad = Mawad::findOrFail($id);

    return $this->removePictureFileAndClear($mawad);
}
}