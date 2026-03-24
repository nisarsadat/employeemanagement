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
use Illuminate\Support\Facades\Storage;

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
            'message' => 'این لوې لارې قبلاً ثبت شده است.',
            'errors' => [
                'duplicate' => [
                    'لوې لارې با همین مشخصات (نوم و ولایت/زون/ولسوالی/قریه) قبلاً در سیستم موجود است.',
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
        $pageInt    = filter_var($page, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

        // ✅ Same pattern: if perPage / page invalid → return all, no pagination
        if ($perPageInt === false || $pageInt === false) {
            $rows = $query->orderByDesc('id')->get();
            return HighwayResource::collection($rows);
        }

        $rows = $query->orderByDesc('id')
            ->paginate($perPageInt, ['*'], 'page', $pageInt);

        return HighwayResource::collection($rows);
    }

    /**
     * POST /highways
     * Accepts:
     * - picture as FILE or STRING
     */
    public function store(StoreHighwayRequest $request)
    {
        $user = $request->user(); // 🔔 who did this
        $data = $request->validated();

        try {
            return DB::transaction(function () use ($request, $data, $user) {

                // ✅ picture: FILE OR STRING (same style as SaeenFasadController)
                if ($request->hasFile('picture')) {
                    $path = $request->file('picture')->store('uploads/highways', 'public');
                    $data['picture'] = "storage/{$path}";
                } else {
                    if (array_key_exists('picture', $data) && !is_string($data['picture'])) {
                        unset($data['picture']);
                    }
                }

                $row = Highway::create($data);
                $row->load(['resource:id,name,code_number,phone_number']);

                // 🔔 Notification: CREATED
                NotificationService::record(
                    $user,
                    module: 'لوې لارې',
                    action: 'created',
                    title: 'لوې لارې  ثبت شوو',
                    message: 'نوم: ' . ($row->high_way_name ?? ''),
                    routeName: 'highways',            // ✅ set your real route name
                    routeParams: ['id' => $row->id],
                    level: 'success',
                    changes: null
                );

                return (new HighwayResource($row))
                    ->additional(['message' => 'Created'])
                    ->response()
                    ->setStatusCode(201);
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

    /**
     * PUT /highways/{highway}
     * Accepts:
     * - picture as FILE or STRING (replaces old file if new uploaded)
     */
    public function update(UpdateHighwayRequest $request, Highway $highway)
    {
        $user = $request->user(); // 🔔
        $data = $request->validated();

        try {
            return DB::transaction(function () use ($request, $highway, $data, $user) {
                // 🧮 build changes BEFORE update (like your current logic)
                $original = $highway->getOriginal();
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
                    if ($highway->picture && str_starts_with($highway->picture, 'storage/')) {
                        $old = str_replace('storage/', '', $highway->picture);
                        Storage::disk('public')->delete($old);
                    }

                    $path = $request->file('picture')->store('uploads/highways', 'public');
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

                $highway->update($data);
                $highway->load(['resource:id,name,code_number,phone_number']);

                // 🔔 Notification: UPDATED + changes
                NotificationService::record(
                    $user,
                    module: 'لوې لارې',
                    action: 'updated',
                    title: 'لوې لارې اصلاح شوو',
                    message: 'نوم: ' . ($highway->high_way_name ?? ''),
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

            // ✅ delete picture file (same style as SaeenFasadController)
            if ($highway->picture && str_starts_with($highway->picture, 'storage/')) {
                $old = str_replace('storage/', '', $highway->picture);
                Storage::disk('public')->delete($old);
            }

            $highway->delete();

            // 🔔 Notification: DELETED
            NotificationService::record(
                $user,
                module: 'لوې لارې',
                action: 'deleted',
                title: 'لوې لارې حذف شوو',
                message: 'نوم: ' . ($name ?? ''),
                routeName: null,
                routeParams: ['highways'],
                level: 'warning',
                changes: null
            );

            return response()->json(['message' => 'Deleted']);
        });
    }
}
