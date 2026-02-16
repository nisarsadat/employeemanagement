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

    // ✅ SAME STYLE AS SaeenFasad (storage/ prefix handling)
    private function deletePictureIfStored(?string $picture): void
    {
        if (!$picture) return;

        $path = $picture;

        // if saved as "storage/xxxx" convert to "xxxx"
        if (str_starts_with($path, 'storage/')) {
            $path = str_replace('storage/', '', $path);
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

                /**
                 * ✅ picture: file OR string (SAME AS SaeenFasad)
                 * - if file: store in public disk and save as "storage/..."
                 * - else: if picture exists but not string => unset
                 */
                if ($request->hasFile('picture')) {
                    $path = $request->file('picture')->store('uploads/mashkok-homes', 'public');
                    $data['picture'] = "storage/{$path}";
                } else {
                    if (array_key_exists('picture', $data) && !is_string($data['picture'])) {
                        unset($data['picture']);
                    }
                }

                $record = MashkokHomes::create($data);

                // ✅ documents (keep your existing behavior)
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

    public function show(MashkokHomes $mashkok_home)
    {
        $mashkok_home->load([
            'resource' => fn ($qr) => $qr->select('id', 'name', 'code_number', 'phone_number'),
            'documents',
        ]);

        return MashkokHomesResource::make($mashkok_home);
    }

    public function update(UpdateMashkokHomesRequest $request, MashkokHomes $mashkok_home)
    {
        $user = $request->user();

        try {
            return DB::transaction(function () use ($request, $mashkok_home, $user) {
                $data = $request->validated();

                /**
                 * ✅ picture: replace file if uploaded (SAME AS SaeenFasad)
                 */
                if ($request->hasFile('picture')) {
                    $this->deletePictureIfStored($mashkok_home->picture);

                    $path = $request->file('picture')->store('uploads/mashkok-homes', 'public');
                    $data['picture'] = "storage/{$path}";
                } else {
                    if (array_key_exists('picture', $data) && !is_string($data['picture'])) {
                        unset($data['picture']);
                    }
                }

                $mashkok_home->update($data);

                // ✅ remove documents by ids (keep your existing behavior)
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

                // ✅ add new documents (keep your existing behavior)
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

    public function destroy(Request $request, MashkokHomes $mashkok_home)
    {
        $user = $request->user();

        return DB::transaction(function () use ($mashkok_home, $user) {
            $mashkok_home->load('documents');

            // ✅ delete picture file (SaeenFasad style)
            $this->deletePictureIfStored($mashkok_home->picture);

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

    // ✅ FIXED: more reliable ZIP download for Windows
    public function documentsZip(MashkokHomes $mashkok_home)
    {
        try {
            if (!class_exists(\ZipArchive::class)) {
                return response()->json(['message' => 'PHP ZipArchive extension is not enabled'], 500);
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
            $zipPath = $tmpDir . '/' . uniqid('zip_', true) . '_' . $zipFileName;

            $zip = new \ZipArchive();
            if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
                return response()->json(['message' => 'ZIP ایجاد نشد'], 500);
            }

            $usedNames = [];
            $added = 0;

            foreach ($mashkok_home->documents as $i => $doc) {
                if (!$doc->file_path) continue;
                if (!Storage::disk('public')->exists($doc->file_path)) continue;

                $abs = Storage::disk('public')->path($doc->file_path);
                if (!is_file($abs)) continue;

                $orig = basename($doc->original_name ?: $abs);
                $ext  = pathinfo($orig, PATHINFO_EXTENSION);
                $name = preg_replace('/[^A-Za-z0-9 _-]/', '', pathinfo($orig, PATHINFO_FILENAME));
                $name = trim($name) ?: 'document_' . ($i + 1);

                $final = $name . ($ext ? '.' . $ext : '');
                $c = 1;
                while (isset($usedNames[$final])) {
                    $final = $name . '_' . $c++ . ($ext ? '.' . $ext : '');
                }

                $usedNames[$final] = true;
                if ($zip->addFile($abs, $final)) {
                    $added++;
                }
            }

            $zip->close();

            if ($added === 0) {
                @unlink($zipPath);
                return response()->json(['message' => 'هیچ فایل معتبری برای ZIP پیدا نشد'], 404);
            }

            // ✅ CRITICAL: remove BOM / output
            while (ob_get_level() > 0) {
                ob_end_clean();
            }

            return response()
                ->download($zipPath, $zipFileName, [
                    'Content-Type' => 'application/zip',
                ])
                ->deleteFileAfterSend(true);

        } catch (\Throwable $e) {
            \Log::error('MashkokHomes ZIP error', [
                'id' => $mashkok_home->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'خطا در ساخت ZIP',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
