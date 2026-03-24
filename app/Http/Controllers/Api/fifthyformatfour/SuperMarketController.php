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
            'message' => 'این تجارتي مرکزونه او سوپرمارکیټونه قبلاً ثبت شده است.',
            'errors' => [
                'duplicate' => [
                    'تجارتي مرکزونه او سوپرمارکیټونه با همین مشخصات (نام، نوع فعالیت، تعداد مراجعین/دکان‌ها و ولایت/زون/ولسوالی/قریه) قبلاً در سیستم موجود است.',
                ],
            ],
        ], 422);
    }

    public function index(Request $request)
    {
        $query = SuperMarket::query()->with(['resource:id,name,code_number,phone_number']);

        if ($s = trim((string) $request->query('search', ''))) {
            $query->where(function (Builder $q) use ($s) {
                $q->where('supermarket_name', 'like', "%$s%")
                    ->orWhere('province', 'like', "%$s%")
                    ->orWhere('village', 'like', "%$s%");
            });
        }

        $perPage = $request->query('perPage');
        $page    = $request->query('page');
        $perPageInt = filter_var($perPage, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $pageInt    = filter_var($page, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

        if ($perPageInt === false || $pageInt === false) {
            return SuperMarketResource::collection($query->orderByDesc('id')->get());
        }

        $rows = $query->orderByDesc('id')->paginate($perPageInt, ['*'], 'page', $pageInt);
        return SuperMarketResource::collection($rows);
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
                    routeName: 'super-markets',         // ✅ set your real route name
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
                    routeName: 'super-markets',         // ✅ set your real route name
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
