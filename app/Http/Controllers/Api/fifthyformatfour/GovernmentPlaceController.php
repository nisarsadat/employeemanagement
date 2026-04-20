<?php

namespace App\Http\Controllers\Api\fifthyformatfour;

use App\Http\Controllers\Controller;
use App\Http\Requests\fifthyformatfour\StoreGovernmentPlaceRequest;
use App\Http\Requests\fifthyformatfour\UpdateGovernmentPlaceRequest;
use App\Http\Resources\fifthyformatfour\GovernmentPlaceResource;
use App\Models\fifthyformatfour\GovernmentPlace;
use App\Services\NotificationService; // 🔔 notifications
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class GovernmentPlaceController extends Controller
{
    private function isDuplicateKey(QueryException $e): bool
    {
        // MySQL duplicate key
        return (int) ($e->errorInfo[1] ?? 0) === 1062;
    }

    private function duplicateResponse()
    {
        return response()->json([
            'message' => 'این دولتي ځایونه قبلاً ثبت شده است.',
            'errors' => [
                'duplicate' => [
                    'دولتي ځایونه با همین مشخصات (نام، مسئول، آدرس و سایر مشخصات) قبلاً در سیستم موجود است.',
                ],
            ],
        ], 422);
    }

    /**
     * GET /government-places
     */
    public function index(Request $request)
    {
        $page    = $request->query('page');
        $perPage = $request->query('perPage', $request->query('per_page', $request->query('itemsPerPage')));

        $isValidPage    = is_numeric($page) && (int) $page > 0;
        $isValidPerPage = is_numeric($perPage) && (int) $perPage > 0;

        $search = trim((string) $request->query('search', ''));
        $searchField = trim((string) $request->query('search_field', ''));
        $searchValue = trim((string) $request->query('search_value', ''));

        $allowedSearchFields = [

                'government_place_name','daily_clientele',
        'responsible_name','responsible_f_name','responsible_possition','responsible_phone',
        'province','zone','district','village','specefic_place','coordinates',
        'tafseel','amnyatee_khala','guard_quantity',
        'picture',
        'Province_addr',
        'employee_quantity',
        'resource_id','note',
        ];

        $query = GovernmentPlace::query()
            ->with(['resource:id,name,code_number,phone_number'])
            ->when($search !== '' && $searchField === '', function ($sub) use ($search) {
                $sub->where(function (Builder $w) use ($search) {
                    $w->where('government_place_name', 'like', "%{$search}%")
                        ->orWhere('province', 'like', "%{$search}%")
                        ->orWhere('district', 'like', "%{$search}%")
                        ->orWhere('village', 'like', "%{$search}%");
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
            ->orderByDesc('id');

        if ($isValidPage && $isValidPerPage) {
            $rows = $query->paginate((int) $perPage, ['*'], 'page', (int) $page);
            return GovernmentPlaceResource::collection($rows);
        }

        return GovernmentPlaceResource::collection($query->get());
    }

    /**
     * GET /government-places/filter-options
     */
    public function filterOptions(Request $request)
    {
        $field = trim((string) $request->query('field', ''));
        $q = trim((string) $request->query('q', ''));

        $allowedFields = [

                'government_place_name','daily_clientele',
        'responsible_name','responsible_f_name','responsible_possition','responsible_phone',
        'province','zone','district','village','specefic_place','coordinates',
        'tafseel','amnyatee_khala','guard_quantity',
        'picture',
        'Province_addr',
        'employee_quantity',
        'resource_id','note',
        ];

        if ($field === '' || !in_array($field, $allowedFields, true)) {
            return response()->json([
                'data' => [
                    'options' => [],
                ],
            ]);
        }

        $query = GovernmentPlace::query()
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
     * POST /government-places
     * Accepts:
     * - picture as FILE or STRING
     */
    public function store(StoreGovernmentPlaceRequest $request)
    {
        $user = $request->user(); // 🔔
        $data = $request->validated();

        try {
            return DB::transaction(function () use ($request, $data, $user) {

                // ✅ picture: FILE OR STRING (same style as SaeenFasadController)
                if ($request->hasFile('picture')) {
                    $path = $request->file('picture')->store('uploads/government-places', 'public');
                    $data['picture'] = "storage/{$path}";
                } else {
                    if (array_key_exists('picture', $data) && !is_string($data['picture'])) {
                        unset($data['picture']);
                    }
                }

                $row = GovernmentPlace::create($data);
                $row->load(['resource:id,name,code_number,phone_number']);

                // 🔔 Notification: CREATED
                NotificationService::record(
                    $user,
                    module: 'دولتي ځایونه',
                    action: 'created',
                    title: 'دولتي ځایونه ثبت شوو',
                    message: 'نوم: ' . ($row->government_place_name ?? ''),
                    routeName: 'government-places',
                    routeParams: ['id' => $row->id],
                    level: 'success',
                    changes: null
                );

                return (new GovernmentPlaceResource($row))
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
     * GET /government-places/{governmentplace}
     */
    public function show(GovernmentPlace $governmentplace)
    {
        $governmentplace->load(['resource:id,name,code_number,phone_number']);
        return GovernmentPlaceResource::make($governmentplace);
    }

    /**
     * PUT /government-places/{governmentplace}
     * Accepts:
     * - picture as FILE or STRING (replaces old file if new uploaded)
     */
    public function update(UpdateGovernmentPlaceRequest $request, GovernmentPlace $governmentplace)
    {
        $user = $request->user(); // 🔔
        $data = $request->validated();

        try {
            return DB::transaction(function () use ($request, $data, $governmentplace, $user) {
                // 🧮 changes BEFORE update
                $original = $governmentplace->getOriginal();
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

                // ✅ picture: replace file if uploaded
                if ($request->hasFile('picture')) {
                    if ($governmentplace->picture && str_starts_with($governmentplace->picture, 'storage/')) {
                        $old = str_replace('storage/', '', $governmentplace->picture);
                        Storage::disk('public')->delete($old);
                    }

                    $path = $request->file('picture')->store('uploads/government-places', 'public');
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

                $governmentplace->update($data);
                $governmentplace->load(['resource:id,name,code_number,phone_number']);

                // 🔔 Notification: UPDATED
                NotificationService::record(
                    $user,
                    module: 'دولتي ځایونه',
                    action: 'updated',
                    title: 'دولتي ځایونه اصلاح شوو',
                    message: 'نوم: ' . ($governmentplace->government_place_name ?? ''),
                    routeName: 'government-places',
                    routeParams: ['id' => $governmentplace->id],
                    level: 'info',
                    changes: !empty($changes) ? $changes : null
                );

                return GovernmentPlaceResource::make($governmentplace);
            });
        } catch (QueryException $e) {
            if ($this->isDuplicateKey($e)) return $this->duplicateResponse();
            throw $e;
        }
    }

    /**
     * DELETE /government-places/{governmentplace}
     */
    public function destroy(Request $request, GovernmentPlace $governmentplace)
    {
        $user = $request->user(); // 🔔

        return DB::transaction(function () use ($governmentplace, $user) {
            $name = $governmentplace->government_place_name;

            // ✅ delete picture file
            if ($governmentplace->picture && str_starts_with($governmentplace->picture, 'storage/')) {
                $old = str_replace('storage/', '', $governmentplace->picture);
                Storage::disk('public')->delete($old);
            }

            $governmentplace->delete();

            // 🔔 Notification: DELETED
            NotificationService::record(
                $user,
                module: 'دولتي ځایونه',
                action: 'deleted',
                title: 'دولتي ځایونه حذف شوو',
                message: 'نوم: ' . ($name ?? ''),
                routeName: null,
                routeParams: ['government-places'],
                level: 'warning',
                changes: null
            );

            return response()->noContent();
        });
    }

    /**
     * Optional bulk delete (if you need it)
     * POST /government-places/bulk-delete  with { "ids": [1,2,3] }
     */
    public function bulkDelete(Request $request)
    {
        $ids = (array) $request->input('ids', []);

        // ✅ delete pictures for selected rows (safe cleanup)
        $rows = GovernmentPlace::query()->whereIn('id', $ids)->get();
        foreach ($rows as $row) {
            if ($row->picture && str_starts_with($row->picture, 'storage/')) {
                $old = str_replace('storage/', '', $row->picture);
                Storage::disk('public')->delete($old);
            }
        }

        GovernmentPlace::destroy($ids);

        return response()->noContent();
    }
}