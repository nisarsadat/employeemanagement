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
use App\Services\NotificationService;
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

        $q = SaeenFasad::query()
            ->with([
                'resource:id,name,code_number,phone_number',
                'documents',
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
            return SaeenFasadResource::collection($data);
        }

        return SaeenFasadResource::collection($q->get());
    }

    /**
     * GET /saeen-fasadas/filter-options
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

        $query = SaeenFasad::query()
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
     */
    public function store(StoreSaeenFasadRequest $request)
    {
        $user = $request->user();
        $data = $request->validated();

        try {
            return DB::transaction(function () use ($request, $data, $user) {
                if ($request->hasFile('picture')) {
                    $path = $request->file('picture')->store('uploads/saeen-fasads', 'public');
                    $data['picture'] = "storage/{$path}";
                } else {
                    if (array_key_exists('picture', $data) && !is_string($data['picture'])) {
                        unset($data['picture']);
                    }
                }

                $item = SaeenFasad::create($data);

                if ($request->hasFile('documents')) {
                    foreach ($request->file('documents') as $file) {
                        if (!$file) {
                            continue;
                        }

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

                NotificationService::record(
                    $user,
                    module: 'ساعین بالفساد',
                    action: 'created',
                    title: 'نوی ریکارډ ثبت شو',
                    message: 'نوم: ' . ($item->name ?? ''),
                    routeName: 'saeen-fasads',
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
     */
    public function update(UpdateSaeenFasadRequest $request, SaeenFasad $saeenFasad)
    {
        $user = $request->user();
        $data = $request->validated();

        try {
            return DB::transaction(function () use ($request, $data, $saeenFasad, $user) {
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

                $saeenFasad->update($data);

                if ($request->hasFile('documents')) {
                    foreach ($request->file('documents') as $file) {
                        if (!$file) {
                            continue;
                        }

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

                NotificationService::record(
                    $user,
                    module: 'ساعین بالفساد',
                    action: 'updated',
                    title: 'ریکارډ اصلاح شو',
                    message: 'نوم: ' . ($saeenFasad->name ?? ''),
                    routeName: 'saeen-fasads',
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
     */
    public function destroy(Request $request, SaeenFasad $saeenFasad)
    {
        $user = $request->user();

        return DB::transaction(function () use ($saeenFasad, $user) {
            $name = $saeenFasad->name;

            if ($saeenFasad->picture && str_starts_with($saeenFasad->picture, 'storage/')) {
                $old = str_replace('storage/', '', $saeenFasad->picture);
                Storage::disk('public')->delete($old);
            }

            $docs = $saeenFasad->documents()->get();
            foreach ($docs as $doc) {
                if ($doc->file_path && str_starts_with($doc->file_path, 'storage/')) {
                    $old = str_replace('storage/', '', $doc->file_path);
                    Storage::disk('public')->delete($old);
                }
            }

            $saeenFasad->delete();

            NotificationService::record(
                $user,
                module: 'ساعین بالفساد',
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

    private function safeZipName(string $name): string
    {
        $name = basename($name);
        $name = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $name) ?: $name;
        $name = preg_replace('/[^A-Za-z0-9\.\-_]/', '_', $name);
        $name = preg_replace('/_+/', '_', $name);
        $name = trim($name, '_');

        return $name !== '' ? $name : 'file';
    }

    /**
     * GET /saeen-fasadas/{id}/download-zip
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

        if ($saeenFasad->picture) {
            $picPath = (string) $saeenFasad->picture;

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

        foreach ($saeenFasad->documents as $doc) {
            $docPath = (string) ($doc->file_path ?? '');
            if ($docPath === '') {
                continue;
            }

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