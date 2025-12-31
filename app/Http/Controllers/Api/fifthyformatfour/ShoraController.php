<?php

namespace App\Http\Controllers\Api\fifthyformatfour;

use App\Http\Controllers\Controller;
use App\Http\Requests\fifthyformatfour\StoreShoraRequest;
use App\Http\Requests\fifthyformatfour\UpdateShoraRequest;
use App\Http\Resources\fifthyformatfour\ShoraResource;
use App\Models\fifthyformatfour\Shora;
use App\Services\NotificationService; // 🔔 notifications
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ShoraController extends Controller
{
    private function isDuplicateKey(QueryException $e): bool
    {
        return (int) ($e->errorInfo[1] ?? 0) === 1062; // MySQL duplicate key
    }

    private function duplicateResponse()
    {
        return response()->json([
            'message' => 'این شورا قبلاً ثبت شده است.',
            'errors' => [
                'duplicate' => [
                    'شورا با همین مشخصات (قریه، نام، هدف، تعداد افراد/جلسات، ثبت و محل جلسه) قبلاً در سیستم موجود است.',
                ],
            ],
        ], 422);
    }

    public function index(Request $request)
    {
        $query = Shora::query()
            ->with(['resource:id,name,code_number,phone_number']);

        if ($s = trim((string) $request->query('search', ''))) {
            $query->where(function (Builder $q) use ($s) {
                $q->where('shora_name','like',"%$s%")
                  ->orWhere('responsible_name','like',"%$s%")
                  ->orWhere('shora_province','like',"%$s%")
                  ->orWhere('shora_village','like',"%$s%");
            });
        }

        $perPage = $request->query('perPage');
        $page    = $request->query('page');

        $perPageInt = filter_var($perPage, FILTER_VALIDATE_INT, ['options'=>['min_range'=>1]]);
        $pageInt    = filter_var($page,    FILTER_VALIDATE_INT, ['options'=>['min_range'=>1]]);

        // Fallback → return ALL when missing/invalid
        if ($perPageInt === false || $pageInt === false) {
            return ShoraResource::collection($query->orderByDesc('id')->get());
        }

        $rows = $query->orderByDesc('id')->paginate($perPageInt, ['*'], 'page', $pageInt);
        return ShoraResource::collection($rows);
    }

    public function store(StoreShoraRequest $request)
    {
        $user = $request->user(); // 🔔

        try {
            return DB::transaction(function () use ($request, $user) {
                $row = Shora::create($request->validated());
                $row->load(['resource:id,name,code_number,phone_number']);

                // 🔔 Notification: CREATED
                NotificationService::record(
                    $user,
                    module: 'شورا',
                    action: 'created',
                    title: 'شورا جدید ثبت شد',
                    message: 'نام شورا: ' . ($row->shora_name ?? ''),
                    routeName: 'shoras',              // ✅ set your real route name
                    routeParams: ['id' => $row->id],
                    level: 'success',
                    changes: null
                );

                return new ShoraResource($row);
            });
        } catch (QueryException $e) {
            if ($this->isDuplicateKey($e)) return $this->duplicateResponse();
            throw $e;
        }
    }

    public function show(Shora $shora)
    {
        $shora->load('resource');
        return ShoraResource::make($shora);
    }

    public function update(UpdateShoraRequest $request, Shora $shora)
    {
        $user = $request->user(); // 🔔

        try {
            return DB::transaction(function () use ($request, $shora, $user) {
                // 🧮 changes BEFORE update
                $original  = $shora->getOriginal();
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

                $shora->update($validated);
                $shora->load(['resource:id,name,code_number,phone_number']);

                // 🔔 Notification: UPDATED
                NotificationService::record(
                    $user,
                    module: 'شورا',
                    action: 'updated',
                    title: 'شورا اصلاح شد',
                    message: 'نام شورا: ' . ($shora->shora_name ?? ''),
                    routeName: 'shoras',              // ✅ set your real route name
                    routeParams: ['id' => $shora->id],
                    level: 'info',
                    changes: !empty($changes) ? $changes : null
                );

                return new ShoraResource($shora);
            });
        } catch (QueryException $e) {
            if ($this->isDuplicateKey($e)) return $this->duplicateResponse();
            throw $e;
        }
    }

    public function destroy(Request $request, Shora $shora)
    {
        $user = $request->user(); // 🔔

        return DB::transaction(function () use ($shora, $user) {
            $name = $shora->shora_name;

            $shora->delete();

            // 🔔 Notification: DELETED
            NotificationService::record(
                $user,
                module: 'شورا',
                action: 'deleted',
                title: 'شورا حذف شد',
                message: 'نام شورا: ' . ($name ?? ''),
                routeName: null,
                routeParams: ['shoras'],
                level: 'warning',
                changes: null
            );

            return response()->json(['message' => 'Deleted']);
        });
    }
}
