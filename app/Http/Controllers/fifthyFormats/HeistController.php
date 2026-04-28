<?php

namespace App\Http\Controllers\fifthyFormats;

use App\Http\Controllers\Controller;
use App\Http\Requests\fifthyFormats\StoreHeistRequest;
use App\Http\Requests\fifthyFormats\UpdateHeistRequest;
use App\Http\Resources\fifthyFormats\HeistResource;
use App\Models\fifthyFormats\Heist;
use App\Models\fifthyFormats\HeistDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\QueryException;
use ZipArchive;
use App\Services\NotificationService; // 🔔 notifications

class HeistController extends Controller
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
        'picture',
        'father_name',
        'main_province',
        'main_district',
        'main_village',
        'now_province',
        'now_zone',
        'now_district',
        'now_village',
        'specific_place',
        'Province_addr',
        'street_number',
        'home_number',
        'coordinates',
        'old_job',
        'now_job',
        'phone_number',
        'done_roberies',
        'gun_type',
        'note',
        'resource_id',
        ];

        $q = Heist::query()
            ->with([
                'resource:id,name,code_number,phone_number',
                'documents:id,heist_id,file_path,original_name,mime_type,size,created_at',
            ])
            ->when($search !== '' && $searchField === '', function ($sub) use ($search) {
                $sub->where(function ($x) use ($search) {
                    $x->where('name', 'like', "%{$search}%")
                        ->orWhere('father_name', 'like', "%{$search}%")
                        ->orWhere('gun_type', 'like', "%{$search}%")
                        ->orWhere('note', 'like', "%{$search}%");
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
            return HeistResource::collection(
                $q->paginate((int) $perPage, ['*'], 'page', (int) $page)
            );
        }

        return HeistResource::collection($q->get());
    }

    /**
     * GET /heists/filter-options
     */
    public function filterOptions(Request $request)
    {
        $field = trim((string) $request->query('field', ''));
        $q = trim((string) $request->query('q', ''));

        $allowedFields = [
           
         'information_id',

        'name',
        'picture',
        'father_name',
        'main_province',
        'main_district',
        'main_village',
        'now_province',
        'now_zone',
        'now_district',
        'now_village',
        'specific_place',
        'Province_addr',
        'street_number',
        'home_number',
        'coordinates',
        'old_job',
        'now_job',
        'phone_number',
        'done_roberies',
        'gun_type',
        'note',
        'resource_id',
        ];

        if ($field === '' || !in_array($field, $allowedFields, true)) {
            return response()->json([
                'data' => [
                    'options' => [],
                ],
            ]);
        }

        $query = Heist::query()
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

    public function show(Heist $heist)
    {
        $heist->load([
            'resource:id,name,code_number,phone_number',
            'documents:id,heist_id,file_path,original_name,mime_type,size,created_at',
        ]);

        return new HeistResource($heist);
    }

    public function store(StoreHeistRequest $request)
    {
        $user = $request->user(); // 🔔
        $data = $request->validated();

        try {
            return DB::transaction(function () use ($request, $data, $user) {

                // ✅ picture upload
                if ($request->hasFile('picture')) {
                    $path = $request->file('picture')->store('uploads/heists', 'public');
                    $data['picture'] = "storage/{$path}";
                } else {
                    if (array_key_exists('picture', $data) && !is_string($data['picture'])) {
                        unset($data['picture']);
                    }
                }

                $heist = Heist::create($data);

                // ✅ documents upload (multiple): documents[]
                if ($request->hasFile('documents')) {
                    foreach ((array) $request->file('documents') as $file) {
                        if (!$file) continue;

                        $docPath = $file->store("uploads/heists/{$heist->id}/documents", 'public');

                        HeistDocument::create([
                            'heist_id'      => $heist->id,
                            'file_path'     => "storage/{$docPath}",
                            'original_name' => $file->getClientOriginalName(),
                            'mime_type'     => $file->getClientMimeType(),
                            'size'          => $file->getSize(),
                        ]);
                    }
                }

                $heist->load([
                    'resource:id,name,code_number,phone_number',
                    'documents:id,heist_id,file_path,original_name,mime_type,size,created_at',
                ]);

                // 🔔 Notification: CREATED
                NotificationService::record(
                    $user,
                    module: 'سرقت کوونکي',
                    action: 'created',
                    title: 'نوی ریکارډ ثبت شو',
                    message: 'نوم: ' . ($heist->name ?? ''),
                    routeName: 'heists',
                    routeParams: ['id' => $heist->id],
                    level: 'success',
                    changes: null
                );

                return (new HeistResource($heist))
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

    public function update(UpdateHeistRequest $request, Heist $heist)
    {
        $user = $request->user(); // 🔔
        $data = $request->validated();

        try {
            return DB::transaction(function () use ($request, $data, $heist, $user) {

                // 🧮 track changes
                $original = $heist->getOriginal();
                $changes  = [];

                foreach ($data as $field => $newValue) {
                    if (array_key_exists($field, $original) && $original[$field] != $newValue) {
                        $changes[$field] = [
                            'old' => $original[$field],
                            'new' => $newValue,
                        ];
                    }
                }

                // ✅ picture upload
                if ($request->hasFile('picture')) {
                    if ($heist->picture && str_starts_with($heist->picture, 'storage/')) {
                        Storage::disk('public')->delete(
                            str_replace('storage/', '', $heist->picture)
                        );
                    }

                    $path = $request->file('picture')->store('uploads/heists', 'public');
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

                // ✅ remove documents by ids: removed_document_ids[]
                $removedIds = $request->input('removed_document_ids', []);
                if (is_array($removedIds) && !empty($removedIds)) {
                    $docsToDelete = HeistDocument::query()
                        ->where('heist_id', $heist->id)
                        ->whereIn('id', $removedIds)
                        ->get();

                    foreach ($docsToDelete as $doc) {
                        if ($doc->file_path && str_starts_with($doc->file_path, 'storage/')) {
                            Storage::disk('public')->delete(
                                str_replace('storage/', '', $doc->file_path)
                            );
                        }
                        $doc->delete();
                    }

                    $changes['documents_removed'] = [
                        'old' => null,
                        'new' => $removedIds,
                    ];
                }

                // ✅ add new documents (multiple): documents[]
                if ($request->hasFile('documents')) {
                    foreach ((array) $request->file('documents') as $file) {
                        if (!$file) continue;

                        $docPath = $file->store("uploads/heists/{$heist->id}/documents", 'public');

                        $doc = HeistDocument::create([
                            'heist_id'      => $heist->id,
                            'file_path'     => "storage/{$docPath}",
                            'original_name' => $file->getClientOriginalName(),
                            'mime_type'     => $file->getClientMimeType(),
                            'size'          => $file->getSize(),
                        ]);

                        $changes['documents_added'][] = [
                            'id'   => $doc->id,
                            'name' => $doc->original_name,
                        ];
                    }
                }

                $heist->update($data);

                $heist->load([
                    'resource:id,name,code_number,phone_number',
                    'documents:id,heist_id,file_path,original_name,mime_type,size,created_at',
                ]);

                // 🔔 Notification: UPDATED
                NotificationService::record(
                    $user,
                    module: 'سرقت کوونکي',
                    action: 'updated',
                    title: 'ریکارډ اصلاح شو',
                    message: 'نوم: ' . ($heist->name ?? ''),
                    routeName: 'heists',
                    routeParams: ['id' => $heist->id],
                    level: 'info',
                    changes: !empty($changes) ? $changes : null
                );

                return new HeistResource($heist);
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

    public function destroy(Request $request, Heist $heist)
    {
        $user = $request->user(); // 🔔

        return DB::transaction(function () use ($heist, $user) {

            // ✅ delete picture file
            if ($heist->picture && str_starts_with($heist->picture, 'storage/')) {
                Storage::disk('public')->delete(
                    str_replace('storage/', '', $heist->picture)
                );
            }

            // ✅ delete all documents files + rows
            $heist->load('documents');
            foreach ($heist->documents as $doc) {
                if ($doc->file_path && str_starts_with($doc->file_path, 'storage/')) {
                    Storage::disk('public')->delete(
                        str_replace('storage/', '', $doc->file_path)
                    );
                }
            }

            $name = $heist->name;
            $heist->delete();

            // 🔔 Notification: DELETED
            NotificationService::record(
                $user,
                module: 'سرقت کوونکي',
                action: 'deleted',
                title: 'ریکارډ حذف شو',
                message: 'نوم: ' . ($name ?? ''),
                routeName: null,
                routeParams: null,
                level: 'warning',
                changes: null
            );

            return response()->noContent();
        });
    }

    public function downloadDocumentsZip(Heist $heist)
    {
        // 🔒 Defensive: stop any output buffering (VERY IMPORTANT)
        while (ob_get_level() > 0) {
            @ob_end_clean();
        }

        $docs = $heist->documents()->get();

        if ($docs->isEmpty()) {
            abort(404, 'هیچ سندی موجود نیست.');
        }

        // ✅ Create temp zip file (Windows-safe, same as Information controller)
        $zipName = "heist_{$heist->id}_documents.zip";
        $tmpFile = tempnam(sys_get_temp_dir(), 'zip_');

        $zip = new \ZipArchive();
        $ok = $zip->open($tmpFile, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
        if ($ok !== true) {
            abort(500, 'Cannot create ZIP file.');
        }

        $added = 0;

        foreach ($docs as $doc) {
            if (empty($doc->file_path)) {
                continue;
            }

            /**
             * 🔥 PATH NORMALIZATION (THIS IS THE FIX)
             * Supports:
             *  - uploads/xxx.pdf
             *  - storage/uploads/xxx.pdf
             *  - /storage/uploads/xxx.pdf
             */
            $p = (string) $doc->file_path;

            if (str_starts_with($p, 'storage/')) {
                $p = substr($p, strlen('storage/'));
            }

            if (str_starts_with($p, '/storage/')) {
                $p = substr($p, strlen('/storage/'));
            }

            $p = ltrim($p, '/');

            // Absolute path on public disk
            $absolute = Storage::disk('public')->path($p);

            // Only add valid, readable files
            if (is_file($absolute) && is_readable($absolute)) {
                $nameInZip = $doc->original_name ?: basename($absolute);

                // Windows-safe filename
                $nameInZip = ltrim(
                    str_replace(['\\', ':'], ['/', '-'], $nameInZip),
                    '/'
                );

                $zip->addFile($absolute, $nameInZip);
                $added++;
            }
        }

        // Finalize ZIP
        $zip->close();
        clearstatcache(true, $tmpFile);

        // Nothing valid → stop (prevents broken ZIPs)
        if ($added === 0 || !filesize($tmpFile)) {
            @unlink($tmpFile);
            abort(404, 'No valid files to download.');
        }

        // ✅ Stream finished ZIP (exactly like Information controller)
        return response()->download($tmpFile, $zipName, [
            'Content-Type' => 'application/zip',
        ])->deleteFileAfterSend(true);
    }
    private function printSearchFields(): array
{
    return [
        'information_id',
        'name',
        'father_name',
        'main_province',
        'main_district',
        'main_village',
        'now_province',
        'now_zone',
        'now_district',
        'now_village',
        'specific_place',
        'Province_addr',
        'street_number',
        'home_number',
        'coordinates',
        'old_job',
        'now_job',
        'phone_number',
        'done_roberies',
        'gun_type',
        'note',
        'resource_id',
        'resource_code',
        'resource_phone',
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
                ->orWhere('father_name', 'like', "%{$search}%")
                ->orWhere('main_province', 'like', "%{$search}%")
                ->orWhere('main_district', 'like', "%{$search}%")
                ->orWhere('main_village', 'like', "%{$search}%")
                ->orWhere('now_province', 'like', "%{$search}%")
                ->orWhere('now_zone', 'like', "%{$search}%")
                ->orWhere('now_district', 'like', "%{$search}%")
                ->orWhere('now_village', 'like', "%{$search}%")
                ->orWhere('specific_place', 'like', "%{$search}%")
                ->orWhere('Province_addr', 'like', "%{$search}%")
                ->orWhere('street_number', 'like', "%{$search}%")
                ->orWhere('home_number', 'like', "%{$search}%")
                ->orWhere('coordinates', 'like', "%{$search}%")
                ->orWhere('old_job', 'like', "%{$search}%")
                ->orWhere('now_job', 'like', "%{$search}%")
                ->orWhere('phone_number', 'like', "%{$search}%")
                ->orWhere('done_roberies', 'like', "%{$search}%")
                ->orWhere('gun_type', 'like', "%{$search}%")
                ->orWhere('note', 'like', "%{$search}%")
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
    $query = Heist::query()
        ->with([
            'resource:id,name,code_number,phone_number',
            'documents:id,heist_id,file_path,original_name,mime_type,size,created_at',
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
    $title           = $request->input('title', 'چاپ سرقت');

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
    $heist = Heist::findOrFail($id);

    return $this->removePictureFileAndClear($heist);
}  
}