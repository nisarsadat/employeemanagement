<?php

namespace App\Http\Controllers\fifthyFormats;

use App\Http\Controllers\Controller;
use App\Http\Requests\fifthyFormats\StoreSaeenFasadRequest;
use App\Http\Requests\fifthyFormats\UpdateSaeenFasadRequest;
use App\Http\Resources\fifthyFormats\SaeenFasadResource;
use App\Models\fifthyFormats\SaeenFasad;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\QueryException;
use App\Services\NotificationService; // 🔔 notifications
use ZipArchive;

class SaeenFasadController extends Controller
{
    /**
     * GET /saeen-fasadas
     */
    public function index(Request $request)
    {
        $page    = $request->query('page');
        $perPage = $request->query('perPage', $request->query('per_page'));

        $isValidPage    = is_numeric($page) && (int) $page > 0;
        $isValidPerPage = is_numeric($perPage) && (int) $perPage > 0;

        $search = trim((string) $request->query('search', ''));

        $q = SaeenFasad::query()
            ->with([
                'resource:id,name,code_number,phone_number',
                'documents', // ✅ include documents for list/zip icon
            ])
            ->when($search !== '', function ($sub) use ($search) {
                $sub->where(function ($x) use ($search) {
                    $x->where('name', 'like', "%{$search}%")
                        ->orWhere('father_name', 'like', "%{$search}%")
                        ->orWhere('nick_name', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('id');

        if ($isValidPage && $isValidPerPage) {
            $data = $q->paginate((int) $perPage, ['*'], 'page', (int) $page);
            return SaeenFasadResource::collection($data);
        }

        return SaeenFasadResource::collection($q->get());
    }

    /**
     * GET /saeen-fasadas/{saeenFasad}
     */
    public function show(SaeenFasad $saeenFasad)
    {
        $saeenFasad->load([
            'resource:id,name,code_number,phone_number',
            'documents',
        ]);

        return new SaeenFasadResource($saeenFasad);
    }

    /**
     * POST /saeen-fasadas
     * Accepts:
     * - picture as FILE or STRING
     * - documents[] as multiple FILES (optional)
     */
    public function store(StoreSaeenFasadRequest $request)
    {
        $user = $request->user(); // 🔔 who did this
        $data = $request->validated();

        try {
            return DB::transaction(function () use ($request, $data, $user) {
                // ✅ picture: file OR string
                if ($request->hasFile('picture')) {
                    $path = $request->file('picture')->store('uploads/saeen-fasads', 'public');
                    $data['picture'] = "storage/{$path}";
                } else {
                    if (array_key_exists('picture', $data) && !is_string($data['picture'])) {
                        unset($data['picture']);
                    }
                }

                $item = SaeenFasad::create($data);

                // ✅ documents (multiple)
                if ($request->hasFile('documents')) {
                    foreach ($request->file('documents') as $file) {
                        if (!$file) continue;

                        $path = $file->store('uploads/saeen-fasads/documents', 'public');

                        $item->documents()->create([
                            'file_path'     => "storage/{$path}",
                            'original_name' => $file->getClientOriginalName(),
                            'mime_type'     => $file->getClientMimeType(),
                            'size'          => $file->getSize(),
                        ]);
                    }
                }

                $item->load([
                    'resource:id,name,code_number,phone_number',
                    'documents',
                ]);

                // 🔔 Notification: CREATED
                NotificationService::record(
                    $user,
                    module: 'سعین فساد',
                    action: 'created',
                    title: 'نوی ریکارډ ثبت شو',
                    message: 'نوم: ' . ($item->name ?? ''),
                    routeName: 'saeen-fasads', // ⚠️ adjust to your real route name
                    routeParams: ['id' => $item->id],
                    level: 'success',
                    changes: null
                );

                return (new SaeenFasadResource($item))
                    ->additional(['message' => 'Created'])
                    ->response()
                    ->setStatusCode(201);
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

    /**
     * PUT /saeen-fasadas/{saeenFasad}
     * Accepts:
     * - picture as FILE or STRING (replaces old file if new uploaded)
     * - documents[] as multiple FILES (optional)
     * - removed_document_ids as JSON string or array (optional)
     */
    public function update(UpdateSaeenFasadRequest $request, SaeenFasad $saeenFasad)
    {
        $user = $request->user(); // 🔔
        $data = $request->validated();

        try {
            return DB::transaction(function () use ($request, $data, $saeenFasad, $user) {
                // 🧮 build changes BEFORE update
                $original = $saeenFasad->getOriginal();
                $changes  = [];

                foreach ($data as $field => $newValue) {
                    if (array_key_exists($field, $original)) {
                        $oldValue = $original[$field];
                        if ($oldValue != $newValue) {
                            $changes[$field] = ['old' => $oldValue, 'new' => $newValue];
                        }
                    }
                }

                // ✅ picture: replace file if uploaded
                if ($request->hasFile('picture')) {
                    if ($saeenFasad->picture && str_starts_with($saeenFasad->picture, 'storage/')) {
                        $old = str_replace('storage/', '', $saeenFasad->picture);
                        Storage::disk('public')->delete($old);
                    }

                    $path = $request->file('picture')->store('uploads/saeen-fasads', 'public');
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

                // ✅ remove documents by ids
                $removedIds = $request->input('removed_document_ids', []);
                if (is_string($removedIds)) {
                    $removedIds = json_decode($removedIds, true) ?: [];
                }

                if (is_array($removedIds) && count($removedIds)) {
                    $docs = $saeenFasad->documents()->whereIn('id', $removedIds)->get();
                    foreach ($docs as $doc) {
                        if ($doc->file_path && str_starts_with($doc->file_path, 'storage/')) {
                            $old = str_replace('storage/', '', $doc->file_path);
                            Storage::disk('public')->delete($old);
                        }
                        $doc->delete();
                    }
                    $changes['documents_removed'] = ['old' => $removedIds, 'new' => []];
                }

                // ✅ update main record
                $saeenFasad->update($data);

                // ✅ add new documents
                if ($request->hasFile('documents')) {
                    foreach ($request->file('documents') as $file) {
                        if (!$file) continue;

                        $path = $file->store('uploads/saeen-fasads/documents', 'public');

                        $saeenFasad->documents()->create([
                            'file_path'     => "storage/{$path}",
                            'original_name' => $file->getClientOriginalName(),
                            'mime_type'     => $file->getClientMimeType(),
                            'size'          => $file->getSize(),
                        ]);
                    }
                    $changes['documents_added'] = ['old' => [], 'new' => 'uploaded'];
                }

                $saeenFasad->load([
                    'resource:id,name,code_number,phone_number',
                    'documents',
                ]);

                // 🔔 Notification: UPDATED + changes
                NotificationService::record(
                    $user,
                    module: 'سعین فساد',
                    action: 'updated',
                    title: 'ریکارډ اصلاح شو',
                    message: 'نوم: ' . ($saeenFasad->name ?? ''),
                    routeName: 'saeen-fasads', // ⚠️ adjust if needed
                    routeParams: ['id' => $saeenFasad->id],
                    level: 'info',
                    changes: !empty($changes) ? $changes : null
                );

                return new SaeenFasadResource($saeenFasad);
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
     * DELETE /saeen-fasadas/{saeenFasad}
     * ✅ deletes:
     * - picture file
     * - documents files
     * - DB rows (documents table cascades)
     */
    public function destroy(Request $request, SaeenFasad $saeenFasad)
    {
        $user = $request->user(); // 🔔

        return DB::transaction(function () use ($saeenFasad, $user) {
            $name = $saeenFasad->name;

            // ✅ delete picture file
            if ($saeenFasad->picture && str_starts_with($saeenFasad->picture, 'storage/')) {
                $old = str_replace('storage/', '', $saeenFasad->picture);
                Storage::disk('public')->delete($old);
            }

            // ✅ delete documents files
            $docs = $saeenFasad->documents()->get();
            foreach ($docs as $doc) {
                if ($doc->file_path && str_starts_with($doc->file_path, 'storage/')) {
                    $old = str_replace('storage/', '', $doc->file_path);
                    Storage::disk('public')->delete($old);
                }
            }

            // ✅ delete record (doc rows will be deleted via cascadeOnDelete)
            $saeenFasad->delete();

            // 🔔 Notification: DELETED
            NotificationService::record(
                $user,
                module: 'سعین فساد',
                action: 'deleted',
                title: 'ریکارډ حذف شو',
                message: 'نوم: ' . ($name ?? ''),
                routeName: null,
                routeParams: ['saeen-fasads'],
                level: 'warning',
                changes: null
            );

            return response()->noContent();
        });
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

    /**
     * GET /saeen-fasadas/{id}/download-zip
     * ✅ Windows-safe ZIP (same style as FasadController)
     */
    public function downloadZip(Request $request, $id)
    {
        $saeenFasad = SaeenFasad::with('documents')->findOrFail($id);

        $zip = new ZipArchive();

        $zipName = "saeen_fasad_{$saeenFasad->id}.zip";
        $tmpDir  = storage_path("app/tmp");

        if (!is_dir($tmpDir)) {
            mkdir($tmpDir, 0777, true);
        }

        $zipPath = "{$tmpDir}/{$zipName}";

        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            return response()->json(['message' => 'Could not create zip'], 500);
        }

        // ✅ picture (optional)
        if ($saeenFasad->picture) {
            $picPath = (string) $saeenFasad->picture;

            // if saved as "storage/xxxx" convert to "xxxx"
            if (str_starts_with($picPath, 'storage/')) {
                $picPath = str_replace('storage/', '', $picPath);
            }

            if (Storage::disk('public')->exists($picPath)) {
                $picName = $this->safeZipName(basename($picPath));

                $zip->addFile(
                    Storage::disk('public')->path($picPath),
                    'picture/' . $picName
                );
            }
        }

        // ✅ documents
        foreach ($saeenFasad->documents as $doc) {
            $docPath = (string) ($doc->file_path ?? '');
            if ($docPath === '') continue;

            if (str_starts_with($docPath, 'storage/')) {
                $docPath = str_replace('storage/', '', $docPath);
            }

            if (Storage::disk('public')->exists($docPath)) {
                $safeName = $this->safeZipName(
                    (string) ($doc->original_name ?: basename($docPath))
                );

                $zip->addFile(
                    Storage::disk('public')->path($docPath),
                    'documents/' . $safeName
                );
            }
        }

        $zip->close();

        // 🚨 fixes Windows "zip invalid" (output buffer corruption)
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
