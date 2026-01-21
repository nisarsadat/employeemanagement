<?php

namespace App\Http\Controllers\fifthyformatone;

use App\Http\Controllers\Controller;
use App\Http\Requests\fifthyformatone\StoreLostMujremeenRequest;
use App\Http\Requests\fifthyformatone\UpdateLostMujremeenRequest;
use App\Http\Resources\fifthyformatone\LostMujremeenResource;
use App\Models\fifthyformatone\LostMujremeen;
use App\Models\fifthyformatone\LostMujremeenDocument;
use App\Services\NotificationService;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class LostMujremeenController extends Controller
{
    private function duplicateErrorPashto(): array
    {
        return [
            'message' => 'دا ریکارډ مخکې ثبت شوی دی. مهرباني وکړئ تکراري معلومات مه داخلوی.',
            'errors'  => [
                'duplicate' => [
                    'دا کس مخکې ثبت شوی دی (همدا نوم، د پلار نوم او اصلي پته).',
                ],
            ],
        ];
    }

    private function isDuplicateKeyException(QueryException $e): bool
    {
        return (int) ($e->errorInfo[1] ?? 0) === 1062;
    }

    private function deletePictureIfStored(?string $picture): void
    {
        if ($picture && str_starts_with($picture, 'storage/')) {
            Storage::disk('public')->delete(str_replace('storage/', '', $picture));
        }
    }

    private function deleteDocFileIfStored(?string $filePath): void
    {
        if (!$filePath) return;

        if (str_starts_with($filePath, 'storage/')) {
            Storage::disk('public')->delete(str_replace('storage/', '', $filePath));
            return;
        }

        if (str_starts_with($filePath, '/storage/')) {
            Storage::disk('public')->delete(str_replace('/storage/', '', $filePath));
            return;
        }
    }

    /**
     * ✅ Accept files from:
     * - _newDocs[] (your Vue form)
     * - documents[] (fallback)
     */
    private function getIncomingDocs(Request $request): array
    {
        // Vue sends "_newDocs[]"
        if ($request->hasFile('_newDocs')) {
            return (array) $request->file('_newDocs');
        }

        // fallback if someone sends "documents[]"
        if ($request->hasFile('documents')) {
            return (array) $request->file('documents');
        }

        return [];
    }

    private function storeDocsFor(LostMujremeen $item, array $files): void
    {
        foreach ($files as $file) {
            if (!$file) continue;

            $docPath = $file->store("uploads/lost-mujremeens/{$item->id}/documents", 'public');

            LostMujremeenDocument::create([
                'lost_mujremeen_id' => $item->id,
                'file_path'         => "storage/{$docPath}",
                'original_name'     => $file->getClientOriginalName(),
                'mime_type'         => $file->getClientMimeType(),
                'size'              => $file->getSize(),
            ]);
        }
    }

    public function index(Request $request)
    {
        $perPage = (int) $request->get('perPage', 15);
        $search  = trim((string) $request->get('search', ''));
        $rid     = $request->get('resource_id');

        $sortBy  = $request->get('sort_by', 'created_at');
        $sortDir = in_array($request->get('sort_dir'), ['asc', 'desc'], true) ? $request->get('sort_dir') : 'desc';

        $q = LostMujremeen::query()->with([
            'resource:id,name,code_number,phone_number,coordinates',
            'documents:id,lost_mujremeen_id,file_path,original_name,mime_type,size,created_at',
        ]);

        if ($search !== '') {
            $q->where(function ($qq) use ($search) {
                $qq->where('name', 'like', "%{$search}%")
                    ->orWhere('father_name', 'like', "%{$search}%")
                    ->orWhere('now_province', 'like', "%{$search}%")
                    ->orWhere('jorm', 'like', "%{$search}%");
            });
        }

        if ($rid) {
            $q->where('resource_id', $rid);
        }

        $q->orderBy($sortBy, $sortDir);

        return LostMujremeenResource::collection($q->paginate($perPage));
    }

    public function show(LostMujremeen $lostMujremeen)
    {
        $lostMujremeen->load([
            'resource:id,name,code_number,phone_number,coordinates',
            'documents:id,lost_mujremeen_id,file_path,original_name,mime_type,size,created_at',
        ]);

        return new LostMujremeenResource($lostMujremeen);
    }

    public function store(StoreLostMujremeenRequest $request)
    {
        $user = $request->user();

        try {
            return DB::transaction(function () use ($request, $user) {
                $data = $request->validated();

                // picture
                if ($request->hasFile('picture')) {
                    $path = $request->file('picture')->store('uploads/lost-mujremeens', 'public');
                    $data['picture'] = "storage/{$path}";
                } else {
                    if (array_key_exists('picture', $data) && !is_string($data['picture'])) {
                        unset($data['picture']);
                    }
                }

                $item = LostMujremeen::create($data);

                // ✅ docs (_newDocs[] or documents[])
                $files = $this->getIncomingDocs($request);
                if (!empty($files)) {
                    $this->storeDocsFor($item, $files);
                }

                $item->load([
                    'resource:id,name,code_number,phone_number,coordinates',
                    'documents:id,lost_mujremeen_id,file_path,original_name,mime_type,size,created_at',
                ]);

                NotificationService::record(
                    $user,
                    module: 'مجرمین فراری',
                    action: 'created',
                    title: 'مجرم فراری جدید ثبت شد',
                    message: 'نام: ' . ($item->name ?? ''),
                    routeName: 'lost-mujremeens',
                    routeParams: ['id' => $item->id],
                    level: 'success',
                    changes: null
                );

                return (new LostMujremeenResource($item))
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

    public function update(UpdateLostMujremeenRequest $request, LostMujremeen $lostMujremeen)
    {
        $user = $request->user();

        try {
            return DB::transaction(function () use ($request, $lostMujremeen, $user) {
                $validated = $request->validated();

                // track changes
                $original = $lostMujremeen->getOriginal();
                $changes  = [];

                foreach ($validated as $field => $newValue) {
                    if (array_key_exists($field, $original)) {
                        $oldValue = $original[$field];
                        if ($oldValue != $newValue) {
                            $changes[$field] = ['old' => $oldValue, 'new' => $newValue];
                        }
                    }
                }

                // picture update
                if ($request->hasFile('picture')) {
                    $this->deletePictureIfStored($lostMujremeen->picture);
                    $path = $request->file('picture')->store('uploads/lost-mujremeens', 'public');
                    $validated['picture'] = "storage/{$path}";
                } else {
                    if (!array_key_exists('picture', $validated) || !is_string($validated['picture'])) {
                        unset($validated['picture']);
                    }
                }

                // ✅ remove docs by ids: removed_document_ids[]
                $removedIds = $request->input('removed_document_ids', []);
                if (is_array($removedIds) && !empty($removedIds)) {
                    $docsToDelete = LostMujremeenDocument::query()
                        ->where('lost_mujremeen_id', $lostMujremeen->id)
                        ->whereIn('id', $removedIds)
                        ->get();

                    foreach ($docsToDelete as $doc) {
                        $this->deleteDocFileIfStored($doc->file_path);
                        $doc->delete();
                    }

                    $changes['documents_removed'] = ['old' => null, 'new' => $removedIds];
                }

                // ✅ add new docs: _newDocs[] or documents[]
                $files = $this->getIncomingDocs($request);
                if (!empty($files)) {
                    foreach ($files as $file) {
                        if (!$file) continue;

                        $docPath = $file->store("uploads/lost-mujremeens/{$lostMujremeen->id}/documents", 'public');

                        $doc = LostMujremeenDocument::create([
                            'lost_mujremeen_id' => $lostMujremeen->id,
                            'file_path'         => "storage/{$docPath}",
                            'original_name'     => $file->getClientOriginalName(),
                            'mime_type'         => $file->getClientMimeType(),
                            'size'              => $file->getSize(),
                        ]);

                        $changes['documents_added'][] = [
                            'id'   => $doc->id,
                            'name' => $doc->original_name,
                        ];
                    }
                }

                $lostMujremeen->update($validated);

                $lostMujremeen->load([
                    'resource:id,name,code_number,phone_number,coordinates',
                    'documents:id,lost_mujremeen_id,file_path,original_name,mime_type,size,created_at',
                ]);

                NotificationService::record(
                    $user,
                    module: 'مجرمین فراری',
                    action: 'updated',
                    title: 'مجرم فراری اصلاح شد',
                    message: 'نام: ' . ($lostMujremeen->name ?? ''),
                    routeName: 'lost-mujremeens',
                    routeParams: ['id' => $lostMujremeen->id],
                    level: 'info',
                    changes: !empty($changes) ? $changes : null
                );

                return new LostMujremeenResource($lostMujremeen);
            });
        } catch (QueryException $e) {
            if ($this->isDuplicateKeyException($e)) {
                return response()->json($this->duplicateErrorPashto(), 422);
            }
            throw $e;
        }
    }

    public function destroy(Request $request, LostMujremeen $lostMujremeen)
    {
        $user = $request->user();

        return DB::transaction(function () use ($lostMujremeen, $user) {
            $name = $lostMujremeen->name;

            // picture
            $this->deletePictureIfStored($lostMujremeen->picture);

            // docs
            $lostMujremeen->load('documents');
            foreach ($lostMujremeen->documents as $doc) {
                $this->deleteDocFileIfStored($doc->file_path);
            }

            $lostMujremeen->delete();

            NotificationService::record(
                $user,
                module: 'مجرمین فراری',
                action: 'deleted',
                title: 'مجرم فراری حذف شد',
                message: 'نام: ' . ($name ?? ''),
                routeName: null,
                routeParams: null,
                level: 'warning',
                changes: null
            );

            return response()->noContent();
        });
    }

    // ✅ ZIP download
    public function downloadDocumentsZip(LostMujremeen $lostMujremeen)
    {
        // prevent corrupted ZIP because of any accidental output
        while (ob_get_level() > 0) {
            @ob_end_clean();
        }

        $docs = $lostMujremeen->documents()->get();
        if ($docs->isEmpty()) {
            abort(404, 'هیچ سندی موجود نیست.');
        }

        $zipName = "lost_mujremeen_{$lostMujremeen->id}_documents.zip";
        $tmpFile = tempnam(sys_get_temp_dir(), 'zip_');

        $zip = new ZipArchive();
        $ok = $zip->open($tmpFile, ZipArchive::CREATE | ZipArchive::OVERWRITE);
        if ($ok !== true) {
            abort(500, 'Cannot create ZIP file.');
        }

        $added = 0;

        foreach ($docs as $doc) {
            if (empty($doc->file_path)) continue;

            $p = (string) $doc->file_path;

            if (str_starts_with($p, 'storage/'))  $p = substr($p, strlen('storage/'));
            if (str_starts_with($p, '/storage/')) $p = substr($p, strlen('/storage/'));

            $p = ltrim($p, '/');
            $absolute = Storage::disk('public')->path($p);

            if (is_file($absolute) && is_readable($absolute)) {
                $nameInZip = $doc->original_name ?: basename($absolute);

                // windows-safe
                $nameInZip = ltrim(str_replace(['\\', ':'], ['/', '-'], $nameInZip), '/');

                $zip->addFile($absolute, $nameInZip);
                $added++;
            }
        }

        $zip->close();
        clearstatcache(true, $tmpFile);

        if ($added === 0 || !filesize($tmpFile)) {
            @unlink($tmpFile);
            abort(404, 'No valid files to download.');
        }

        return response()->download($tmpFile, $zipName, [
            'Content-Type'              => 'application/zip',
            'Content-Disposition'       => 'attachment; filename="'.$zipName.'"',
            'X-Content-Type-Options'    => 'nosniff',
            'Cache-Control'             => 'no-store, no-cache, must-revalidate, max-age=0',
        ])->deleteFileAfterSend(true);
    }
}
