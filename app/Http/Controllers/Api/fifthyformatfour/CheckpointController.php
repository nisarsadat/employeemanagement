<?php

namespace App\Http\Controllers\Api\fifthyformatfour;

use App\Http\Controllers\Controller;
use App\Http\Requests\fifthyformatfour\StoreCheckpointRequest;
use App\Http\Requests\fifthyformatfour\UpdateCheckpointRequest;
use App\Http\Resources\fifthyformatfour\CheckpointResource;
use App\Models\fifthyformatfour\Checkpoint;
use App\Services\NotificationService; // 🔔 notifications
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CheckpointController extends Controller
{
    private function isDuplicateKey(QueryException $e): bool
    {
        // MySQL duplicate key code = 1062
        return (int) ($e->errorInfo[1] ?? 0) === 1062;
    }

    private function duplicateResponse()
    {
        return response()->json([
            'message' => 'این چک‌پوینت قبلاً ثبت شده است.',
            'errors' => [
                'duplicate' => [
                    'چک‌پوینت با همین مشخصات (نام، تعداد کارمند، ارگان، دلیل ایجاد، نوع سلاح و آدرس) قبلاً در سیستم موجود است.',
                ],
            ],
        ], 422);
    }

    public function index(Request $request)
    {
        $query = Checkpoint::query()->with(['resource:id,name,code_number,phone_number']);

        if ($s = trim((string) $request->query('search', ''))) {
            $query->where(function (Builder $q) use ($s) {
                $q->where('checkpoint_name', 'like', "%$s%")
                  ->orWhere('organ', 'like', "%$s%")
                  ->orWhere('province', 'like', "%$s%")
                  ->orWhere('village', 'like', "%$s%");
            });
        }

        $perPage = $request->query('perPage');
        $page    = $request->query('page');

        $perPageInt = filter_var($perPage, FILTER_VALIDATE_INT, ['options'=>['min_range'=>1]]);
        $pageInt    = filter_var($page,    FILTER_VALIDATE_INT, ['options'=>['min_range'=>1]]);

        // Fallback → return ALL when missing/invalid
        if ($perPageInt === false || $pageInt === false) {
            return CheckpointResource::collection($query->orderByDesc('id')->get());
        }

        $rows = $query->orderByDesc('id')->paginate($perPageInt, ['*'], 'page', $pageInt);
        return CheckpointResource::collection($rows);
    }

    public function store(StoreCheckpointRequest $request)
    {
        $user = $request->user(); // 🔔 who did this

        try {
            return DB::transaction(function () use ($request, $user) {
                $row = Checkpoint::create($request->validated());
                $row->load(['resource:id,name,code_number,phone_number']);

                // 🔔 Notification: CREATED
                NotificationService::record(
                    $user,
                    module: 'چک‌پوینت',
                    action: 'created',
                    title: 'چک‌پوینت جدید ثبت شد',
                    message: 'نام چک‌پوینت: ' . ($row->checkpoint_name ?? ''),
                    routeName: 'checkpoints',          // ✅ set your real route name
                    routeParams: ['id' => $row->id],
                    level: 'success',
                    changes: null
                );

                return new CheckpointResource($row);
            });
        } catch (QueryException $e) {
            if ($this->isDuplicateKey($e)) return $this->duplicateResponse();
            throw $e;
        }
    }

    public function show(Checkpoint $checkpoint)
    {
        $checkpoint->load(['resource:id,name,code_number,phone_number']);
        return CheckpointResource::make($checkpoint);
    }

    public function update(UpdateCheckpointRequest $request, Checkpoint $checkpoint)
    {
        $user = $request->user(); // 🔔

        try {
            return DB::transaction(function () use ($request, $checkpoint, $user) {
                // 🧮 build changes BEFORE update
                $original  = $checkpoint->getOriginal();
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

                $checkpoint->update($validated);
                $checkpoint->load(['resource:id,name,code_number,phone_number']);

                // 🔔 Notification: UPDATED + changes
                NotificationService::record(
                    $user,
                    module: 'چک‌پوینت',
                    action: 'updated',
                    title: 'چک‌پوینت اصلاح شد',
                    message: 'نام چک‌پوینت: ' . ($checkpoint->checkpoint_name ?? ''),
                    routeName: 'checkpoints',          // ✅ set your real route name
                    routeParams: ['id' => $checkpoint->id],
                    level: 'info',
                    changes: !empty($changes) ? $changes : null
                );

                return new CheckpointResource($checkpoint);
            });
        } catch (QueryException $e) {
            if ($this->isDuplicateKey($e)) return $this->duplicateResponse();
            throw $e;
        }
    }

    public function destroy(Request $request, Checkpoint $checkpoint)
    {
        $user = $request->user(); // 🔔

        return DB::transaction(function () use ($checkpoint, $user) {
            $name = $checkpoint->checkpoint_name;

            $checkpoint->delete();

            // 🔔 Notification: DELETED
            NotificationService::record(
                $user,
                module: 'چک‌پوینت',
                action: 'deleted',
                title: 'چک‌پوینت حذف شد',
                message: 'نام چک‌پوینت: ' . ($name ?? ''),
                routeName: null,
                routeParams: ['checkpoints'],
                level: 'warning',
                changes: null
            );

            return response()->json(['message' => 'Deleted']);
        });
    }
}
