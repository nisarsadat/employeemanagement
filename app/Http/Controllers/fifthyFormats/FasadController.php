<?php

namespace App\Http\Controllers\fifthyFormats;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\fifthyFormats\Fasad;
use App\Models\fifthyFormats\FasadDocument;
use App\Http\Resources\fifthyformats\FasadResource;
use App\Http\Requests\fifthyformats\StoreFasadRequest;
use App\Http\Requests\fifthyformats\UpdateFasadRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Storage;
use ZipArchive;
use App\Services\NotificationService; // 🔔 notifications

class FasadController extends Controller
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

        'name',
        'picture', // ✅ single picture path
        'nick_name',
        'father_name',
        'main_province',
        'main_district',
        'main_village',
        'now_province',
        'now_zone',
        'Province_addr',
        'now_district',
        'now_village',
        'specific_place',
        'street_number',
        'home_number',
        'jabha',
        'supplier_name',
        'responsibility',
        'activity_zone',
        'friends_number',
        'phone_number',
        'bond_distance',
        'last_band_reason',
        'band_place',
        'now_job',
        'tafseel',
        'resource_code',
        'resource_phone',
        'resource_id',
        'coordinates',
        'notes',
        
        ];

        $q = Fasad::query()
            ->with([
                'resource:id,name,code_number,phone_number',
                'documents:id,fasad_id,file_path,original_name,mime_type,size,created_at',
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
            return FasadResource::collection($data);
        }

        return FasadResource::collection($q->get());
    }

    /**
     * GET /fasads/filter-options
     */
    public function filterOptions(Request $request)
    {
        $field = trim((string) $request->query('field', ''));
        $q = trim((string) $request->query('q', ''));

        $allowedFields = [
           
         'information_id',

        'name',
        'picture', // ✅ single picture path
        'nick_name',
        'father_name',
        'main_province',
        'main_district',
        'main_village',
        'now_province',
        'now_zone',
        'Province_addr',
        'now_district',
        'now_village',
        'specific_place',
        'street_number',
        'home_number',
        'jabha',
        'supplier_name',
        'responsibility',
        'activity_zone',
        'friends_number',
        'phone_number',
        'bond_distance',
        'last_band_reason',
        'band_place',
        'now_job',
        'tafseel',
        'resource_code',
        'resource_phone',
        'resource_id',
        'coordinates',
        'notes',
        ];

        if ($field === '' || !in_array($field, $allowedFields, true)) {
            return response()->json([
                'data' => [
                    'options' => [],
                ],
            ]);
        }

        $query = Fasad::query()
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

    public function store(StoreFasadRequest $request)
    {
        $user = $request->user(); // 🔔 who did this

        try {
            return DB::transaction(function () use ($request, $user) {
                $validated = $request->validated();

                // ✅ picture upload
                if ($request->hasFile('picture')) {
                    $validated['picture'] = $request->file('picture')->store('uploads/fasads/pictures', 'public');
                }

                $fasad = Fasad::create($validated);

                // ✅ documents upload (multiple)
                if ($request->hasFile('documents')) {
                    foreach ($request->file('documents') as $file) {
                        $path = $file->store('uploads/fasads/docs', 'public');

                        FasadDocument::create([
                            'fasad_id' => $fasad->id,
                            'file_path' => $path,
                            'original_name' => $file->getClientOriginalName(),
                            'mime_type' => $file->getClientMimeType(),
                            'size' => $file->getSize(),
                        ]);
                    }
                }

                $fasad->load([
                    'resource:id,name,code_number,phone_number',
                    'documents:id,fasad_id,file_path,original_name,mime_type,size,created_at',
                ]);

                // 🔔 Notification: CREATED
                NotificationService::record(
                    $user,
                    module: 'شر او فساد',
                    action: 'created',
                    title: 'نوی ریکارډ ثبت شو',
                    message: 'نوم: ' . ($fasad->name ?? ''),
                    routeName: 'fasads',
                    routeParams: ['id' => $fasad->id],
                    level: 'success',
                    changes: null
                );

                return FasadResource::make($fasad);
            });
        } catch (QueryException $e) {
            // ✅ Duplicate unique constraint (MySQL: 1062)
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

    public function show(Fasad $fasad)
    {
        $fasad->load([
            'resource:id,name,code_number,phone_number',
            'documents:id,fasad_id,file_path,original_name,mime_type,size,created_at',
        ]);

        return FasadResource::make($fasad);
    }

    public function update(UpdateFasadRequest $request, Fasad $fasad)
    {
        $user = $request->user(); // 🔔

        try {
            return DB::transaction(function () use ($request, $fasad, $user) {
                // 🧮 build changes BEFORE update
                $original  = $fasad->getOriginal();
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

                // ✅ picture replace (if sent)
                if ($request->hasFile('picture')) {
                    // delete old picture file
                    if ($fasad->picture && Storage::disk('public')->exists($fasad->picture)) {
                        Storage::disk('public')->delete($fasad->picture);
                    }

                    $validated['picture'] = $request->file('picture')->store('uploads/fasads/pictures', 'public');

                    // track change for notifications
                    $changes['picture'] = [
                        'old' => $original['picture'] ?? null,
                        'new' => $validated['picture'],
                    ];
                }

                // 🔄 apply update
                $fasad->update($validated);

                // ✅ remove documents by ids (removed_document_ids[])
                $removeIds = $request->input('removed_document_ids', []);
                $removeIds = is_array($removeIds) ? array_values(array_filter($removeIds)) : [];

                if (!empty($removeIds)) {
                    $docs = $fasad->documents()->whereIn('id', $removeIds)->get();

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

                // ✅ add new documents (documents[])
                if ($request->hasFile('documents')) {
                    $added = [];
                    foreach ($request->file('documents') as $file) {
                        $path = $file->store('uploads/fasads/docs', 'public');

                        $doc = FasadDocument::create([
                            'fasad_id' => $fasad->id,
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

                $fasad->load([
                    'resource:id,name,code_number,phone_number',
                    'documents:id,fasad_id,file_path,original_name,mime_type,size,created_at',
                ]);

                // 🔔 Notification: UPDATED + changes
                NotificationService::record(
                    $user,
                    module: 'شر او فساد',
                    action: 'updated',
                    title: 'ریکارډ اصلاح شو',
                    message: 'نوم: ' . ($fasad->name ?? ''),
                    routeName: 'fasads',
                    routeParams: ['id' => $fasad->id],
                    level: 'info',
                    changes: !empty($changes) ? $changes : null
                );

                return FasadResource::make($fasad);
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

    public function destroy(Request $request, Fasad $fasad)
    {
        $user = $request->user(); // 🔔

        return DB::transaction(function () use ($fasad, $user) {
            $name = $fasad->name;

            // ✅ delete picture file
            if ($fasad->picture && Storage::disk('public')->exists($fasad->picture)) {
                Storage::disk('public')->delete($fasad->picture);
            }

            // ✅ delete documents files + rows
            $docs = $fasad->documents()->get();
            foreach ($docs as $doc) {
                if ($doc->file_path && Storage::disk('public')->exists($doc->file_path)) {
                    Storage::disk('public')->delete($doc->file_path);
                }
            }
            $fasad->documents()->delete(); // safe (and cascade is ok too)

            $fasad->delete();

            // 🔔 Notification: DELETED
            NotificationService::record(
                $user,
                module: 'شر او فساد',
                action: 'deleted',
                title: 'ریکارډ حذف شو',
                message: 'نوم: ' . ($name ?? ''),
                routeName: null,
                routeParams: ['fasads'],
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

            $rows = Fasad::whereIn('id', $ids)->get(['id', 'name', 'picture']);

            // ✅ delete files for each fasad (picture + docs)
            $fasads = Fasad::with('documents')->whereIn('id', $ids)->get();

            foreach ($fasads as $fasad) {
                if ($fasad->picture && Storage::disk('public')->exists($fasad->picture)) {
                    Storage::disk('public')->delete($fasad->picture);
                }

                foreach ($fasad->documents as $doc) {
                    if ($doc->file_path && Storage::disk('public')->exists($doc->file_path)) {
                        Storage::disk('public')->delete($doc->file_path);
                    }
                }
            }

            Fasad::destroy($ids);

            // 🔔 Notification: BULK DELETED
            NotificationService::record(
                $user,
                module: 'شر او فساد',
                action: 'bulk_deleted',
                title: 'ډله ییز حذف ترسره شو',
                message: 'ټول حذف شوي: ' . count($ids),
                routeName: null,
                routeParams: ['fasads'],
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

    // ✅ delete a single document
    public function deleteDocument(Request $request, $docId)
    {
        $user = $request->user();

        $doc = FasadDocument::findOrFail($docId);

        if ($doc->file_path && Storage::disk('public')->exists($doc->file_path)) {
            Storage::disk('public')->delete($doc->file_path);
        }

        $fasadId = $doc->fasad_id;
        $doc->delete();

        NotificationService::record(
            $user,
            module: 'شر او فساد',
            action: 'document_deleted',
            title: 'سند حذف شو',
            message: 'Document ID: ' . $docId,
            routeName: 'fasads',
            routeParams: ['id' => $fasadId],
            level: 'warning',
            changes: [
                'deleted_document_id' => [
                    'old' => $docId,
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

        // Convert UTF-8 (Persian/Pashto) to ASCII for Windows
        $name = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $name) ?: $name;

        // Replace anything unsafe
        $name = preg_replace('/[^A-Za-z0-9\.\-_]/', '_', $name);
        $name = preg_replace('/_+/', '_', $name);
        $name = trim($name, '_');

        return $name !== '' ? $name : 'file';
    }

    public function downloadZip(Request $request, $id)
    {
        $fasad = Fasad::with('documents')->findOrFail($id);

        $zip = new ZipArchive();

        $zipName = "fasad_{$fasad->id}.zip";
        $tmpDir  = storage_path("app/tmp");

        if (!is_dir($tmpDir)) {
            mkdir($tmpDir, 0777, true);
        }

        $zipPath = "{$tmpDir}/{$zipName}";

        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            return response()->json(['message' => 'Could not create zip'], 500);
        }

        // picture
        if ($fasad->picture && Storage::disk('public')->exists($fasad->picture)) {
            $picName = $this->safeZipName(basename($fasad->picture));

            $zip->addFile(
                Storage::disk('public')->path($fasad->picture),
                'picture/' . $picName
            );
        }

        // documents
        foreach ($fasad->documents as $doc) {
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

        // 🚨 THIS FIXES WINDOWS ZIP ERROR
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