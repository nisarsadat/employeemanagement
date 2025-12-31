<?php

namespace App\Http\Controllers\Api\fifthyformatfour;

use App\Http\Controllers\Controller;
use App\Http\Requests\fifthyformatfour\StoreHighwayRequest;
use App\Http\Requests\fifthyformatfour\UpdateHighwayRequest;
use App\Http\Resources\fifthyformatfour\HighwayResource;
use App\Models\fifthyformatfour\Highway;
use App\Services\NotificationService; // 🔔 notifications
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HighwayController extends Controller
{
    private function isDuplicateKey(QueryException $e): bool
    {
        // MySQL duplicate key code = 1062
        return (int) ($e->errorInfo[1] ?? 0) === 1062;
    }

    private function duplicateResponse()
    {
        return response()->json([
            'message' => 'این شاهراه قبلاً ثبت شده است.',
            'errors' => [
                'duplicate' => [
                    'شاهراه با همین مشخصات (نام شاهراه و ولایت/زون/ولسوالی/قریه) قبلاً در سیستم موجود است.',
                ],
            ],
        ], 422);
    }

    public function index(Request $request)
    {
        $query = Highway::query()
            ->with(['resource:id,name,code_number,phone_number']);

        if ($s = trim((string) $request->query('search', ''))) {
            $query->where(function (Builder $q) use ($s) {
                $q->where('high_way_name', 'like', "%{$s}%")
                  ->orWhere('province', 'like', "%{$s}%")
                  ->orWhere('district', 'like', "%{$s}%")
                  ->orWhere('village', 'like', "%{$s}%");
            });
        }

        $perPage = $request->query('perPage');
        $page    = $request->query('page');

        $perPageInt = filter_var($perPage, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $pageInt    = filter_var($page,    FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

        // ✅ Same pattern: if perPage / page invalid → return all, no pagination
        if ($perPageInt === false || $pageInt === false) {
            $rows = $query->orderByDesc('id')->get();
            return HighwayResource::collection($rows);
        }

        $rows = $query->orderByDesc('id')
            ->paginate($perPageInt, ['*'], 'page', $pageInt);

        return HighwayResource::collection($rows);
    }

    public function store(StoreHighwayRequest $request)
    {
        $user = $request->user(); // 🔔 who did this

        try {
            return DB::transaction(function () use ($request, $user) {
                $row = Highway::create($request->validated());
                $row->load(['resource:id,name,code_number,phone_number']);

                // 🔔 Notification: CREATED
                NotificationService::record(
                    $user,
                    module: 'شاهراه',
                    action: 'created',
                    title: 'شاهراه جدید ثبت شد',
                    message: 'نام شاهراه: ' . ($row->high_way_name ?? ''),
                    routeName: 'highways',            // ✅ set your real route name
                    routeParams: ['id' => $row->id],
                    level: 'success',
                    changes: null
                );

                return new HighwayResource($row);
            });
        } catch (QueryException $e) {
            if ($this->isDuplicateKey($e)) return $this->duplicateResponse();
            throw $e;
        }
    }

    public function show(Highway $highway)
    {
        $highway->load(['resource:id,name,code_number,phone_number']);
        return new HighwayResource($highway);
    }

    public function update(UpdateHighwayRequest $request, Highway $highway)
    {
        $user = $request->user(); // 🔔

        try {
            return DB::transaction(function () use ($request, $highway, $user) {
                // 🧮 build changes BEFORE update (like InformationController)
                $original  = $highway->getOriginal();
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

                $highway->update($validated);
                $highway->load(['resource:id,name,code_number,phone_number']);

                // 🔔 Notification: UPDATED + changes
                NotificationService::record(
                    $user,
                    module: 'شاهراه',
                    action: 'updated',
                    title: 'شاهراه اصلاح شد',
                    message: 'نام شاهراه: ' . ($highway->high_way_name ?? ''),
                    routeName: 'highways',            // ✅ set your real route name
                    routeParams: ['id' => $highway->id],
                    level: 'info',
                    changes: !empty($changes) ? $changes : null
                );

                return new HighwayResource($highway);
            });
        } catch (QueryException $e) {
            if ($this->isDuplicateKey($e)) return $this->duplicateResponse();
            throw $e;
        }
    }

    public function destroy(Request $request, Highway $highway)
    {
        $user = $request->user(); // 🔔

        return DB::transaction(function () use ($highway, $user) {
            $name = $highway->high_way_name;

            $highway->delete();

            // 🔔 Notification: DELETED
            NotificationService::record(
                $user,
                module: 'شاهراه',
                action: 'deleted',
                title: 'شاهراه حذف شد',
                message: 'نام شاهراه: ' . ($name ?? ''),
                routeName: null,
                routeParams: ['highways'],
                level: 'warning',
                changes: null
            );

            return response()->json(['message' => 'Deleted']);
        });
    }
}
