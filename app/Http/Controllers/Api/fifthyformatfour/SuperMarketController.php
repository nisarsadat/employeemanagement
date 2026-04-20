<?php

namespace App\Http\Controllers\Api\fifthyformatfour;

use App\Http\Controllers\Controller;
use App\Http\Requests\fifthyformatfour\StoreSuperMarketRequest;
use App\Http\Requests\fifthyformatfour\UpdateSuperMarketRequest;
use App\Http\Resources\fifthyformatfour\SuperMarketResource;
use App\Models\fifthyformatfour\SuperMarket;
use App\Services\NotificationService; // 🔔 notifications
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class SuperMarketController extends Controller
{
    private function isDuplicateKey(QueryException $e): bool
    {
        return (int) ($e->errorInfo[1] ?? 0) === 1062; // MySQL duplicate key
    }

    private function duplicateResponse()
    {
        return response()->json([
            'message' => 'دا تجارتي مرکزونه او سوپرمارکیټونه مخکې ثبت شوي دي.',
            'errors' => [
                'duplicate' => [
                    'تجارتي مرکزونه او سوپرمارکیټونه په همدې مشخصاتو (نوم، د فعالیت ډول، د مراجعینو/دوکانونو شمېر او ولایت/زون/ولسوالي/قریه) سره مخکې په سیستم کې موجود دي.',
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

                'supermarket_name','market_activity_type','daily_clientele','shops_quantity',
        'responsible_name','responsible_f_name',
        'responsible_phone',
        'picture',
        'responsible_job',
        'province','zone','district','village','specefic_place','coordinates',
        'Province_addr',
        'employee_quantity','guard_quantity','resource_id','note',
        ];

        $query = SuperMarket::query()
            ->with(['resource:id,name,code_number,phone_number'])
            ->when($search !== '' && $searchField === '', function (Builder $sub) use ($search) {
                $sub->where(function (Builder $q) use ($search) {
                    $q->where('supermarket_name', 'like', "%{$search}%")
                        ->orWhere('province', 'like', "%{$search}%")
                        ->orWhere('village', 'like', "%{$search}%");
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
            return SuperMarketResource::collection($rows);
        }

        return SuperMarketResource::collection($query->get());
    }

    /**
     * GET /super-markets/filter-options
     */
    public function filterOptions(Request $request)
    {
        $field = trim((string) $request->query('field', ''));
        $q = trim((string) $request->query('q', ''));

        $allowedFields = [

                'supermarket_name','market_activity_type','daily_clientele','shops_quantity',
        'responsible_name','responsible_f_name',
        'responsible_phone',
        'picture',
        'responsible_job',
        'province','zone','district','village','specefic_place','coordinates',
        'Province_addr',
        'employee_quantity','guard_quantity','resource_id','note',
        ];

        if ($field === '' || !in_array($field, $allowedFields, true)) {
            return response()->json([
                'data' => [
                    'options' => [],
                ],
            ]);
        }

        $query = SuperMarket::query()
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
     * POST /super-markets
     * Accepts:
     * - picture as FILE or STRING
     */
    public function store(StoreSuperMarketRequest $request)
    {
        $user = $request->user(); // 🔔
        $data = $request->validated();

        try {
            return DB::transaction(function () use ($request, $data, $user) {

                // ✅ picture: FILE OR STRING (same style as SaeenFasadController)
                if ($request->hasFile('picture')) {
                    $path = $request->file('picture')->store('uploads/super-markets', 'public');
                    $data['picture'] = "storage/{$path}";
                } else {
                    if (array_key_exists('picture', $data) && !is_string($data['picture'])) {
                        unset($data['picture']);
                    }
                }

                $row = SuperMarket::create($data);
                $row->load(['resource:id,name,code_number,phone_number']);

                // 🔔 Notification: CREATED
                NotificationService::record(
                    $user,
                    module: 'تجارتي مرکزونه او سوپرمارکیټونه',
                    action: 'created',
                    title: 'تجارتي مرکزونه او سوپرمارکیټونه  ثبت شوو',
                    message: 'نوم: ' . ($row->supermarket_name ?? ''),
                    routeName: 'super-markets',
                    routeParams: ['id' => $row->id],
                    level: 'success',
                    changes: null
                );

                return (new SuperMarketResource($row))
                    ->additional(['message' => 'Created'])
                    ->response()
                    ->setStatusCode(201);
            });
        } catch (QueryException $e) {
            if ($this->isDuplicateKey($e)) return $this->duplicateResponse();
            throw $e;
        }
    }

    public function show(SuperMarket $supermarket)
    {
        $supermarket->load(['resource:id,name,code_number,phone_number']);
        return new SuperMarketResource($supermarket);
    }

    /**
     * PUT /super-markets/{supermarket}
     * Accepts:
     * - picture as FILE or STRING (replaces old file if new uploaded)
     */
    public function update(UpdateSuperMarketRequest $request, SuperMarket $supermarket)
    {
        $user = $request->user(); // 🔔
        $data = $request->validated();

        try {
            return DB::transaction(function () use ($request, $supermarket, $data, $user) {
                // 🧮 changes BEFORE update
                $original = $supermarket->getOriginal();
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
                    if ($supermarket->picture && str_starts_with($supermarket->picture, 'storage/')) {
                        $old = str_replace('storage/', '', $supermarket->picture);
                        Storage::disk('public')->delete($old);
                    }

                    $path = $request->file('picture')->store('uploads/super-markets', 'public');
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

                $supermarket->update($data);
                $supermarket->load(['resource:id,name,code_number,phone_number']);

                // 🔔 Notification: UPDATED
                NotificationService::record(
                    $user,
                    module: 'تجارتي مرکزونه او سوپرمارکیټونه',
                    action: 'updated',
                    title: 'تجارتي مرکزونه او سوپرمارکیټونه اصلاح شوو',
                    message: 'نوم: ' . ($supermarket->supermarket_name ?? ''),
                    routeName: 'super-markets',
                    routeParams: ['id' => $supermarket->id],
                    level: 'info',
                    changes: !empty($changes) ? $changes : null
                );

                return new SuperMarketResource($supermarket);
            });
        } catch (QueryException $e) {
            if ($this->isDuplicateKey($e)) return $this->duplicateResponse();
            throw $e;
        }
    }

    public function destroy(Request $request, SuperMarket $supermarket)
    {
        $user = $request->user(); // 🔔

        return DB::transaction(function () use ($supermarket, $user) {
            $name = $supermarket->supermarket_name;

            // ✅ delete picture file
            if ($supermarket->picture && str_starts_with($supermarket->picture, 'storage/')) {
                $old = str_replace('storage/', '', $supermarket->picture);
                Storage::disk('public')->delete($old);
            }

            $supermarket->delete();

            // 🔔 Notification: DELETED
            NotificationService::record(
                $user,
                module: 'تجارتي مرکزونه او سوپرمارکیټونه',
                action: 'deleted',
                title: 'تجارتي مرکزونه او سوپرمارکیټونه حذف شوو',
                message: 'نوم: ' . ($name ?? ''),
                routeName: null,
                routeParams: ['super-markets'],
                level: 'warning',
                changes: null
            );

            return response()->json(['message' => 'Deleted']);
        });
    }
}