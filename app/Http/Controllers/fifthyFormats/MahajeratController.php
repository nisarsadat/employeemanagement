<?php

namespace App\Http\Controllers\fifthyFormats;

use App\Http\Controllers\Controller;
use App\Http\Requests\fifthyFormats\StoreMahajeratRequest;
use App\Http\Requests\fifthyFormats\UpdateMahajeratRequest;
use App\Http\Resources\fifthyFormats\MahajeratResource;
use App\Models\fifthyFormats\Mahajerat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\QueryException;
use App\Services\NotificationService; // 🔔 notifications

class MahajeratController extends Controller
{
    public function index(Request $request)
    {
        $page    = $request->query('page');
        $perPage = $request->query('perPage', $request->query('per_page'));

        $isValidPage    = is_numeric($page) && (int) $page > 0;
        $isValidPerPage = is_numeric($perPage) && (int) $perPage > 0;

        $search = trim((string) $request->query('search', ''));
        $rid    = $request->query('resource_id');
        $fiftySixFormatId = $request->query('fifty_six_format_id');
        $searchField = trim((string) $request->query('search_field', ''));
        $searchValue = trim((string) $request->query('search_value', ''));

        $sortable = ['id', 'name', 'created_at', 'updated_at'];
        $sortBy   = in_array($request->query('sort_by', 'created_at'), $sortable, true)
            ? $request->query('sort_by', 'created_at')
            : 'created_at';

        $sortDir = strtolower((string) $request->query('sort_dir', 'desc'));
        if (!in_array($sortDir, ['asc', 'desc'], true)) {
            $sortDir = 'desc';
        }

        $allowedSearchFields = [
            'name',
            'father_name',
            'scientific_level',
            'main_province',
            'main_district',
            'main_village',
            'now_province',
            'now_zone',
            'now_district',
            'now_vinage',
            'specific_place',
            'country',
            'phone_number',
            'old_job',
            'now_job',
            'tafseel',
            'note',
        ];

        $q = Mahajerat::query()
            ->with(['resource:id,name,code_number,phone_number'])
            ->when($search !== '' && $searchField === '', function ($sub) use ($search) {
                $sub->where(function ($x) use ($search) {
                    $x->where('name', 'like', "%{$search}%")
                        ->orWhere('father_name', 'like', "%{$search}%")
                        ->orWhere('scientific_level', 'like', "%{$search}%")
                        ->orWhere('main_province', 'like', "%{$search}%")
                        ->orWhere('main_district', 'like', "%{$search}%")
                        ->orWhere('main_village', 'like', "%{$search}%")
                        ->orWhere('now_province', 'like', "%{$search}%")
                        ->orWhere('now_zone', 'like', "%{$search}%")
                        ->orWhere('now_district', 'like', "%{$search}%")
                        ->orWhere('now_vinage', 'like', "%{$search}%")
                        ->orWhere('specific_place', 'like', "%{$search}%")
                        ->orWhere('country', 'like', "%{$search}%");
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
            ->when($rid, fn ($sub) => $sub->where('resource_id', $rid))
            ->when(
                is_numeric($fiftySixFormatId) && (int) $fiftySixFormatId > 0,
                function ($sub) use ($fiftySixFormatId) {
                    $sub->where('id', (int) $fiftySixFormatId);
                }
            )
            ->orderBy($sortBy, $sortDir);

        if ($isValidPage && $isValidPerPage) {
            return MahajeratResource::collection(
                $q->paginate((int) $perPage, ['*'], 'page', (int) $page)
            );
        }

        return MahajeratResource::collection($q->get());
    }

    /**
     * GET /mahajerats/filter-options
     */
    public function filterOptions(Request $request)
    {
        $field = trim((string) $request->query('field', ''));
        $q = trim((string) $request->query('q', ''));

        $allowedFields = [
            'name',
            'father_name',
            'scientific_level',
            'main_province',
            'main_district',
            'main_village',
            'now_province',
            'now_zone',
            'now_district',
            'now_vinage',
            'specific_place',
            'country',
            'phone_number',
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

        $query = Mahajerat::query()
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

    public function show(Mahajerat $mahajerat)
    {
        $mahajerat->load(['resource:id,name,code_number,phone_number']);
        return new MahajeratResource($mahajerat);
    }

    /**
     * POST /mahajerats
     * Accepts:
     * - picture as FILE or STRING
     */
    public function store(StoreMahajeratRequest $request)
    {
        $user = $request->user(); // 🔔
        $data = $request->validated();

        try {
            return DB::transaction(function () use ($request, $data, $user) {

                // ✅ picture: file OR string (same style as SaeenFasad)
                if ($request->hasFile('picture')) {
                    $path = $request->file('picture')->store('uploads/mahajerats', 'public');
                    $data['picture'] = "storage/{$path}";
                } else {
                    if (array_key_exists('picture', $data) && !is_string($data['picture'])) {
                        unset($data['picture']);
                    }
                }

                $item = Mahajerat::create($data);

                $item->load(['resource:id,name,code_number,phone_number']);

                // 🔔 Notification: CREATED
                NotificationService::record(
                    $user,
                    module: 'نوي راغلي کورونه',
                    action: 'created',
                    title: 'نوی ریکارډ ثبت شو',
                    message: 'نوم: ' . ($item->name ?? ''),
                    routeName: 'mahajerats', // ⚠️ adjust if your route name differs
                    routeParams: ['id' => $item->id],
                    level: 'success',
                    changes: null
                );

                return (new MahajeratResource($item))
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
     * PUT /mahajerats/{mahajerat}
     * Accepts:
     * - picture as FILE or STRING (replaces old file if new uploaded)
     */
    public function update(UpdateMahajeratRequest $request, Mahajerat $mahajerat)
    {
        $user = $request->user(); // 🔔
        $data = $request->validated();

        try {
            return DB::transaction(function () use ($request, $data, $mahajerat, $user) {

                // 🧮 changes BEFORE update
                $original = $mahajerat->getOriginal();
                $changes  = [];

                foreach ($data as $field => $newValue) {
                    if (array_key_exists($field, $original) && $original[$field] != $newValue) {
                        $changes[$field] = [
                            'old' => $original[$field],
                            'new' => $newValue,
                        ];
                    }
                }

                // ✅ picture: replace file if uploaded
                if ($request->hasFile('picture')) {
                    if ($mahajerat->picture && str_starts_with($mahajerat->picture, 'storage/')) {
                        $old = str_replace('storage/', '', $mahajerat->picture);
                        Storage::disk('public')->delete($old);
                    }

                    $path = $request->file('picture')->store('uploads/mahajerats', 'public');
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

                $mahajerat->update($data);
                $mahajerat->load(['resource:id,name,code_number,phone_number']);

                // 🔔 Notification: UPDATED
                NotificationService::record(
                    $user,
                    module: 'نوي راغلي کورونه',
                    action: 'updated',
                    title: 'ریکارډ اصلاح شو',
                    message: 'نوم: ' . ($mahajerat->name ?? ''),
                    routeName: 'mahajerats',
                    routeParams: ['id' => $mahajerat->id],
                    level: 'info',
                    changes: !empty($changes) ? $changes : null
                );

                return new MahajeratResource($mahajerat);
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

    public function destroy(Request $request, Mahajerat $mahajerat)
    {
        $user = $request->user(); // 🔔

        return DB::transaction(function () use ($mahajerat, $user) {

            $name = $mahajerat->name;

            // ✅ delete picture file (same style as SaeenFasad destroy)
            if ($mahajerat->picture && str_starts_with($mahajerat->picture, 'storage/')) {
                $old = str_replace('storage/', '', $mahajerat->picture);
                Storage::disk('public')->delete($old);
            }

            $mahajerat->delete();

            // 🔔 Notification: DELETED
            NotificationService::record(
                $user,
                module: 'نوي راغلي کورونه',
                action: 'deleted',
                title: 'ریکارډ حذف شو',
                message: 'نوم: ' . ($name ?? ''),
                routeName: null,
                routeParams: [],
                level: 'warning',
                changes: null
            );

            return response()->noContent();
        });
    }
}