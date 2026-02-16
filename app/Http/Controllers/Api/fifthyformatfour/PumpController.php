<?php

namespace App\Http\Controllers\Api\fifthyformatfour;

use App\Http\Controllers\Controller;
use App\Http\Requests\fifthyformatfour\StorePumpRequest;
use App\Http\Requests\fifthyformatfour\UpdatePumpRequest;
use App\Http\Resources\fifthyformatfour\PumpResource;
use App\Models\fifthyformatfour\Pump;
use App\Services\NotificationService; // 🔔 notifications
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PumpController extends Controller
{
    private function isDuplicateKey(QueryException $e): bool
    {
        return (int) ($e->errorInfo[1] ?? 0) === 1062; // MySQL duplicate key
    }

    private function duplicateResponse()
    {
        return response()->json([
            'message' => 'این پمپ قبلاً ثبت شده است.',
            'errors' => [
                'duplicate' => [
                    'پمپ با همین مشخصات (نام پمپ، نوع، مالک و ولایت/زون/ولسوالی/قریه) قبلاً در سیستم موجود است.',
                ],
            ],
        ], 422);
    }

    public function index(Request $request)
    {
        $query = Pump::query()->with(['resource:id,name,code_number,phone_number']);

        if ($s = trim((string) $request->query('search', ''))) {
            $query->where(function (Builder $q) use ($s) {
                $q->where('pump_name', 'like', "%$s%")
                    ->orWhere('main_owner', 'like', "%$s%")
                    ->orWhere('province', 'like', "%$s%")
                    ->orWhere('village', 'like', "%$s%");
            });
        }

        $perPage = $request->query('perPage');
        $page    = $request->query('page');
        $perPageInt = filter_var($perPage, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $pageInt    = filter_var($page, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

        // Fallback → return ALL when invalid/missing
        if ($perPageInt === false || $pageInt === false) {
            return PumpResource::collection($query->orderByDesc('id')->get());
        }

        $rows = $query->orderByDesc('id')->paginate($perPageInt, ['*'], 'page', $pageInt);
        return PumpResource::collection($rows);
    }

    /**
     * POST /pumps
     * Accepts:
     * - picture as FILE or STRING
     */
    public function store(StorePumpRequest $request)
    {
        $user = $request->user(); // 🔔
        $data = $request->validated();

        try {
            return DB::transaction(function () use ($request, $data, $user) {

                // ✅ picture: FILE OR STRING (same style as SaeenFasadController)
                if ($request->hasFile('picture')) {
                    $path = $request->file('picture')->store('uploads/pumps', 'public');
                    $data['picture'] = "storage/{$path}";
                } else {
                    if (array_key_exists('picture', $data) && !is_string($data['picture'])) {
                        unset($data['picture']);
                    }
                }

                $row = Pump::create($data);
                $row->load(['resource:id,name,code_number,phone_number']);

                // 🔔 Notification: CREATED
                NotificationService::record(
                    $user,
                    module: 'پمپ',
                    action: 'created',
                    title: 'پمپ جدید ثبت شد',
                    message: 'نام پمپ: ' . ($row->pump_name ?? ''),
                    routeName: 'pumps',               // ✅ set your real route name
                    routeParams: ['id' => $row->id],
                    level: 'success',
                    changes: null
                );

                return (new PumpResource($row))
                    ->additional(['message' => 'Created'])
                    ->response()
                    ->setStatusCode(201);
            });
        } catch (QueryException $e) {
            if ($this->isDuplicateKey($e)) return $this->duplicateResponse();
            throw $e;
        }
    }

    public function show(Pump $pump)
    {
        $pump->load(['resource:id,name,code_number,phone_number']);
        return PumpResource::make($pump);
    }

    /**
     * PUT /pumps/{pump}
     * Accepts:
     * - picture as FILE or STRING (replaces old file if new uploaded)
     */
    public function update(UpdatePumpRequest $request, Pump $pump)
    {
        $user = $request->user(); // 🔔
        $data = $request->validated();

        try {
            return DB::transaction(function () use ($request, $pump, $data, $user) {
                // 🧮 changes BEFORE update
                $original = $pump->getOriginal();
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
                    if ($pump->picture && str_starts_with($pump->picture, 'storage/')) {
                        $old = str_replace('storage/', '', $pump->picture);
                        Storage::disk('public')->delete($old);
                    }

                    $path = $request->file('picture')->store('uploads/pumps', 'public');
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

                $pump->update($data);
                $pump->load(['resource:id,name,code_number,phone_number']);

                // 🔔 Notification: UPDATED
                NotificationService::record(
                    $user,
                    module: 'پمپ',
                    action: 'updated',
                    title: 'پمپ اصلاح شد',
                    message: 'نام پمپ: ' . ($pump->pump_name ?? ''),
                    routeName: 'pumps',               // ✅ set your real route name
                    routeParams: ['id' => $pump->id],
                    level: 'info',
                    changes: !empty($changes) ? $changes : null
                );

                return new PumpResource($pump);
            });
        } catch (QueryException $e) {
            if ($this->isDuplicateKey($e)) return $this->duplicateResponse();
            throw $e;
        }
    }

    public function destroy(Request $request, Pump $pump)
    {
        $user = $request->user(); // 🔔

        return DB::transaction(function () use ($pump, $user) {
            $name = $pump->pump_name;

            // ✅ delete picture file
            if ($pump->picture && str_starts_with($pump->picture, 'storage/')) {
                $old = str_replace('storage/', '', $pump->picture);
                Storage::disk('public')->delete($old);
            }

            $pump->delete();

            // 🔔 Notification: DELETED
            NotificationService::record(
                $user,
                module: 'پمپ',
                action: 'deleted',
                title: 'پمپ حذف شد',
                message: 'نام پمپ: ' . ($name ?? ''),
                routeName: null,
                routeParams: ['pumps'],
                level: 'warning',
                changes: null
            );

            return response()->json(['message' => 'Deleted']);
        });
    }
}
