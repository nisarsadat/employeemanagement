<?php

namespace App\Http\Controllers\Api\fifthyformatfour;

use App\Http\Controllers\Controller;
use App\Http\Requests\fifthyformatfour\StoreDeplomaticPlaceRequest;
use App\Http\Requests\fifthyformatfour\UpdateDeplomaticPlaceRequest;
use App\Http\Resources\fifthyformatfour\DeplomaticPlaceResource;
use App\Models\fifthyformatfour\DeplomaticPlace;
use App\Services\NotificationService; // 🔔 notifications
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DeplomaticPlaceController extends Controller
{
    private function isDuplicateKey(QueryException $e): bool
    {
        // MySQL duplicate key
        return (int) ($e->errorInfo[1] ?? 0) === 1062;
    }

    private function duplicateResponse()
    {
        return response()->json([
            'message' => 'دا ډیپلوماټیک ځای مخکې نه ثبت شوی دی.',
            'errors'  => [
                'duplicate' => [
                    'د همدې ځای او معلوماتو (نوم/فعالیت ډول/د فعالیت ځای + ولایت/زون/ولسوالۍ/کلی/ځانګړی ځای/کوڅه/کور) سره ریکارډ مخکې موجود دی.',
                ],
            ],
        ], 422);
    }

    public function index(Request $request)
    {
        $page    = $request->query('page');
        $perPage = $request->query('perPage', $request->query('per_page'));

        $isValidPage    = is_numeric($page) && (int) $page > 0;
        $isValidPerPage = is_numeric($perPage) && (int) $perPage > 0;

        $search = trim((string) $request->query('search', ''));
        $searchField = trim((string) $request->query('search_field', ''));
        $searchValue = trim((string) $request->query('search_value', ''));

        $allowedSearchFields = [
        'deplomatic_place_name','activity_type','activity_place',
        'enter_male_quantity','outer_male_quantity','enter_female_quantity','outer_female_quantity',
        'responsible_name','responsible_f_name','responsible_possition','responsible_country','responsible_phone',
        'province','zone','district','village','specefic_place','street_number','home_number',
        'supplier_country',
        'picture',
        'Province_addr',
        'guard_quantity','coordinates','resource_id','note',
        ];

        $query = DeplomaticPlace::query()
            ->with(['resource:id,name,code_number,phone_number'])
            ->when($search !== '' && $searchField === '', function (Builder $sub) use ($search) {
                $sub->where(function (Builder $q) use ($search) {
                    $q->where('deplomatic_place_name', 'like', "%{$search}%")
                        ->orWhere('province', 'like', "%{$search}%")
                        ->orWhere('zone', 'like', "%{$search}%")
                        ->orWhere('district', 'like', "%{$search}%")
                        ->orWhere('village', 'like', "%{$search}%")
                        ->orWhere('specefic_place', 'like', "%{$search}%");
                });
            })
            ->when(
                $searchField !== '' && in_array($searchField, $allowedSearchFields, true),
                function (Builder $sub) use ($searchField, $searchValue, $search) {
                    $value = $searchValue !== '' ? $searchValue : $search;

                    if ($value !== '') {
                        $sub->where($searchField, 'like', "%{$value}%");
                    }
                }
            )
            ->orderByDesc('id');

        if ($isValidPage && $isValidPerPage) {
            $rows = $query->paginate((int) $perPage, ['*'], 'page', (int) $page);
            return DeplomaticPlaceResource::collection($rows);
        }

        return DeplomaticPlaceResource::collection($query->get());
    }

    /**
     * GET /deplomaticplaces/filter-options
     */
    public function filterOptions(Request $request)
    {
        $field = trim((string) $request->query('field', ''));
        $q = trim((string) $request->query('q', ''));

        $allowedFields = [
        'deplomatic_place_name','activity_type','activity_place',
        'enter_male_quantity','outer_male_quantity','enter_female_quantity','outer_female_quantity',
        'responsible_name','responsible_f_name','responsible_possition','responsible_country','responsible_phone',
        'province','zone','district','village','specefic_place','street_number','home_number',
        'supplier_country',
        'picture',
        'Province_addr',
        'guard_quantity','coordinates','resource_id','note',
        ];

        if ($field === '' || !in_array($field, $allowedFields, true)) {
            return response()->json([
                'data' => [
                    'options' => [],
                ],
            ]);
        }

        $query = DeplomaticPlace::query()
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

    public function store(StoreDeplomaticPlaceRequest $request)
    {
        $user = $request->user(); // 🔔 who did this
        $data = $request->validated();

        try {
            return DB::transaction(function () use ($request, $user, $data) {

                // ✅ picture: file OR string
                if ($request->hasFile('picture')) {
                    $path = $request->file('picture')->store('uploads/deplomaticplaces', 'public');
                    $data['picture'] = "storage/{$path}";
                } else {
                    if (array_key_exists('picture', $data) && !is_string($data['picture'])) {
                        unset($data['picture']);
                    }
                }

                $row = DeplomaticPlace::create($data);
                $row->load(['resource:id,name,code_number,phone_number']);

                // 🔔 Notification: CREATED
                NotificationService::record(
                    $user,
                    module: 'ډیپلوماټیک ځای',
                    action: 'created',
                    title: 'نوی ډیپلوماټیک ځای ثبت شو',
                    message: 'نوم: ' . ($row->deplomatic_place_name ?? ''),
                    routeName: 'deplomaticplaces',          // ⚠️ set to your real route name
                    routeParams: ['id' => $row->id],
                    level: 'success',
                    changes: null
                );

                return new DeplomaticPlaceResource($row);
            });
        } catch (QueryException $e) {
            if ($this->isDuplicateKey($e)) {
                return $this->duplicateResponse();
            }
            throw $e;
        }
    }

    public function show(DeplomaticPlace $deplomaticplace)
    {
        $deplomaticplace->load(['resource:id,name,code_number,phone_number']);
        return new DeplomaticPlaceResource($deplomaticplace);
    }

    public function update(UpdateDeplomaticPlaceRequest $request, DeplomaticPlace $deplomaticplace)
    {
        $user = $request->user(); // 🔔

        try {
            return DB::transaction(function () use ($request, $deplomaticplace, $user) {

                $original  = $deplomaticplace->getOriginal();
                $validated = $request->validated();
                $changes   = [];

                foreach ($validated as $field => $newValue) {
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

                    // delete old
                    if ($deplomaticplace->picture && str_starts_with($deplomaticplace->picture, 'storage/')) {
                        $old = str_replace('storage/', '', $deplomaticplace->picture);
                        Storage::disk('public')->delete($old);
                    }

                    // store new
                    $path = $request->file('picture')->store('uploads/deplomaticplaces', 'public');
                    $validated['picture'] = "storage/{$path}";

                    $changes['picture'] = [
                        'old' => $original['picture'] ?? null,
                        'new' => $validated['picture'],
                    ];
                } else {
                    if (array_key_exists('picture', $validated) && !is_string($validated['picture'])) {
                        unset($validated['picture']);
                    }
                }

                $deplomaticplace->update($validated);
                $deplomaticplace->load(['resource:id,name,code_number,phone_number']);

                // 🔔 Notification: UPDATED
                NotificationService::record(
                    $user,
                    module: 'ډیپلوماټیک ځای',
                    action: 'updated',
                    title: 'ډیپلوماټیک ځای اصلاح شو',
                    message: 'نوم: ' . ($deplomaticplace->deplomatic_place_name ?? ''),
                    routeName: 'deplomaticplaces',          // ⚠️ set to your real route name
                    routeParams: ['id' => $deplomaticplace->id],
                    level: 'info',
                    changes: !empty($changes) ? $changes : null
                );

                return new DeplomaticPlaceResource($deplomaticplace);
            });
        } catch (QueryException $e) {
            if ($this->isDuplicateKey($e)) {
                return $this->duplicateResponse();
            }
            throw $e;
        }
    }

    public function destroy(Request $request, DeplomaticPlace $deplomaticplace)
    {
        $user = $request->user(); // 🔔

        return DB::transaction(function () use ($deplomaticplace, $user) {
            $name = $deplomaticplace->deplomatic_place_name;

            // ✅ delete picture file
            if ($deplomaticplace->picture && str_starts_with($deplomaticplace->picture, 'storage/')) {
                $old = str_replace('storage/', '', $deplomaticplace->picture);
                Storage::disk('public')->delete($old);
            }

            $deplomaticplace->delete();

            // 🔔 Notification: DELETED
            NotificationService::record(
                $user,
                module: 'ډیپلوماټیک ځای',
                action: 'deleted',
                title: 'ډیپلوماټیک ځای حذف شو',
                message: 'نوم: ' . ($name ?? ''),
                routeName: null,
                routeParams: ['deplomaticplaces'],
                level: 'warning',
                changes: null
            );

            return response()->json(['message' => 'Deleted']);
        });
    }
}