<?php

namespace App\Http\Controllers\Api\fifthyformatfour;

use App\Http\Controllers\Controller;
use App\Http\Requests\fifthyformatfour\StoreMasjidRequest;
use App\Http\Requests\fifthyformatfour\UpdateMasjidRequest;
use App\Http\Resources\fifthyformatfour\MasjidResource;
use App\Models\fifthyformatfour\Masjid;
use App\Services\NotificationService; // 🔔 notifications
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class MasjidController extends Controller
{
    private function isDuplicateKey(QueryException $e): bool
    {
        // MySQL duplicate key code = 1062
        return (int) ($e->errorInfo[1] ?? 0) === 1062;
    }

    private function duplicateResponse()
    {
        return response()->json([
            'message' => 'این مساجد او خانقاوې قبلاً ثبت شده است.',
            'errors' => [
                'duplicate' => [
                    'مساجد او خانقاوې با همین مشخصات (نام، نوع، ولایت/زون/ولسوالی/قریه) قبلاً در سیستم موجود است.',
                ],
            ],
        ], 422);
    }

    /**
     * GET /masjids
     */
    public function index(Request $request)
    {
        $query = Masjid::query()
            ->with(['resource:id,name,code_number,phone_number']);

        $search = trim((string) $request->query('search', ''));
        $searchField = trim((string) $request->query('search_field', ''));
        $searchValue = trim((string) $request->query('search_value', ''));

        $allowedSearchFields = [

                'masjid_name','masjid_type',
        'responsible_name','responsible_f_name','responsible_position',
        'responsible_mazhab','responsible_old_job','responsible_now_job','responsible_tarfdary',
        'responsible_main_province','responsible_main_district','responsible_main_village',
        'responsible_now_province','responsible_now_district','responsible_now_village',
        'masjid_people_mazhab','masjid_homes_quantity',
        'masjid_province','masjid_zone','masjid_district','masjid_village',
        'masjid_specific_place','masjid_street_number','masjid_coordinates',
        'Province_addr',
        'picture',
        'resource_id','note',
        ];

        if ($search !== '' && $searchField === '') {
            $query->where(function (Builder $q) use ($search) {
                $q->where('masjid_name', 'like', "%{$search}%")
                    ->orWhere('responsible_name', 'like', "%{$search}%")
                    ->orWhere('responsible_f_name', 'like', "%{$search}%")
                    ->orWhere('masjid_village', 'like', "%{$search}%");
            });
        }

        if ($searchField !== '' && in_array($searchField, $allowedSearchFields, true)) {
            $value = $searchValue !== '' ? $searchValue : $search;

            if ($value !== '') {
                $query->where($searchField, 'like', "%{$value}%");
            }
        }

        $perPage = $request->query('perPage');
        $page    = $request->query('page');

        $perPageInt = filter_var($perPage, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $pageInt    = filter_var($page, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

        if ($perPageInt === false || $pageInt === false) {
            $rows = $query->orderByDesc('id')->get();
            return MasjidResource::collection($rows);
        }

        $rows = $query
            ->orderByDesc('id')
            ->paginate($perPageInt, ['*'], 'page', $pageInt);

        return MasjidResource::collection($rows);
    }

    public function filterOptions(Request $request)
    {
        $field = trim((string) $request->query('field', ''));
        $q = trim((string) $request->query('q', ''));

        $allowedFields = [

                'masjid_name','masjid_type',
        'responsible_name','responsible_f_name','responsible_position',
        'responsible_mazhab','responsible_old_job','responsible_now_job','responsible_tarfdary',
        'responsible_main_province','responsible_main_district','responsible_main_village',
        'responsible_now_province','responsible_now_district','responsible_now_village',
        'masjid_people_mazhab','masjid_homes_quantity',
        'masjid_province','masjid_zone','masjid_district','masjid_village',
        'masjid_specific_place','masjid_street_number','masjid_coordinates',
        'Province_addr',
        'picture',
        'resource_id','note',
        ];

        if ($field === '' || !in_array($field, $allowedFields, true)) {
            return response()->json([
                'data' => [
                    'options' => [],
                ],
            ]);
        }

        $query = Masjid::query()
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
     * POST /masjids
     * Accepts:
     * - picture as FILE or STRING
     */
    public function store(StoreMasjidRequest $request)
    {
        $user = $request->user(); // 🔔 who did this
        $data = $request->validated();

        try {
            return DB::transaction(function () use ($request, $data, $user) {

                // ✅ picture: FILE OR STRING (same style as SaeenFasadController)
                if ($request->hasFile('picture')) {
                    $path = $request->file('picture')->store('uploads/masjids', 'public');
                    $data['picture'] = "storage/{$path}";
                } else {
                    if (array_key_exists('picture', $data) && !is_string($data['picture'])) {
                        unset($data['picture']);
                    }
                }

                $row = Masjid::create($data);

                $row->load(['resource:id,name,code_number,phone_number']);

                // 🔔 Notification: CREATED
                NotificationService::record(
                    $user,
                    module: 'مساجد او خانقاوې',
                    action: 'created',
                    title: 'مساجد او خانقاوې  ثبت شوو',
                    message: 'نوم مساجد او خانقاوې: ' . ($row->masjid_name ?? ''),
                    routeName: 'masjids',              // ✅ set your real route name
                    routeParams: ['id' => $row->id],
                    level: 'success',
                    changes: null
                );

                return (new MasjidResource($row))
                    ->additional(['message' => 'Created'])
                    ->response()
                    ->setStatusCode(201);
            });
        } catch (QueryException $e) {
            if ($this->isDuplicateKey($e)) return $this->duplicateResponse();
            throw $e;
        }
    }

    /**
     * GET /masjids/{masjid}
     */
    public function show(Masjid $masjid)
    {
        $masjid->load(['resource:id,name,code_number,phone_number']);
        return new MasjidResource($masjid);
    }

    /**
     * PUT /masjids/{masjid}
     * Accepts:
     * - picture as FILE or STRING (replaces old file if new uploaded)
     */
    public function update(UpdateMasjidRequest $request, Masjid $masjid)
    {
        $user = $request->user(); // 🔔
        $data = $request->validated();

        try {
            return DB::transaction(function () use ($request, $masjid, $data, $user) {
                // 🧮 build changes BEFORE update (like your current logic)
                $original = $masjid->getOriginal();
                $changes  = [];

                foreach ($data as $field => $newValue) {
                    if (array_key_exists($field, $original)) {
                        $oldValue = $original[$field];
                        if ($oldValue != $newValue) {
                            $changes[$field] = [
                                'old' => $oldValue,
                                'new' => $newValue,
                            ];
                        }
                    }
                }

                // ✅ picture: replace file if uploaded (same style as SaeenFasadController)
                if ($request->hasFile('picture')) {
                    if ($masjid->picture && str_starts_with($masjid->picture, 'storage/')) {
                        $old = str_replace('storage/', '', $masjid->picture);
                        Storage::disk('public')->delete($old);
                    }

                    $path = $request->file('picture')->store('uploads/masjids', 'public');
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

                // 1️⃣ Update Masjid fields (including picture + resource_id)
                $masjid->update($data);

                // 2️⃣ (Optional) update linked Resource code/phone if frontend sends them (keep your existing logic)
                $resourceCode  = $request->input('resource_code');   // extra field
                $resourcePhone = $request->input('resource_phone');  // extra field

                if (($resourceCode !== null || $resourcePhone !== null) && $masjid->resource) {
                    $updates = [];

                    if ($resourceCode !== null) {
                        $updates['code_number'] = $resourceCode;
                    }
                    if ($resourcePhone !== null) {
                        $updates['phone_number'] = $resourcePhone;
                    }

                    if (!empty($updates)) {
                        $masjid->resource->update($updates);
                    }
                }

                // 3️⃣ Reload
                $masjid->load(['resource:id,name,code_number,phone_number']);

                // 🔔 Notification: UPDATED + changes
                NotificationService::record(
                    $user,
                    module: 'مساجد او خانقاوې',
                    action: 'updated',
                    title: 'مساجد او خانقاوې اصلاح شوو',
                    message: 'نوم مساجد او خانقاوې: ' . ($masjid->masjid_name ?? ''),
                    routeName: 'masjids',              // ✅ set your real route name
                    routeParams: ['id' => $masjid->id],
                    level: 'info',
                    changes: !empty($changes) ? $changes : null
                );

                return new MasjidResource($masjid);
            });
        } catch (QueryException $e) {
            if ($this->isDuplicateKey($e)) return $this->duplicateResponse();
            throw $e;
        }
    }

    /**
     * DELETE /masjids/{masjid}
     */
    public function destroy(Request $request, Masjid $masjid)
    {
        $user = $request->user(); // 🔔

        return DB::transaction(function () use ($masjid, $user) {
            $name = $masjid->masjid_name;

            // ✅ delete picture file (same style as SaeenFasadController)
            if ($masjid->picture && str_starts_with($masjid->picture, 'storage/')) {
                $old = str_replace('storage/', '', $masjid->picture);
                Storage::disk('public')->delete($old);
            }

            $masjid->delete();

            // 🔔 Notification: DELETED
            NotificationService::record(
                $user,
                module: 'مساجد او خانقاوې',
                action: 'deleted',
                title: 'مساجد او خانقاوې حذف شوو',
                message: 'نوم مساجد او خانقاوې: ' . ($name ?? ''),
                routeName: null,
                routeParams: ['masjids'],
                level: 'warning',
                changes: null
            );

            return response()->json(['message' => 'Deleted']);
        });
    }
}