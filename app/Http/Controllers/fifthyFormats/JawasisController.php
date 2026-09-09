<?php

namespace App\Http\Controllers\fifthyFormats;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\fifthyFormats\Jawasis;
use App\Models\fifthyFormats\JawasisDocument;
use App\Http\Resources\fifthyFormats\JawasisResource;
use App\Http\Requests\fifthyFormats\StoreJawasisRequest;
use App\Http\Requests\fifthyFormats\UpdateJawasisRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Storage;
use ZipArchive;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\JawasisExport;
use App\Services\NotificationService;

class JawasisController extends Controller
{
    public function index(Request $request)
    {
        $page    = $request->query('page');
        $perPage = $request->query('perPage', $request->query('per_page'));

        $isValidPage    = is_numeric($page) && (int) $page > 0;
        $isValidPerPage = is_numeric($perPage) && (int) $perPage > 0;

        $search = trim((string) $request->query('search', ''));
        $fiftySixFormatId = $request->query('fifty_six_format_id');
        $searchField = trim((string) $request->query('search_field', ''));
        $searchValue = trim((string) $request->query('search_value', ''));

        $allowedSearchFields = [
           
         'information_id',
        'picture',
        'name',
        'nick_name',
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
        'now_home_number',
        'coordinates',
        'country',
        'responsiability',
        'done_activities',
        'activity_place',
        'count_of_communication',
        'phone_number',
        'old_job',
        'now_job',
        'resource_id',
        'notes',
        'tafseel',
        ];

        $q = Jawasis::query()
            ->with([
                'resource:id,name,code_number,phone_number',
                'documents:id,jawasis_id,file_path,original_name,mime_type,size,created_at',
            ])
            ->when($search !== '' && $searchField === '', function ($sub) use ($search) {
                $sub->where(function ($x) use ($search) {
                    $x->where('name', 'like', "%{$search}%")
                        ->orWhere('father_name', 'like', "%{$search}%")
                        ->orWhere('nick_name', 'like', "%{$search}%");
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
            ->when(
                is_numeric($fiftySixFormatId) && (int) $fiftySixFormatId > 0,
                function ($sub) use ($fiftySixFormatId) {
                    $sub->where('id', (int) $fiftySixFormatId);
                }
            )
            ->orderByDesc('id');

        if ($isValidPage && $isValidPerPage) {
            $data = $q->paginate((int) $perPage, ['*'], 'page', (int) $page);
            return JawasisResource::collection($data);
        }

        return JawasisResource::collection($q->get());
    }

    /**
     * GET /jawasis/filter-options
     */
    public function filterOptions(Request $request)
    {
        $field = trim((string) $request->query('field', ''));
        $q = trim((string) $request->query('q', ''));

        $allowedFields = [
           
         'information_id',
        'picture',
        'name',
        'nick_name',
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
        'now_home_number',
        'coordinates',
        'country',
        'responsiability',
        'done_activities',
        'activity_place',
        'count_of_communication',
        'phone_number',
        'old_job',
        'now_job',
        'resource_id',
        'notes',
        'tafseel',
        ];

        if ($field === '' || !in_array($field, $allowedFields, true)) {
            return response()->json([
                'data' => [
                    'options' => [],
                ],
            ]);
        }

        $query = Jawasis::query()
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

    public function store(StoreJawasisRequest $request)
    {
        $user = $request->user();

        try {
            return DB::transaction(function () use ($request, $user) {
                $validated = $request->validated();

                // ✅ picture upload
                if ($request->hasFile('picture')) {
                    $validated['picture'] = $request->file('picture')->store('uploads/jawasis/pictures', 'public');
                }

                $jawasi = Jawasis::create($validated);

                // ✅ documents upload (multiple)
                if ($request->hasFile('documents')) {
                    foreach ($request->file('documents') as $file) {
                        $path = $file->store('uploads/jawasis/docs', 'public');

                        JawasisDocument::create([
                            'jawasis_id' => $jawasi->id,
                            'file_path' => $path,
                            'original_name' => $file->getClientOriginalName(),
                            'mime_type' => $file->getClientMimeType(),
                            'size' => $file->getSize(),
                        ]);
                    }
                }

                $jawasi->load([
                    'resource:id,name,code_number,phone_number',
                    'documents:id,jawasis_id,file_path,original_name,mime_type,size,created_at',
                ]);

                NotificationService::record(
                    $user,
                    module: 'جواسیس',
                    action: 'created',
                    title: 'نوی ریکارډ ثبت شو',
                    message: 'نوم: ' . ($jawasi->name ?? ''),
                    routeName: 'jawasis',
                    routeParams: ['id' => $jawasi->id],
                    level: 'success',
                    changes: null
                );

                return JawasisResource::make($jawasi);
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

    /**
     * ✅ IMPORTANT:
     * With Route::apiResource('jawasis', ...),
     * the route param is {jawasi} (singular of jawasis).
     * So the argument MUST be Jawasis $jawasi (same name).
     */
    public function show(Jawasis $jawasi)
    {
        $jawasi->load([
            'resource:id,name,code_number,phone_number',
            'documents:id,jawasis_id,file_path,original_name,mime_type,size,created_at',
        ]);

        return JawasisResource::make($jawasi);
    }

    public function update(UpdateJawasisRequest $request, Jawasis $jawasi)
    {
        $user = $request->user();

        try {
            return DB::transaction(function () use ($request, $jawasi, $user) {
                $original  = $jawasi->getOriginal();
                $validated = $request->validated();
                $changes   = [];

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

                // ✅ picture replace
                if ($request->hasFile('picture')) {
                    if ($jawasi->picture && Storage::disk('public')->exists($jawasi->picture)) {
                        Storage::disk('public')->delete($jawasi->picture);
                    }

                    $validated['picture'] = $request->file('picture')->store('uploads/jawasis/pictures', 'public');

                    $changes['picture'] = [
                        'old' => $original['picture'] ?? null,
                        'new' => $validated['picture'],
                    ];
                }

                $jawasi->update($validated);

                // ✅ remove documents by ids
                $removeIds = $request->input('removed_document_ids', []);
                $removeIds = is_array($removeIds) ? array_values(array_filter($removeIds)) : [];

                if (!empty($removeIds)) {
                    $docs = $jawasi->documents()->whereIn('id', $removeIds)->get();

                    foreach ($docs as $doc) {
                        if ($doc->file_path && Storage::disk('public')->exists($doc->file_path)) {
                            Storage::disk('public')->delete($doc->file_path);
                        }
                        $doc->delete();
                    }

                    $changes['removed_document_ids'] = [
                        'old' => $removeIds,
                        'new' => [],
                    ];
                }

                // ✅ add new documents
                if ($request->hasFile('documents')) {
                    $added = [];
                    foreach ($request->file('documents') as $file) {
                        $path = $file->store('uploads/jawasis/docs', 'public');

                        $doc = JawasisDocument::create([
                            'jawasis_id' => $jawasi->id,
                            'file_path' => $path,
                            'original_name' => $file->getClientOriginalName(),
                            'mime_type' => $file->getClientMimeType(),
                            'size' => $file->getSize(),
                        ]);

                        $added[] = $doc->id;
                    }

                    $changes['added_document_ids'] = [
                        'old' => [],
                        'new' => $added,
                    ];
                }

                $jawasi->load([
                    'resource:id,name,code_number,phone_number',
                    'documents:id,jawasis_id,file_path,original_name,mime_type,size,created_at',
                ]);

                NotificationService::record(
                    $user,
                    module: 'جواسیس',
                    action: 'updated',
                    title: 'ریکارډ اصلاح شو',
                    message: 'نوم: ' . ($jawasi->name ?? ''),
                    routeName: 'jawasis',
                    routeParams: ['id' => $jawasi->id],
                    level: 'info',
                    changes: !empty($changes) ? $changes : null
                );

                return JawasisResource::make($jawasi);
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

    public function destroy(Request $request, Jawasis $jawasi)
    {
        $user = $request->user();

        return DB::transaction(function () use ($jawasi, $user) {
            $name = $jawasi->name;

            if ($jawasi->picture && Storage::disk('public')->exists($jawasi->picture)) {
                Storage::disk('public')->delete($jawasi->picture);
            }

            $docs = $jawasi->documents()->get();
            foreach ($docs as $doc) {
                if ($doc->file_path && Storage::disk('public')->exists($doc->file_path)) {
                    Storage::disk('public')->delete($doc->file_path);
                }
            }
            $jawasi->documents()->delete();

            $jawasi->delete();

            NotificationService::record(
                $user,
                module: 'جواسیس',
                action: 'deleted',
                title: 'ریکارډ حذف شو',
                message: 'نوم: ' . ($name ?? ''),
                routeName: null,
                routeParams: ['jawasis'],
                level: 'warning',
                changes: null
            );

            return response()->noContent();
        });
    }

    public function bulkDelete(Request $request)
    {
        $user = $request->user();

        $ids = $request->input('ids', []);
        $ids = is_array($ids) ? array_values(array_filter($ids)) : [];

        return DB::transaction(function () use ($ids, $user) {
            if (empty($ids)) {
                return response()->noContent();
            }

            $rows = Jawasis::whereIn('id', $ids)->get(['id', 'name', 'picture']);
            $jawasisRows = Jawasis::with('documents')->whereIn('id', $ids)->get();

            foreach ($jawasisRows as $jawasi) {
                if ($jawasi->picture && Storage::disk('public')->exists($jawasi->picture)) {
                    Storage::disk('public')->delete($jawasi->picture);
                }

                foreach ($jawasi->documents as $doc) {
                    if ($doc->file_path && Storage::disk('public')->exists($doc->file_path)) {
                        Storage::disk('public')->delete($doc->file_path);
                    }
                }
            }

            Jawasis::destroy($ids);

            NotificationService::record(
                $user,
                module: 'جواسیس',
                action: 'bulk_deleted',
                title: 'ډله ییز حذف ترسره شو',
                message: 'ټول حذف شوي: ' . count($ids),
                routeName: null,
                routeParams: ['jawasis'],
                level: 'warning',
                changes: [
                    'deleted_ids' => [
                        'old' => $rows->pluck('id')->values()->all(),
                        'new' => [],
                    ],
                    'deleted_names' => [
                        'old' => $rows->pluck('name')->values()->all(),
                        'new' => [],
                    ],
                ],
            );

            return response()->noContent();
        });
    }

    public function deleteDocument(Request $request, $id)
    {
        $user = $request->user();

        $doc = JawasisDocument::findOrFail($id);

        if ($doc->file_path && Storage::disk('public')->exists($doc->file_path)) {
            Storage::disk('public')->delete($doc->file_path);
        }

        $jawasisId = $doc->jawasis_id;
        $doc->delete();

        NotificationService::record(
            $user,
            module: 'جواسیس',
            action: 'document_deleted',
            title: 'سند حذف شو',
            message: 'Document ID: ' . $id,
            routeName: 'jawasis',
            routeParams: ['id' => $jawasisId],
            level: 'warning',
            changes: [
                'deleted_document_id' => [
                    'old' => $id,
                    'new' => null,
                ],
            ],
        );

        return response()->json(['message' => 'Document deleted']);
    }

    /* ================= ZIP helpers ================= */
    private function safeZipName(string $name): string
    {
        $name = basename($name);
        $name = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $name) ?: $name;
        $name = preg_replace('/[^A-Za-z0-9\.\-_]/', '_', $name);
        $name = preg_replace('/_+/', '_', $name);
        $name = trim($name, '_');

        return $name !== '' ? $name : 'file';
    }

    public function downloadZip(Request $request, $id)
    {
        $jawasi = Jawasis::with('documents')->findOrFail($id);

        $zip = new ZipArchive();

        $zipName = "jawasis_{$jawasi->id}.zip";
        $tmpDir  = storage_path("app/tmp");

        if (!is_dir($tmpDir)) {
            mkdir($tmpDir, 0777, true);
        }

        $zipPath = "{$tmpDir}/{$zipName}";

        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            return response()->json(['message' => 'Could not create zip'], 500);
        }

        // picture
        if ($jawasi->picture && Storage::disk('public')->exists($jawasi->picture)) {
            $picName = $this->safeZipName(basename($jawasi->picture));

            $zip->addFile(
                Storage::disk('public')->path($jawasi->picture),
                'picture/' . $picName
            );
        }

        // documents
        foreach ($jawasi->documents as $doc) {
            if ($doc->file_path && Storage::disk('public')->exists($doc->file_path)) {
                $safeName = $this->safeZipName(
                    $doc->original_name ?: basename($doc->file_path)
                );

                $zip->addFile(
                    Storage::disk('public')->path($doc->file_path),
                    'documents/' . $safeName
                );
            }
        }

        $zip->close();

        if (ob_get_level()) {
            ob_end_clean();
        }

        return response()->download(
            $zipPath,
            $zipName,
            [
                'Content-Type' => 'application/zip',
                'Content-Disposition' => 'attachment; filename="' . $zipName . '"',
            ]
        )->deleteFileAfterSend(true);
    }
    private function printSearchFields(): array
{
    return [
        'information_id',
        'name',
        'nick_name',
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
        'now_home_number',
        'coordinates',
        'country',
        'responsiability',
        'done_activities',
        'activity_place',
        'count_of_communication',
        'phone_number',
        'old_job',
        'now_job',
        'resource_id',
        'resource_code',
        'resource_phone',
        'notes',
        'tafseel',
    ];
}

private function applyPrintFilters($query, Request $request)
{
    $search           = trim((string) $request->query('search', ''));
    $searchField      = trim((string) $request->query('search_field', ''));
    $searchValue      = trim((string) $request->query('search_value', ''));
    $fiftySixFormatId = $request->query('fifty_six_format_id');

    $allowedFields = $this->printSearchFields();

    if ($search !== '' && $searchField === '') {
        $query->where(function ($x) use ($search) {
            $x->where('name', 'like', "%{$search}%")
                ->orWhere('nick_name', 'like', "%{$search}%")
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
                ->orWhere('coordinates', 'like', "%{$search}%")
                ->orWhere('country', 'like', "%{$search}%")
                ->orWhere('responsiability', 'like', "%{$search}%")
                ->orWhere('done_activities', 'like', "%{$search}%")
                ->orWhere('activity_place', 'like', "%{$search}%")
                ->orWhere('count_of_communication', 'like', "%{$search}%")
                ->orWhere('phone_number', 'like', "%{$search}%")
                ->orWhere('old_job', 'like', "%{$search}%")
                ->orWhere('now_job', 'like', "%{$search}%")
                ->orWhere('tafseel', 'like', "%{$search}%")
                ->orWhere('notes', 'like', "%{$search}%")
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

    if (is_numeric($fiftySixFormatId) && (int) $fiftySixFormatId > 0) {
        $query->where('id', (int) $fiftySixFormatId);
    }

    return $query;
}

private function cleanPrintDate($value): string
{
    if ($value === null || trim((string) $value) === '') {
        return '';
    }

    $raw = trim((string) $value);

    $raw = preg_replace('/\s+\d{2}:\d{2}:\d{2}$/', '', $raw);
    $raw = preg_replace('/T\d{2}:\d{2}:\d{2}(\.\d+)?Z?$/', '', $raw);

    if (preg_match('/(\d{4})[\/-](\d{1,2})[\/-](\d{1,2})/', $raw, $matches)) {
        return sprintf('%04d/%02d/%02d', $matches[1], $matches[2], $matches[3]);
    }

    return trim($raw);
}

public function printView(Request $request)
{
    $query = Jawasis::query()
        ->with([
            'resource:id,name,code_number,phone_number',
            'documents:id,jawasis_id,file_path,original_name,mime_type,size,created_at',
        ]);

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
                ?? $row->resource_phone
                ?? '-';

            if ($row->created_at !== null && trim((string) $row->created_at) !== '') {
                $row->created_at = $this->cleanPrintDate($row->created_at);
            }

            if ($row->updated_at !== null && trim((string) $row->updated_at) !== '') {
                $row->updated_at = $this->cleanPrintDate($row->updated_at);
            }

            return $row;
        });

    $selectedColumns = $request->input('selected_columns', []);
    $columnLabels    = $request->input('column_labels', []);
    $printLabels     = $request->input('print_labels', []);
    $locale          = $request->input('locale', 'fa');
    $title           = $request->input('title', 'چاپ جواسیس');

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
    $jawasi = Jawasis::findOrFail($id);

    return $this->removePictureFileAndClear($jawasi);
}

public function exportExcel(Request $request)
{
    $search = trim((string) $request->get('search', ''));
    $searchField = $request->get('search_field');
    $searchValue = $request->get('search_value');
    $fileName = 'jawasis_' . now()->format('Y_m_d_H_i_s') . '.xlsx';
    return Excel::download(new JawasisExport(['search' => $search, 'search_field' => $searchField, 'search_value' => $searchValue, 'resource_id' => $request->get('resource_id')]), $fileName);
}
}