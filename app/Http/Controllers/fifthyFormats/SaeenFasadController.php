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
use App\Exports\SaeenFasadExport;
use Maatwebsite\Excel\Facades\Excel;
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
            'resource_id',
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
            'resource_id',
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
 private function allowedSearchFields(): array
{
    return [
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
        'resource_id',
        'resource_code',
        'resource_phone_number',
        'band_distince',
        'band_place',
        'old_job',
        'now_job',
        'tafseel',
        'note',
    ];
}

private function applyPrintFilters($query, Request $request)
{
    $search           = trim((string) $request->query('search', ''));
    $searchField      = trim((string) $request->query('search_field', ''));
    $searchValue      = trim((string) $request->query('search_value', ''));
    $fiftySixFormatId = $request->query('fifty_six_format_id');
    $cardFilter       = trim((string) $request->query('card_filter', 'all'));

    $allowedSearchFields = $this->allowedSearchFields();

    if ($search !== '' && $searchField === '') {
        $query->where(function ($x) use ($search) {
            $x->where('name', 'like', "%{$search}%")
                ->orWhere('father_name', 'like', "%{$search}%")
                ->orWhere('nick_name', 'like', "%{$search}%")
                ->orWhere('main_province', 'like', "%{$search}%")
                ->orWhere('main_district', 'like', "%{$search}%")
                ->orWhere('main_village', 'like', "%{$search}%")
                ->orWhere('now_province', 'like', "%{$search}%")
                ->orWhere('now_zone', 'like', "%{$search}%")
                ->orWhere('now_district', 'like', "%{$search}%")
                ->orWhere('now_village', 'like', "%{$search}%")
                ->orWhere('specific_place', 'like', "%{$search}%")
                ->orWhere('street_number', 'like', "%{$search}%")
                ->orWhere('home_number', 'like', "%{$search}%")
                ->orWhere('coordinates', 'like', "%{$search}%")
                ->orWhere('responsiability', 'like', "%{$search}%")
                ->orWhere('activity_place', 'like', "%{$search}%")
                ->orWhere('friends_number', 'like', "%{$search}%")
                ->orWhere('phone_number', 'like', "%{$search}%")
                ->orWhere('band_distince', 'like', "%{$search}%")
                ->orWhere('band_place', 'like', "%{$search}%")
                ->orWhere('old_job', 'like', "%{$search}%")
                ->orWhere('now_job', 'like', "%{$search}%")
                ->orWhere('tafseel', 'like', "%{$search}%")
                ->orWhere('note', 'like', "%{$search}%")
                ->orWhereHas('resource', function ($r) use ($search) {
                    $r->where('code_number', 'like', "%{$search}%")
                        ->orWhere('phone_number', 'like', "%{$search}%");
                });
        });
    }

    if ($searchField !== '' && in_array($searchField, $allowedSearchFields, true)) {
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
                $query->where($searchField, 'like', "%{$value}%");
            }
        }
    }

    if (is_numeric($fiftySixFormatId) && (int) $fiftySixFormatId > 0) {
        $query->where('id', (int) $fiftySixFormatId);
    }

    if ($cardFilter === 'withCoordinates') {
        $query->whereNotNull('coordinates')
            ->whereRaw("TRIM(coordinates) != ''");
    } elseif ($cardFilter === 'withPhone') {
        $query->whereNotNull('phone_number')
            ->whereRaw("TRIM(phone_number) != ''");
    } elseif ($cardFilter === 'withJob') {
        $query->whereNotNull('now_job')
            ->whereRaw("TRIM(now_job) != ''");
    }

    return $query;
}

public function printView(Request $request)
{
    $query = SaeenFasad::query()
        ->with([
            'resource:id,name,code_number,phone_number',
            'documents',
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

            $row->resource_phone_number =
                $row->resource?->phone_number
                ?? '-';

            return $row;
        });

    $selectedColumns = $request->input('selected_columns', []);
    $columnLabels    = $request->input('column_labels', []);
    $printLabels     = $request->input('print_labels', []);
    $locale          = $request->input('locale', 'fa');
    $title           = $request->input('title', 'د ساعین بالفساد چاپ');

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
    $saeenFasad = SaeenFasad::findOrFail($id);

    return $this->removePictureFileAndClear($saeenFasad);
}  

    public function exportExcel(Request $request)
    {
        $filters = $request->only(['search', 'search_field', 'search_value', 'resource_id']);
        return Excel::download(new SaeenFasadExport($filters), 'saeen_fasad_' . now()->format('Y_m_d_His') . '.xlsx');
    }
}