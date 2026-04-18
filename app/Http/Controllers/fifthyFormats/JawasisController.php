<?php

namespace App\Http\Controllers\fifthyFormats;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\fifthyFormats\Jawasis;
use App\Models\fifthyFormats\JawasisDocument;
use App\Http\Resources\fifthyformats\JawasisResource;
use App\Http\Requests\fifthyformats\StoreJawasisRequest;
use App\Http\Requests\fifthyformats\UpdateJawasisRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Storage;
use ZipArchive;
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
            'name',
            'father_name',
            'nick_name',
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
            'coordinates',
            'responsiability',
            'activity_place',
            'friends_number',
            'phone_number',
            'band_distince',
            'band_place',
            'old_job',
            'now_job',
            'tafseel',
            'note',
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
            'name',
            'father_name',
            'nick_name',
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
            'coordinates',
            'responsiability',
            'activity_place',
            'friends_number',
            'phone_number',
            'band_distince',
            'band_place',
            'old_job',
            'now_job',
            'tafseel',
            'note',
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
            ->limit(50)
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
}