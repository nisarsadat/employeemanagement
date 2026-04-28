<?php

namespace App\Http\Controllers\fifthyformatone;

use App\Http\Controllers\Controller;
use App\Http\Requests\fifthyformatone\StoreMashkokShopsRequest;
use App\Http\Requests\fifthyformatone\UpdateMashkokShopsRequest;
use App\Http\Resources\fifthyformatone\MashkokShopsResource;
use App\Models\fifthyformatone\MashkokShops;
use App\Services\NotificationService; // 🔔 notifications
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class MashkokShopsController extends Controller
{
    private function duplicateErrorPashto(): array
    {
        return [
            'message' => 'دا ریکارډ مخکې ثبت شوی دی. مهرباني وکړئ تکراري معلومات مه داخلوی.',
            'errors'  => [
                'duplicate' => [
                    'دا دوکان/شخص مخکې ثبت شوی دی (نوم، د پلار نوم، اصلي ولایت، د تماس شمېره، د دوکان ډول او پته یو شان دي).',
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
        $table = (new MashkokShops())->getTable();

        $candidates = [
            'information_id',

        'province',
        'zone',
        'district',
        'village',
        'specific_place',
        'market_name',
        'shop_number',
        'picture',
        'coordinates',

        'name',
        'Province_addr',
        'father_name',
        'main_province',
        'phone_numbers',

        'shop_type',
        'mashkok_type',

        'note',
        'tafseel',
        'resource_id',
        ];

        return array_values(array_filter($candidates, fn ($field) => Schema::hasColumn($table, $field)));
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

    /** GET /mashkok-shops */
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

        $q = MashkokShops::query()->with([
            'resource' => fn ($qr) => $qr->select('id', 'name', 'code_number', 'phone_number'),
        ]);

        if ($search !== '' && $searchField === '') {
            $q->where(function ($w) use ($search) {
                $w->where('province', 'like', "%{$search}%")
                  ->orWhere('district', 'like', "%{$search}%")
                  ->orWhere('village', 'like', "%{$search}%")
                  ->orWhere('market_name', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%");
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

        return MashkokShopsResource::collection($q->paginate($perPage));
    }

    /**
     * GET /mashkok-shops/filter-options
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

        $query = MashkokShops::query()
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

    /** POST /mashkok-shops
     * Accepts:
     * - picture as FILE or STRING
     */
    public function store(StoreMashkokShopsRequest $request)
    {
        $user = $request->user(); // 🔔
        $data = $request->validated();

        try {
            return DB::transaction(function () use ($request, $data, $user) {

                // ✅ picture: file OR string (same style as SaeenFasad)
                if ($request->hasFile('picture')) {
                    $path = $request->file('picture')->store('uploads/mashkok-shops', 'public');
                    $data['picture'] = "storage/{$path}";
                } else {
                    if (array_key_exists('picture', $data) && !is_string($data['picture'])) {
                        unset($data['picture']);
                    }
                }

                $record = MashkokShops::create($data);

                $record->load([
                    'resource' => fn ($qr) => $qr->select('id', 'name', 'code_number', 'phone_number'),
                ]);

                NotificationService::record(
                    $user,
                    module: 'مشکوک دوکانونه',
                    action: 'created',
                    title: ' مشکوک دوکان ثبت شوو',
                    message: 'نوم: ' . ($record->name ?? '') . ' | مارکیټ نوم: ' . ($record->market_name ?? ''),
                    routeName: 'mashkok-shops', // ⚠️ set your real route name
                    routeParams: ['id' => $record->id],
                    level: 'success',
                    changes: null
                );

                return (new MashkokShopsResource($record))
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

    /** GET /mashkok-shops/{mashkok_shop} */
    public function show(MashkokShops $mashkok_shop)
    {
        $mashkok_shop->load([
            'resource' => fn ($qr) => $qr->select('id', 'name', 'code_number', 'phone_number'),
        ]);

        return MashkokShopsResource::make($mashkok_shop);
    }

    /** PUT /mashkok-shops/{mashkok_shop}
     * Accepts:
     * - picture as FILE or STRING (replaces old file if new uploaded)
     */
    public function update(UpdateMashkokShopsRequest $request, MashkokShops $mashkok_shop)
    {
        $user = $request->user(); // 🔔
        $validated = $request->validated();

        try {
            return DB::transaction(function () use ($request, $validated, $mashkok_shop, $user) {

                // 🧮 changes BEFORE update
                $original = $mashkok_shop->getOriginal();
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
                    $this->deletePictureIfStored($mashkok_shop->picture);

                    $path = $request->file('picture')->store('uploads/mashkok-shops', 'public');
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

                $mashkok_shop->update($validated);

                $mashkok_shop->load([
                    'resource' => fn ($qr) => $qr->select('id', 'name', 'code_number', 'phone_number'),
                ]);

                NotificationService::record(
                    $user,
                    module: 'مشکوک دوکانونه',
                    action: 'updated',
                    title: ' مشکوک  دوکاناصلاح شوو',
                    message: 'نوم: ' . ($mashkok_shop->name ?? '') . ' | مارکیټ نوم: ' . ($mashkok_shop->market_name ?? ''),
                    routeName: 'mashkok-shops', // ⚠️ set your real route name
                    routeParams: ['id' => $mashkok_shop->id],
                    level: 'info',
                    changes: !empty($changes) ? $changes : null
                );

                return MashkokShopsResource::make($mashkok_shop);
            });
        } catch (QueryException $e) {
            if ($this->isDuplicateKeyException($e)) {
                return response()->json($this->duplicateErrorPashto(), 422);
            }
            throw $e;
        }
    }

    /** DELETE /mashkok-shops/{mashkok_shop} */
    public function destroy(Request $request, MashkokShops $mashkok_shop)
    {
        $user = $request->user(); // 🔔

        return DB::transaction(function () use ($mashkok_shop, $user) {
            $name = $mashkok_shop->name;

            // ✅ delete picture file (same style as SaeenFasad destroy)
            $this->deletePictureIfStored($mashkok_shop->picture);

            $mashkok_shop->delete();

            NotificationService::record(
                $user,
                module: 'مشکوک دوکانونه',
                action: 'deleted',
                title: ' مشکوک دوکان حذف شوو',
                message: 'نوم: ' . ($name ?? ''),
                routeName: null,
                routeParams: ['mashkok-shops'],
                level: 'warning',
                changes: null
            );

            return response()->noContent();
        });
    }

    /** POST /mashkok-shops/bulk-delete  { "ids": [1,2,3] } */
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
            $rows = MashkokShops::whereIn('id', $ids)->get(['id', 'picture']);
            foreach ($rows as $row) {
                $this->deletePictureIfStored($row->picture);
            }

            MashkokShops::whereIn('id', $ids)->delete();

            NotificationService::record(
                $user,
                module: 'مشکوک دوکانونه',
                action: 'deleted',
                title: 'دوکان‌ها به‌صورت گروهی حذف شوو',
                message: 'تعداد: ' . count($ids),
                routeName: null,
                routeParams: ['mashkok-shops'],
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
    $fiftySixFormatId = $request->query('fifty_six_format_id');
    $cardFilter = trim((string) $request->query('card_filter', 'all'));

    $allowedFields = $this->printSearchFields();

    // Same broad search as index
    if ($search !== '' && $searchField === '') {
        $query->where(function ($w) use ($search) {
            $w->where('province', 'like', "%{$search}%")
                ->orWhere('district', 'like', "%{$search}%")
                ->orWhere('village', 'like', "%{$search}%")
                ->orWhere('market_name', 'like', "%{$search}%")
                ->orWhere('name', 'like', "%{$search}%");
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
    } elseif ($cardFilter === 'withPhone') {
        $query->whereNotNull('phone_numbers')
            ->whereRaw("TRIM(phone_numbers) != ''");
    } elseif ($cardFilter === 'withSuspicionType') {
        $query->whereNotNull('mashkok_type')
            ->whereRaw("TRIM(mashkok_type) != ''");
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
    $query = MashkokShops::query()
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
    $title           = $request->input('title', 'چاپ دکاکین مشکوک');

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