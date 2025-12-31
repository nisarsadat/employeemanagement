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

class SuperMarketController extends Controller
{
    private function isDuplicateKey(QueryException $e): bool
    {
        return (int) ($e->errorInfo[1] ?? 0) === 1062; // MySQL duplicate key
    }

    private function duplicateResponse()
    {
        return response()->json([
            'message' => 'این سوپرمارکت قبلاً ثبت شده است.',
            'errors' => [
                'duplicate' => [
                    'سوپرمارکت با همین مشخصات (نام، نوع فعالیت، تعداد مراجعین/دکان‌ها و ولایت/زون/ولسوالی/قریه) قبلاً در سیستم موجود است.',
                ],
            ],
        ], 422);
    }

    public function index(Request $request)
    {
        $query = SuperMarket::query()->with(['resource:id,name,code_number,phone_number']);

        if ($s = trim((string) $request->query('search', ''))) {
            $query->where(function (Builder $q) use ($s) {
                $q->where('supermarket_name','like',"%$s%")
                  ->orWhere('province','like',"%$s%")
                  ->orWhere('village','like',"%$s%");
            });
        }

        $perPage = $request->query('perPage');
        $page    = $request->query('page');
        $perPageInt = filter_var($perPage, FILTER_VALIDATE_INT, ['options'=>['min_range'=>1]]);
        $pageInt    = filter_var($page,    FILTER_VALIDATE_INT, ['options'=>['min_range'=>1]]);

        if ($perPageInt === false || $pageInt === false) {
            return SuperMarketResource::collection($query->orderByDesc('id')->get());
        }

        $rows = $query->orderByDesc('id')->paginate($perPageInt, ['*'], 'page', $pageInt);
        return SuperMarketResource::collection($rows);
    }

    public function store(StoreSuperMarketRequest $request)
    {
        $user = $request->user(); // 🔔

        try {
            return DB::transaction(function () use ($request, $user) {
                $row = SuperMarket::create($request->validated());
                $row->load(['resource:id,name,code_number,phone_number']);

                // 🔔 Notification: CREATED
                NotificationService::record(
                    $user,
                    module: 'سوپرمارکت',
                    action: 'created',
                    title: 'سوپرمارکت جدید ثبت شد',
                    message: 'نام سوپرمارکت: ' . ($row->supermarket_name ?? ''),
                    routeName: 'super-markets',         // ✅ set your real route name
                    routeParams: ['id' => $row->id],
                    level: 'success',
                    changes: null
                );

                return new SuperMarketResource($row);
            });
        } catch (QueryException $e) {
            if ($this->isDuplicateKey($e)) return $this->duplicateResponse();
            throw $e;
        }
    }

    public function show(SuperMarket $supermarket)
    {
        $supermarket->load('resource');
        return new SuperMarketResource($supermarket);
    }

    public function update(UpdateSuperMarketRequest $request, SuperMarket $supermarket)
    {
        $user = $request->user(); // 🔔

        try {
            return DB::transaction(function () use ($request, $supermarket, $user) {
                // 🧮 changes BEFORE update
                $original  = $supermarket->getOriginal();
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

                $supermarket->update($validated);
                $supermarket->load(['resource:id,name,code_number,phone_number']);

                // 🔔 Notification: UPDATED
                NotificationService::record(
                    $user,
                    module: 'سوپرمارکت',
                    action: 'updated',
                    title: 'سوپرمارکت اصلاح شد',
                    message: 'نام سوپرمارکت: ' . ($supermarket->supermarket_name ?? ''),
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

            $supermarket->delete();

            // 🔔 Notification: DELETED
            NotificationService::record(
                $user,
                module: 'سوپرمارکت',
                action: 'deleted',
                title: 'سوپرمارکت حذف شد',
                message: 'نام سوپرمارکت: ' . ($name ?? ''),
                routeName: null,
                routeParams: ['super-markets'],
                level: 'warning',
                changes: null
            );

            return response()->json(['message' => 'Deleted']);
        });
    }
}
