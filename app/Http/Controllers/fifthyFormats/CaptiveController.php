<?php

namespace App\Http\Controllers\fifthyFormats;

use App\Http\Controllers\Controller;
use App\Http\Requests\fifthyFormats\StoreCaptiveRequest;
use App\Http\Requests\fifthyFormats\UpdateCaptiveRequest;
use App\Http\Resources\fifthyFormats\CaptiveResource;
use App\Models\fifthyFormats\Captive;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\QueryException;
use App\Services\NotificationService; // 🔔 notifications

class CaptiveController extends Controller
{
    private function allowedSearchFields(): array
    {
        $table = (new Captive())->getTable();

        $candidates = [
            'information_id',
        'name',
        'picture',
        'nick_name',
        'father_name',
        'main_province',
        'main_district',
        'main_village',
        'now_province',
        'now_zone',
        'now_district',
        'now_village',
        'specific_place',
        'street_number',
        'home_number',
        'specific_form',
        'band_distince',
        'now_activity',
        'phone_number',
        'note',
        'Province_addr',
        'coordinates',
        'resource_id',
        ];

        return array_values(array_filter($candidates, fn ($field) => Schema::hasColumn($table, $field)));
    }

    public function index(Request $request)
    {
        $page    = $request->query('page');
        $perPage = $request->query('perPage', $request->query('per_page'));

        $isValidPage    = is_numeric($page) && (int) $page > 0;
        $isValidPerPage = is_numeric($perPage) && (int) $perPage > 0;

        $search = trim((string) $request->query('search', ''));
        $rid    = $request->query('resource_id');
        $searchField = trim((string) $request->query('search_field', ''));
        $searchValue = trim((string) $request->query('search_value', ''));

        $sortable = ['id', 'name', 'created_at', 'updated_at'];
        $sortBy   = in_array($request->query('sort_by', 'created_at'), $sortable, true)
            ? $request->query('sort_by', 'created_at')
            : 'created_at';

        $sortDir  = strtolower((string) $request->query('sort_dir', 'desc'));
        if (!in_array($sortDir, ['asc', 'desc'], true)) {
            $sortDir = 'desc';
        }

        $allowedSearchFields = $this->allowedSearchFields();

        $q = Captive::query()
            ->leftJoin('resources', 'captives.resource_id', '=', 'resources.id')
            ->select([
                'captives.*',
                'resources.code_number as resource_code',
                'resources.phone_number as resource_phone_number',
            ])
            ->with([
                'resource:id,name,code_number,phone_number',
                'documents',
            ])
            ->when($search !== '' && $searchField === '', function ($sub) use ($search) {
                $sub->where(function ($x) use ($search) {
                    $x->where('captives.name', 'like', "%{$search}%")
                      ->orWhere('captives.father_name', 'like', "%{$search}%")
                      ->orWhere('captives.nick_name', 'like', "%{$search}%")
                      ->orWhere('captives.note', 'like', "%{$search}%");
                });
            })
            ->when(
                $searchField !== '' && in_array($searchField, $allowedSearchFields, true),
                function ($sub) use ($searchField, $searchValue, $search) {
                    $value = $searchValue !== '' ? $searchValue : $search;

                    if ($value !== '') {
                        $sub->where("captives.{$searchField}", 'like', "%{$value}%");
                    }
                }
            )
            ->when($rid, fn ($sub) => $sub->where('captives.resource_id', $rid))
            ->orderBy($sortBy, $sortDir);

        if ($isValidPage && $isValidPerPage) {
            return CaptiveResource::collection(
                $q->paginate((int) $perPage, ['*'], 'page', (int) $page)
            );
        }

        return CaptiveResource::collection($q->get());
    }

    /**
     * GET /captives/filter-options
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

        $query = Captive::query()
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

    public function show(Captive $captive)
    {
        $captive->load([
            'resource:id,name,code_number,phone_number',
            'documents',
        ]);

        return new CaptiveResource($captive);
    }

    public function store(StoreCaptiveRequest $request)
    {
        $user = $request->user(); // 🔔
        $data = $request->validated();

        try {
            return DB::transaction(function () use ($request, $data, $user) {

                // ✅ picture
                if ($request->hasFile('picture')) {
                    $path = $request->file('picture')->store('uploads/captives', 'public');
                    $data['picture'] = "storage/{$path}";
                } else {
                    if (array_key_exists('picture', $data) && !is_string($data['picture'])) {
                        unset($data['picture']);
                    }
                }

                $captive = Captive::create($data);

                // ✅ documents[]
                if ($request->hasFile('documents')) {
                    foreach ((array) $request->file('documents') as $file) {
                        if (!$file || !$file->isValid()) continue;

                        $docPath = $file->store("uploads/captive-documents/{$captive->id}", 'public');

                        $captive->documents()->create([
                            'file_name'  => $file->getClientOriginalName(),
                            'file_path'  => $docPath,
                            'mime_type'  => $file->getClientMimeType(),
                            'size_bytes' => $file->getSize(),
                        ]);
                    }
                }

                $captive->load([
                    'resource:id,name,code_number,phone_number',
                    'documents',
                ]);

                // 🔔 Notification: CREATED
                NotificationService::record(
                    $user,
                    module: 'راخوشي شوي بندیان',
                    action: 'created',
                    title: 'نوی ریکارډ ثبت شو',
                    message: 'نوم: ' . ($captive->name ?? ''),
                    routeName: 'captives',
                    routeParams: ['id' => $captive->id],
                    level: 'success',
                    changes: null
                );

                return (new CaptiveResource($captive))
                    ->additional(['message' => 'Created'])
                    ->response()
                    ->setStatusCode(201);
            });
        } catch (QueryException $e) {
            if ((int) ($e->errorInfo[1] ?? 0) === 1062) {
                return response()->json([
                    'message' => 'دا ریکارډ له مخکې نه موجود دی (تکراري ثبت اجازه نشته).',
                    'errors'  => [
                        'duplicate' => ['دا ریکارډ له مخکې نه موجود دی (تکراري ثبت اجازه نشته).'],
                    ],
                ], 422);
            }
            throw $e;
        }
    }

    public function update(UpdateCaptiveRequest $request, Captive $captive)
    {
        $user = $request->user(); // 🔔
        $data = $request->validated();

        try {
            return DB::transaction(function () use ($request, $data, $captive, $user) {

                // 🧮 changes BEFORE update
                $original = $captive->getOriginal();
                $changes  = [];

                foreach ($data as $field => $newValue) {
                    if (array_key_exists($field, $original) && $original[$field] != $newValue) {
                        $changes[$field] = [
                            'old' => $original[$field],
                            'new' => $newValue,
                        ];
                    }
                }

                // ✅ picture replace
                if ($request->hasFile('picture')) {
                    if ($captive->picture && str_starts_with($captive->picture, 'storage/')) {
                        Storage::disk('public')->delete(str_replace('storage/', '', $captive->picture));
                    }

                    $path = $request->file('picture')->store('uploads/captives', 'public');
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

                $captive->update($data);

                // ✅ delete selected old documents
                $removed = (array) $request->input('removed_document_ids', []);
                if (!empty($removed)) {
                    $docs = $captive->documents()->whereIn('id', $removed)->get();
                    foreach ($docs as $doc) {
                        if ($doc->file_path && Storage::disk('public')->exists($doc->file_path)) {
                            Storage::disk('public')->delete($doc->file_path);
                        }
                        $doc->delete();
                    }
                }

                // ✅ add new documents[]
                if ($request->hasFile('documents')) {
                    foreach ((array) $request->file('documents') as $file) {
                        if (!$file || !$file->isValid()) continue;

                        $docPath = $file->store("uploads/captive-documents/{$captive->id}", 'public');

                        $captive->documents()->create([
                            'file_name'  => $file->getClientOriginalName(),
                            'file_path'  => $docPath,
                            'mime_type'  => $file->getClientMimeType(),
                            'size_bytes' => $file->getSize(),
                        ]);
                    }
                }

                $captive->load([
                    'resource:id,name,code_number,phone_number',
                    'documents',
                ]);

                // 🔔 Notification: UPDATED
                NotificationService::record(
                    $user,
                    module: 'راخوشي شوي بندیان',
                    action: 'updated',
                    title: 'ریکارډ اصلاح شو',
                    message: 'نوم: ' . ($captive->name ?? ''),
                    routeName: 'captives',
                    routeParams: ['id' => $captive->id],
                    level: 'info',
                    changes: !empty($changes) ? $changes : null
                );

                return new CaptiveResource($captive);
            });
        } catch (QueryException $e) {
            if ((int) ($e->errorInfo[1] ?? 0) === 1062) {
                return response()->json([
                    'message' => 'دا ریکارډ له مخکې نه موجود دی (تکراري ثبت اجازه نشته).',
                    'errors'  => [
                        'duplicate' => ['دا ریکارډ له مخکې نه موجود دی (تکراري ثبت اجازه نشته).'],
                    ],
                ], 422);
            }
            throw $e;
        }
    }

    public function destroy(Request $request, Captive $captive)
    {
        $user = $request->user(); // 🔔

        return DB::transaction(function () use ($captive, $user) {

            // delete picture file if stored locally
            if ($captive->picture && str_starts_with($captive->picture, 'storage/')) {
                Storage::disk('public')->delete(str_replace('storage/', '', $captive->picture));
            }

            // delete docs files + rows (safe even if you already do it in model booted())
            $captive->load('documents');
            foreach ($captive->documents as $doc) {
                if ($doc->file_path && Storage::disk('public')->exists($doc->file_path)) {
                    Storage::disk('public')->delete($doc->file_path);
                }
                $doc->delete();
            }

            $name = $captive->name;
            $captive->delete();

            // 🔔 Notification: DELETED
            NotificationService::record(
                $user,
                module: 'راخوشي شوي بندیان',
                action: 'deleted',
                title: 'ریکارډ حذف شو',
                message: 'نوم: ' . ($name ?? ''),
                routeName: null,
                routeParams: ['captives'],
                level: 'warning',
                changes: null
            );

            return response()->noContent();
        });
    }
    private function printSearchFields(): array
{
    $table = (new Captive())->getTable();

    $candidates = [
        'information_id',
        'name',
        'picture',
        'nick_name',
        'father_name',
        'main_province',
        'main_district',
        'main_village',
        'now_province',
        'now_zone',
        'now_district',
        'now_village',
        'specific_place',
        'street_number',
        'home_number',
        'specific_form',
        'band_distince',
        'now_activity',
        'phone_number',
        'note',
        'Province_addr',
        'coordinates',
        'resource_id',

        // relation display fields
        'resource_code',
        'resource_phone_number',
    ];

    return array_values(array_filter($candidates, function ($field) use ($table) {
        return in_array($field, ['resource_code', 'resource_phone_number'], true)
            || Schema::hasColumn($table, $field);
    }));
}

private function gregorianToJalali(int $gy, int $gm, int $gd): array
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
        + intdiv(($gy2 + 3), 4)
        - intdiv(($gy2 + 99), 100)
        + intdiv(($gy2 + 399), 400)
        - 80
        + $gd
        + $g_d_m[$gm - 1];

    $jy += 33 * intdiv($days, 12053);
    $days %= 12053;

    $jy += 4 * intdiv($days, 1461);
    $days %= 1461;

    if ($days > 365) {
        $jy += intdiv(($days - 1), 365);
        $days = ($days - 1) % 365;
    }

    if ($days < 186) {
        $jm = 1 + intdiv($days, 31);
        $jd = 1 + ($days % 31);
    } else {
        $jm = 7 + intdiv(($days - 186), 30);
        $jd = 1 + (($days - 186) % 30);
    }

    return [$jy, $jm, $jd];
}

private function cleanPrintDate($value): string
{
    if ($value === null || trim((string) $value) === '') {
        return '';
    }

    $raw = trim((string) $value);

    if (preg_match('/^(\d{4})[\/-](\d{1,2})[\/-](\d{1,2})/', $raw, $matches)) {
        try {
            [$jy, $jm, $jd] = $this->gregorianToJalali(
                (int) $matches[1],
                (int) $matches[2],
                (int) $matches[3]
            );

            return sprintf('%04d/%02d/%02d', $jy, $jm, $jd);
        } catch (\Throwable $e) {
            return str_replace('-', '/', $matches[0]);
        }
    }

    $raw = preg_replace('/\s+\d{2}:\d{2}:\d{2}$/', '', $raw);
    $raw = preg_replace('/T\d{2}:\d{2}:\d{2}(\.\d+)?Z?$/', '', $raw);

    return trim($raw);
}

private function applyPrintFilters($query, Request $request)
{
    $search      = trim((string) $request->query('search', ''));
    $searchField = trim((string) $request->query('search_field', ''));
    $searchValue = trim((string) $request->query('search_value', ''));
    $resourceId  = $request->query('resource_id');

    $allowedFields = $this->printSearchFields();

    if ($search !== '' && $searchField === '') {
        $query->where(function ($x) use ($search) {
            $x->where('captives.name', 'like', "%{$search}%")
                ->orWhere('captives.father_name', 'like', "%{$search}%")
                ->orWhere('captives.nick_name', 'like', "%{$search}%")
                ->orWhere('captives.main_province', 'like', "%{$search}%")
                ->orWhere('captives.main_district', 'like', "%{$search}%")
                ->orWhere('captives.main_village', 'like', "%{$search}%")
                ->orWhere('captives.now_province', 'like', "%{$search}%")
                ->orWhere('captives.now_zone', 'like', "%{$search}%")
                ->orWhere('captives.now_district', 'like', "%{$search}%")
                ->orWhere('captives.now_village', 'like', "%{$search}%")
                ->orWhere('captives.specific_place', 'like', "%{$search}%")
                ->orWhere('captives.street_number', 'like', "%{$search}%")
                ->orWhere('captives.home_number', 'like', "%{$search}%")
                ->orWhere('captives.specific_form', 'like', "%{$search}%")
                ->orWhere('captives.band_distince', 'like', "%{$search}%")
                ->orWhere('captives.now_activity', 'like', "%{$search}%")
                ->orWhere('captives.phone_number', 'like', "%{$search}%")
                ->orWhere('captives.note', 'like', "%{$search}%")
                ->orWhere('captives.Province_addr', 'like', "%{$search}%")
                ->orWhere('captives.coordinates', 'like', "%{$search}%")
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
            } elseif ($searchField === 'resource_phone_number') {
                $query->whereHas('resource', function ($r) use ($value) {
                    $r->where('phone_number', 'like', "%{$value}%");
                });
            } else {
                $query->where("captives.{$searchField}", 'like', "%{$value}%");
            }
        }
    }

    if ($resourceId !== null && $resourceId !== '') {
        $query->where('captives.resource_id', $resourceId);
    }

    return $query;
}

public function printView(Request $request)
{
    $query = Captive::query()
        ->leftJoin('resources', 'captives.resource_id', '=', 'resources.id')
        ->select([
            'captives.*',
            'resources.code_number as resource_code',
            'resources.phone_number as resource_phone_number',
        ])
        ->with([
            'resource:id,name,code_number,phone_number',
            'documents',
        ]);

    $this->applyPrintFilters($query, $request);

    $dateColumns = [
        'captive_date',
        'date',
        'from_date',
        'to_date',
        'created_at',
        'updated_at',
    ];

    $rows = $query
        ->orderByDesc('captives.id')
        ->get()
        ->map(function ($row) use ($dateColumns) {
            $row->resource_code =
                $row->resource?->code_number
                ?? $row->resource_code
                ?? $row->resource_id
                ?? '-';

            $row->resource_phone_number =
                $row->resource?->phone_number
                ?? $row->resource_phone_number
                ?? '-';

            foreach ($dateColumns as $column) {
                if (isset($row->{$column}) && trim((string) $row->{$column}) !== '') {
                    $row->{$column} = $this->cleanPrintDate($row->{$column});
                }
            }

            return $row;
        });

    $selectedColumns = $request->input('selected_columns', []);
    $columnLabels    = $request->input('column_labels', []);
    $printLabels     = $request->input('print_labels', []);
    $locale          = $request->input('locale', 'fa');
    $title           = $request->input('title', 'چاپ محبوسین رها شده');

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