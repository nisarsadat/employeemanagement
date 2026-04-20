<?php

namespace App\Http\Controllers\fifthyFormats;

use App\Http\Controllers\Controller;
use App\Http\Requests\fifthyFormats\StoreCaptiveRequest;
use App\Http\Requests\fifthyFormats\UpdateCaptiveRequest;
use App\Http\Resources\fifthyFormats\CaptiveResource;
use App\Models\fifthyFormats\Captive;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\QueryException;
use App\Services\NotificationService; // 🔔 notifications

class CaptiveController extends Controller
{
    private function allowedSearchFields(): array
    {
        $table = (new Captive())->getTable();

        $candidates = [
            'information_id',
        'name',
        'picture',
        'nick_name',
        'father_name',
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
        'specific_form',
        'band_distince',
        'now_activity',
        'phone_number',
        'note',
        'Province_addr',
        'coordinates',
        'resource_id',
        ];

        return array_values(array_filter($candidates, fn ($field) => Schema::hasColumn($table, $field)));
    }

    public function index(Request $request)
    {
        $page    = $request->query('page');
        $perPage = $request->query('perPage', $request->query('per_page'));

        $isValidPage    = is_numeric($page) && (int) $page > 0;
        $isValidPerPage = is_numeric($perPage) && (int) $perPage > 0;

        $search = trim((string) $request->query('search', ''));
        $rid    = $request->query('resource_id');
        $searchField = trim((string) $request->query('search_field', ''));
        $searchValue = trim((string) $request->query('search_value', ''));

        $sortable = ['id', 'name', 'created_at', 'updated_at'];
        $sortBy   = in_array($request->query('sort_by', 'created_at'), $sortable, true)
            ? $request->query('sort_by', 'created_at')
            : 'created_at';

        $sortDir  = strtolower((string) $request->query('sort_dir', 'desc'));
        if (!in_array($sortDir, ['asc', 'desc'], true)) {
            $sortDir = 'desc';
        }

        $allowedSearchFields = $this->allowedSearchFields();

        $q = Captive::query()
            ->leftJoin('resources', 'captives.resource_id', '=', 'resources.id')
            ->select([
                'captives.*',
                'resources.code_number as resource_code',
                'resources.phone_number as resource_phone_number',
            ])
            ->with([
                'resource:id,name,code_number,phone_number',
                'documents',
            ])
            ->when($search !== '' && $searchField === '', function ($sub) use ($search) {
                $sub->where(function ($x) use ($search) {
                    $x->where('captives.name', 'like', "%{$search}%")
                      ->orWhere('captives.father_name', 'like', "%{$search}%")
                      ->orWhere('captives.nick_name', 'like', "%{$search}%")
                      ->orWhere('captives.note', 'like', "%{$search}%");
                });
            })
            ->when(
                $searchField !== '' && in_array($searchField, $allowedSearchFields, true),
                function ($sub) use ($searchField, $searchValue, $search) {
                    $value = $searchValue !== '' ? $searchValue : $search;

                    if ($value !== '') {
                        $sub->where("captives.{$searchField}", 'like', "%{$value}%");
                    }
                }
            )
            ->when($rid, fn ($sub) => $sub->where('captives.resource_id', $rid))
            ->orderBy($sortBy, $sortDir);

        if ($isValidPage && $isValidPerPage) {
            return CaptiveResource::collection(
                $q->paginate((int) $perPage, ['*'], 'page', (int) $page)
            );
        }

        return CaptiveResource::collection($q->get());
    }

    /**
     * GET /captives/filter-options
     */
    public function filterOptions(Request $request)
    {
        $field = trim((string) $request->query('field', ''));
        $q     = trim((string) $request->query('q', ''));

        $allowedFields = $this->allowedSearchFields();

        if ($field === '' || !in_array($field, $allowedFields, true)) {
            return response()->json([
                'data' => [
                    'options' => [],
                ],
            ]);
        }

        $query = Captive::query()
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

    public function show(Captive $captive)
    {
        $captive->load([
            'resource:id,name,code_number,phone_number',
            'documents',
        ]);

        return new CaptiveResource($captive);
    }

    public function store(StoreCaptiveRequest $request)
    {
        $user = $request->user(); // 🔔
        $data = $request->validated();

        try {
            return DB::transaction(function () use ($request, $data, $user) {

                // ✅ picture
                if ($request->hasFile('picture')) {
                    $path = $request->file('picture')->store('uploads/captives', 'public');
                    $data['picture'] = "storage/{$path}";
                } else {
                    if (array_key_exists('picture', $data) && !is_string($data['picture'])) {
                        unset($data['picture']);
                    }
                }

                $captive = Captive::create($data);

                // ✅ documents[]
                if ($request->hasFile('documents')) {
                    foreach ((array) $request->file('documents') as $file) {
                        if (!$file || !$file->isValid()) continue;

                        $docPath = $file->store("uploads/captive-documents/{$captive->id}", 'public');

                        $captive->documents()->create([
                            'file_name'  => $file->getClientOriginalName(),
                            'file_path'  => $docPath,
                            'mime_type'  => $file->getClientMimeType(),
                            'size_bytes' => $file->getSize(),
                        ]);
                    }
                }

                $captive->load([
                    'resource:id,name,code_number,phone_number',
                    'documents',
                ]);

                // 🔔 Notification: CREATED
                NotificationService::record(
                    $user,
                    module: 'راخوشي شوي بندیان',
                    action: 'created',
                    title: 'نوی ریکارډ ثبت شو',
                    message: 'نوم: ' . ($captive->name ?? ''),
                    routeName: 'captives',
                    routeParams: ['id' => $captive->id],
                    level: 'success',
                    changes: null
                );

                return (new CaptiveResource($captive))
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

    public function update(UpdateCaptiveRequest $request, Captive $captive)
    {
        $user = $request->user(); // 🔔
        $data = $request->validated();

        try {
            return DB::transaction(function () use ($request, $data, $captive, $user) {

                // 🧮 changes BEFORE update
                $original = $captive->getOriginal();
                $changes  = [];

                foreach ($data as $field => $newValue) {
                    if (array_key_exists($field, $original) && $original[$field] != $newValue) {
                        $changes[$field] = [
                            'old' => $original[$field],
                            'new' => $newValue,
                        ];
                    }
                }

                // ✅ picture replace
                if ($request->hasFile('picture')) {
                    if ($captive->picture && str_starts_with($captive->picture, 'storage/')) {
                        Storage::disk('public')->delete(str_replace('storage/', '', $captive->picture));
                    }

                    $path = $request->file('picture')->store('uploads/captives', 'public');
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

                $captive->update($data);

                // ✅ delete selected old documents
                $removed = (array) $request->input('removed_document_ids', []);
                if (!empty($removed)) {
                    $docs = $captive->documents()->whereIn('id', $removed)->get();
                    foreach ($docs as $doc) {
                        if ($doc->file_path && Storage::disk('public')->exists($doc->file_path)) {
                            Storage::disk('public')->delete($doc->file_path);
                        }
                        $doc->delete();
                    }
                }

                // ✅ add new documents[]
                if ($request->hasFile('documents')) {
                    foreach ((array) $request->file('documents') as $file) {
                        if (!$file || !$file->isValid()) continue;

                        $docPath = $file->store("uploads/captive-documents/{$captive->id}", 'public');

                        $captive->documents()->create([
                            'file_name'  => $file->getClientOriginalName(),
                            'file_path'  => $docPath,
                            'mime_type'  => $file->getClientMimeType(),
                            'size_bytes' => $file->getSize(),
                        ]);
                    }
                }

                $captive->load([
                    'resource:id,name,code_number,phone_number',
                    'documents',
                ]);

                // 🔔 Notification: UPDATED
                NotificationService::record(
                    $user,
                    module: 'راخوشي شوي بندیان',
                    action: 'updated',
                    title: 'ریکارډ اصلاح شو',
                    message: 'نوم: ' . ($captive->name ?? ''),
                    routeName: 'captives',
                    routeParams: ['id' => $captive->id],
                    level: 'info',
                    changes: !empty($changes) ? $changes : null
                );

                return new CaptiveResource($captive);
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

    public function destroy(Request $request, Captive $captive)
    {
        $user = $request->user(); // 🔔

        return DB::transaction(function () use ($captive, $user) {

            // delete picture file if stored locally
            if ($captive->picture && str_starts_with($captive->picture, 'storage/')) {
                Storage::disk('public')->delete(str_replace('storage/', '', $captive->picture));
            }

            // delete docs files + rows (safe even if you already do it in model booted())
            $captive->load('documents');
            foreach ($captive->documents as $doc) {
                if ($doc->file_path && Storage::disk('public')->exists($doc->file_path)) {
                    Storage::disk('public')->delete($doc->file_path);
                }
                $doc->delete();
            }

            $name = $captive->name;
            $captive->delete();

            // 🔔 Notification: DELETED
            NotificationService::record(
                $user,
                module: 'راخوشي شوي بندیان',
                action: 'deleted',
                title: 'ریکارډ حذف شو',
                message: 'نوم: ' . ($name ?? ''),
                routeName: null,
                routeParams: ['captives'],
                level: 'warning',
                changes: null
            );

            return response()->noContent();
        });
    }
}