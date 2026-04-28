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
        $page    = $request->query('page');
        $perPage = $request->query('perPage', $request->query('per_page', 10));

        $isValidPage    = is_numeric($page) && (int) $page > 0;
        $isValidPerPage = is_numeric($perPage) && (int) $perPage > 0;

        $search  = trim((string) $request->query('search', ''));
        $rid     = $request->query('resource_id');
        $fiftySixFormatId = $request->query('fifty_six_format_id');
        $searchField = trim((string) $request->query('search_field', ''));
        $searchValue = trim((string) $request->query('search_value', ''));

        $sortBy  = $request->input('sort_by', 'created_at');
        $sortDir = in_array($request->input('sort_dir'), ['asc', 'desc'], true)
            ? $request->input('sort_dir')
            : 'desc';

        $allowedSearchFields = [
            
         'information_id',

        'picture',
        'province',
        'zone',
        'district',
        'village',
        'specific_place',
        'street_number',
        'home_number',
        'coordinates',
        'home_type',
        'mashkok_type',
        'Province_addr',
        'shak_symbols',
        'note',
        'tafseel',
        'resource_id',
        ];

        $q = MashkokHomes::query()->with([
            'resource' => fn ($qr) => $qr->select('id', 'name', 'code_number', 'phone_number'),
            'documents',
        ])
        ->when($search !== '' && $searchField === '', function ($sub) use ($search) {
            $sub->where(function ($w) use ($search) {
                $w->where('province', 'like', "%{$search}%")
                    ->orWhere('district', 'like', "%{$search}%")
                    ->orWhere('village', 'like', "%{$search}%")
                    ->orWhere('home_type', 'like', "%{$search}%");
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
        ->when($rid, function ($sub) use ($rid) {
            $sub->where('resource_id', $rid);
        })
        ->when(
            is_numeric($fiftySixFormatId) && (int) $fiftySixFormatId > 0,
            function ($sub) use ($fiftySixFormatId) {
                $sub->where('id', (int) $fiftySixFormatId);
            }
        );

        $q->orderBy($sortBy, $sortDir);

        if ($isValidPage && $isValidPerPage) {
            return MashkokHomesResource::collection(
                $q->paginate((int) $perPage, ['*'], 'page', (int) $page)
            );
        }

        return MashkokHomesResource::collection($q->get());
    }

    /**
     * GET /mashkok-homes/filter-options
     */
    public function filterOptions(Request $request)
    {
        $field = trim((string) $request->query('field', ''));
        $q = trim((string) $request->query('q', ''));

        $allowedFields = [
            
         'information_id',

        'picture',
        'province',
        'zone',
        'district',
        'village',
        'specific_place',
        'street_number',
        'home_number',
        'coordinates',
        'home_type',
        'mashkok_type',
        'Province_addr',
        'shak_symbols',
        'note',
        'tafseel',
        'resource_id',
        ];

        if ($field === '' || !in_array($field, $allowedFields, true)) {
            return response()->json([
                'data' => [
                    'options' => [],
                ],
            ]);
        }

        $query = MashkokHomes::query()
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
                    'مشکوک کورونه',
                    'created',
                    'مشکوک کورونه ثبت شوو',
                    'ځای: ' . ($record->province ?? ''),
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
                    'مشکوک کورونه',
                    'updated',
                    'مشکوک کورونه اصلاح شوو',
                    'ځای: ' . ($mashkok_home->province ?? ''),
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
                'مشکوک کورونه',
                'deleted',
                'مشکوک کورونه حذف شوو',
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
    private function printSearchFields(): array
{
    return [
        'information_id',

        'picture',
        'province',
        'zone',
        'district',
        'village',
        'specific_place',
        'street_number',
        'home_number',
        'coordinates',
        'home_type',
        'mashkok_type',
        'Province_addr',
        'shak_symbols',
        'note',
        'tafseel',

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
    $resourceId       = $request->query('resource_id');
    $fiftySixFormatId = $request->query('fifty_six_format_id');
    $cardFilter       = trim((string) $request->query('card_filter', 'all'));

    $allowedFields = $this->printSearchFields();

    if ($search !== '' && $searchField === '') {
        $query->where(function ($q) use ($search) {
            $q->where('province', 'like', "%{$search}%")
                ->orWhere('zone', 'like', "%{$search}%")
                ->orWhere('district', 'like', "%{$search}%")
                ->orWhere('village', 'like', "%{$search}%")
                ->orWhere('specific_place', 'like', "%{$search}%")
                ->orWhere('street_number', 'like', "%{$search}%")
                ->orWhere('home_number', 'like', "%{$search}%")
                ->orWhere('coordinates', 'like', "%{$search}%")
                ->orWhere('home_type', 'like', "%{$search}%")
                ->orWhere('mashkok_type', 'like', "%{$search}%")
                ->orWhere('Province_addr', 'like', "%{$search}%")
                ->orWhere('shak_symbols', 'like', "%{$search}%")
                ->orWhere('tafseel', 'like', "%{$search}%")
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
            } elseif ($searchField === 'resource_id') {
                $query->where('resource_id', $value);
            } else {
                $query->where($searchField, 'like', "%{$value}%");
            }
        }
    }

    if ($resourceId) {
        $query->where('resource_id', $resourceId);
    }

    if (is_numeric($fiftySixFormatId) && (int) $fiftySixFormatId > 0) {
        $query->where('id', (int) $fiftySixFormatId);
    }

    if ($cardFilter === 'withCoordinates') {
        $query->whereNotNull('coordinates')
            ->whereRaw("TRIM(coordinates) != ''");
    } elseif ($cardFilter === 'withResource') {
        $query->whereNotNull('resource_id')
            ->whereRaw("TRIM(resource_id) != ''");
    } elseif ($cardFilter === 'withHomeType') {
        $query->whereNotNull('home_type')
            ->whereRaw("TRIM(home_type) != ''");
    }

    return $query;
}

public function printView(Request $request)
{
    $query = MashkokHomes::query()
        ->with([
            'resource' => fn ($qr) => $qr->select('id', 'name', 'code_number', 'phone_number'),
            'documents',
        ]);

    $this->applyPrintFilters($query, $request);

    $rows = $query
        ->orderByDesc('id')
        ->get()
        ->map(function ($row) {
            $resourceCode = $row->resource?->code_number;
            $resourcePhone = $row->resource?->phone_number;

            $row->resource_code = $resourceCode ?? '-';
            $row->resource_phone = $resourcePhone ?? '-';

            // Because your header key is resource_id but title says "کود منبع"
            $row->resource_id = $resourceCode ?? $row->resource_id ?? '-';

            return $row;
        });

    $selectedColumns = $request->input('selected_columns', []);
    $columnLabels    = $request->input('column_labels', []);
    $printLabels     = $request->input('print_labels', []);
    $locale          = $request->input('locale', 'fa');
    $title           = $request->input('title', 'چاپ خانه‌های مشکوک');

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
    $mashkokHome = MashkokHomes::findOrFail($id);

    return $this->removePictureFileAndClear($mashkokHome);
}  
}