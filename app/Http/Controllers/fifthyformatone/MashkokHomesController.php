<?php

namespace App\Http\Controllers\fifthyformatone;

use App\Http\Controllers\Controller;
use App\Http\Requests\fifthyformatone\StoreMashkokHomesRequest;
use App\Http\Requests\fifthyformatone\UpdateMashkokHomesRequest;
use App\Http\Resources\fifthyformatone\MashkokHomesResource;
use App\Models\fifthyformatone\MashkokHomes;
use App\Services\NotificationService;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use ZipArchive;

class MashkokHomesController extends Controller
{
    private function duplicateErrorPashto(): array
    {
        return [
            'message' => 'دا کور مخکې ثبت شوی دی. مهرباني وکړئ تکراري معلومات مه داخلوی.',
            'errors'  => [
                'duplicate' => [
                    'دا موقعیت مخکې ثبت شوی دی (همدا ولایت، زون، ولسوالۍ، کلی او مشخص ځای).',
                ],
            ],
        ];
    }

    private function isDuplicateKeyException(QueryException $e): bool
    {
        return (int) ($e->errorInfo[1] ?? 0) === 1062;
    }

    // ✅ NEW: delete picture by public disk path (same style)
    private function deletePictureIfStored(?string $picture): void
    {
        if (!$picture) return;

        $path = $picture;

        // normalize if it was stored as "storage/..."
        if (str_starts_with($path, 'storage/')) {
            $path = substr($path, strlen('storage/'));
        }

        Storage::disk('public')->delete($path);
    }

    public function index(Request $request)
    {
        $perPage = (int) $request->input('perPage', 10);
        $search  = $request->input('search');
        $rid     = $request->input('resource_id');

        $sortBy  = $request->input('sort_by', 'created_at');
        $sortDir = in_array($request->input('sort_dir'), ['asc', 'desc'], true)
            ? $request->input('sort_dir')
            : 'desc';

        $q = MashkokHomes::query()->with([
            'resource' => fn ($qr) => $qr->select('id', 'name', 'code_number', 'phone_number'),
            'documents',
        ]);

        if ($search) {
            $q->where(function ($w) use ($search) {
                $w->where('province', 'like', "%{$search}%")
                  ->orWhere('district', 'like', "%{$search}%")
                  ->orWhere('village', 'like', "%{$search}%")
                  ->orWhere('home_type', 'like', "%{$search}%");
            });
        }

        if ($rid) {
            $q->where('resource_id', $rid);
        }

        $q->orderBy($sortBy, $sortDir);

        return MashkokHomesResource::collection($q->paginate($perPage));
    }

    public function store(StoreMashkokHomesRequest $request)
    {
        $user = $request->user();

        try {
            return DB::transaction(function () use ($request, $user) {
                $data = $request->validated();

                // ✅ NEW: picture upload (store ONLY disk path)
                if ($request->hasFile('picture')) {
                    $data['picture'] = $request->file('picture')->store('mashkok_homes/pictures', 'public');
                }

                $record = MashkokHomes::create($data);

                // documents (unchanged)
                if ($request->hasFile('_newDocs')) {
                    foreach ((array) $request->file('_newDocs') as $file) {
                        if (!$file) continue;

                        $path = $file->store("mashkok_homes/{$record->id}", 'public');

                        $record->documents()->create([
                            'file_path'     => $path,
                            'original_name' => $file->getClientOriginalName(),
                            'mime_type'     => $file->getClientMimeType(),
                            'size'          => $file->getSize(),
                        ]);
                    }
                }

                $record->load(['resource:id,name,code_number,phone_number', 'documents']);

                NotificationService::record(
                    $user,
                    'خانه‌های مشکوک',
                    'created',
                    'خانه مشکوک جدید ثبت شد',
                    'محل: ' . ($record->province ?? ''),
                    'mashkok-homes',
                    ['id' => $record->id],
                    'success'
                );

                return (new MashkokHomesResource($record))
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

    // ✅ NEW: show (so edit page gets picture too)
    public function show(MashkokHomes $mashkok_home)
    {
        $mashkok_home->load([
            'resource' => fn ($qr) => $qr->select('id', 'name', 'code_number', 'phone_number'),
            'documents',
        ]);

        return MashkokHomesResource::make($mashkok_home);
    }

    // ✅ NEW: update picture + docs delete
    public function update(UpdateMashkokHomesRequest $request, MashkokHomes $mashkok_home)
    {
        $user = $request->user();

        try {
            return DB::transaction(function () use ($request, $mashkok_home, $user) {
                $data = $request->validated();

                // ✅ NEW: picture update
                if ($request->hasFile('picture')) {
                    $this->deletePictureIfStored($mashkok_home->picture);
                    $data['picture'] = $request->file('picture')->store('mashkok_homes/pictures', 'public');
                }

                $mashkok_home->update($data);

                // ✅ docs remove (expects removed_document_ids[])
                $removed = $request->input('removed_document_ids', []);
                $removed = is_array($removed) ? array_values(array_filter($removed)) : [];
                if (!empty($removed)) {
                    $docs = $mashkok_home->documents()->whereIn('id', $removed)->get();
                    foreach ($docs as $doc) {
                        if ($doc->file_path) {
                            Storage::disk('public')->delete($doc->file_path);
                        }
                        $doc->delete();
                    }
                }

                // ✅ docs add (expects _newDocs[])
                if ($request->hasFile('_newDocs')) {
                    foreach ((array) $request->file('_newDocs') as $file) {
                        if (!$file) continue;

                        $path = $file->store("mashkok_homes/{$mashkok_home->id}", 'public');

                        $mashkok_home->documents()->create([
                            'file_path'     => $path,
                            'original_name' => $file->getClientOriginalName(),
                            'mime_type'     => $file->getClientMimeType(),
                            'size'          => $file->getSize(),
                        ]);
                    }
                }

                $mashkok_home->load(['resource:id,name,code_number,phone_number', 'documents']);

                NotificationService::record(
                    $user,
                    'خانه‌های مشکوک',
                    'updated',
                    'خانه مشکوک اصلاح شد',
                    'محل: ' . ($mashkok_home->province ?? ''),
                    'mashkok-homes',
                    ['id' => $mashkok_home->id],
                    'info'
                );

                return MashkokHomesResource::make($mashkok_home);
            });
        } catch (QueryException $e) {
            if ($this->isDuplicateKeyException($e)) {
                return response()->json($this->duplicateErrorPashto(), 422);
            }
            throw $e;
        }
    }

    // ✅ NEW: destroy deletes picture + docs files
    public function destroy(Request $request, MashkokHomes $mashkok_home)
    {
        $user = $request->user();

        return DB::transaction(function () use ($mashkok_home, $user) {
            $mashkok_home->load('documents');

            // delete picture
            $this->deletePictureIfStored($mashkok_home->picture);

            // delete docs files
            foreach ($mashkok_home->documents as $doc) {
                if ($doc->file_path) {
                    Storage::disk('public')->delete($doc->file_path);
                }
                $doc->delete();
            }

            $mashkok_home->delete();

            NotificationService::record(
                $user,
                'خانه‌های مشکوک',
                'deleted',
                'خانه مشکوک حذف شد',
                'ID: ' . ($mashkok_home->id ?? ''),
                'mashkok-homes',
                null,
                'warning'
            );

            return response()->noContent();
        });
    }

    // ✅ your ZIP function unchanged
    public function documentsZip(MashkokHomes $mashkok_home)
    {
        try {
            if (!class_exists(\ZipArchive::class)) {
                return response()->json([
                    'message' => 'PHP ZipArchive extension is not enabled'
                ], 500);
            }

            $mashkok_home->load('documents');

            if ($mashkok_home->documents->isEmpty()) {
                return response()->json(['message' => 'هیچ سندی برای این خانه موجود نیست'], 404);
            }

            $tmpDir = storage_path('app/tmp');
            if (!is_dir($tmpDir)) {
                mkdir($tmpDir, 0755, true);
            }

            $zipFileName = "mashkok_home_{$mashkok_home->id}_documents.zip";
            $zipPath = $tmpDir . '/' . uniqid() . '_' . $zipFileName;

            $zip = new \ZipArchive();
            $opened = $zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);

            if ($opened !== true) {
                return response()->json(['message' => 'ZIP ایجاد نشد'], 500);
            }

            $added = 0;
            $usedNames = [];

            foreach ($mashkok_home->documents as $index => $doc) {
                if (!$doc->file_path) continue;

                if (!Storage::disk('public')->exists($doc->file_path)) continue;

                $absolutePath = Storage::disk('public')->path($doc->file_path);
                if (!is_file($absolutePath)) continue;

                $originalName = $doc->original_name ?: basename($absolutePath);
                $originalName = basename($originalName);

                // ✅ ASCII-ONLY filenames → Windows safe
                $extension = pathinfo($originalName, PATHINFO_EXTENSION);
                $filename  = pathinfo($originalName, PATHINFO_FILENAME);

                $filename = preg_replace('/[^A-Za-z0-9 _-]/', '', $filename);
                $filename = trim($filename);

                if ($filename === '') {
                    $filename = 'document_' . ($index + 1);
                }

                $entryName = $filename . ($extension ? '.' . $extension : '');

                // Ensure uniqueness
                $counter = 1;
                $finalEntryName = $entryName;
                while (isset($usedNames[$finalEntryName])) {
                    $finalEntryName = $filename . '_' . $counter . ($extension ? '.' . $extension : '');
                    $counter++;
                }

                $usedNames[$finalEntryName] = true;

                if ($zip->addFile($absolutePath, $finalEntryName)) {
                    $added++;
                }
            }

            $zip->close();

            if ($added === 0) {
                @unlink($zipPath);
                return response()->json(['message' => 'هیچ فایل معتبری برای ZIP پیدا نشد'], 404);
            }

            return response()->streamDownload(function () use ($zipPath) {
                $stream = fopen($zipPath, 'rb');
                fpassthru($stream);
                fclose($stream);
                @unlink($zipPath);
            }, $zipFileName, [
                'Content-Type'        => 'application/zip',
                'Content-Disposition' => 'attachment; filename="'.$zipFileName.'"',
            ]);

        } catch (\Throwable $e) {
            \Log::error('MashkokHomes ZIP error', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'خطا در ساخت ZIP',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}
