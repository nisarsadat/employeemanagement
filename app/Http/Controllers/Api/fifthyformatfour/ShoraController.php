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
use Illuminate\Support\Facades\Storage;

class ShoraController extends Controller
{
    private function isDuplicateKey(QueryException $e): bool
    {
        return (int) ($e->errorInfo[1] ?? 0) === 1062; // MySQL duplicate key
    }

    private function duplicateResponse()
    {
        return response()->json([
            'message' => 'این محلي شوراګانې قبلاً ثبت شده است.',
            'errors' => [
                'duplicate' => [
                    'محلي شوراګانې با همین مشخصات (قریه، نام، هدف، تعداد افراد/جلسات، ثبت و محل جلسه) قبلاً در سیستم موجود است.',
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
                $q->where('shora_name', 'like', "%$s%")
                    ->orWhere('responsible_name', 'like', "%$s%")
                    ->orWhere('shora_province', 'like', "%$s%")
                    ->orWhere('shora_village', 'like', "%$s%");
            });
        }

        $perPage = $request->query('perPage');
        $page    = $request->query('page');

        $perPageInt = filter_var($perPage, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $pageInt    = filter_var($page, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

        // Fallback → return ALL when missing/invalid
        if ($perPageInt === false || $pageInt === false) {
            return ShoraResource::collection($query->orderByDesc('id')->get());
        }

        $rows = $query->orderByDesc('id')->paginate($perPageInt, ['*'], 'page', $pageInt);
        return ShoraResource::collection($rows);
    }

    /**
     * POST /shoras
     * Accepts:
     * - picture as FILE or STRING
     */
    public function store(StoreShoraRequest $request)
    {
        $user = $request->user(); // 🔔
        $data = $request->validated();

        try {
            return DB::transaction(function () use ($request, $data, $user) {

                // ✅ picture: FILE OR STRING (same style as SaeenFasadController)
                if ($request->hasFile('picture')) {
                    $path = $request->file('picture')->store('uploads/shoras', 'public');
                    $data['picture'] = "storage/{$path}";
                } else {
                    if (array_key_exists('picture', $data) && !is_string($data['picture'])) {
                        unset($data['picture']);
                    }
                }

                $row = Shora::create($data);
                $row->load(['resource:id,name,code_number,phone_number']);

                // 🔔 Notification: CREATED
                NotificationService::record(
                    $user,
                    module: 'محلي شوراګانې',
                    action: 'created',
                    title: 'محلي شوراګانې  ثبت شوو',
                    message: 'نوم: ' . ($row->shora_name ?? ''),
                    routeName: 'shoras',              // ✅ set your real route name
                    routeParams: ['id' => $row->id],
                    level: 'success',
                    changes: null
                );

                return (new ShoraResource($row))
                    ->additional(['message' => 'Created'])
                    ->response()
                    ->setStatusCode(201);
            });
        } catch (QueryException $e) {
            if ($this->isDuplicateKey($e)) return $this->duplicateResponse();
            throw $e;
        }
    }

    public function show(Shora $shora)
    {
        $shora->load(['resource:id,name,code_number,phone_number']);
        return ShoraResource::make($shora);
    }

    /**
     * PUT /shoras/{shora}
     * Accepts:
     * - picture as FILE or STRING (replaces old file if new uploaded)
     */
    public function update(UpdateShoraRequest $request, Shora $shora)
    {
        $user = $request->user(); // 🔔
        $data = $request->validated();

        try {
            return DB::transaction(function () use ($request, $shora, $data, $user) {
                // 🧮 changes BEFORE update
                $original = $shora->getOriginal();
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
                    if ($shora->picture && str_starts_with($shora->picture, 'storage/')) {
                        $old = str_replace('storage/', '', $shora->picture);
                        Storage::disk('public')->delete($old);
                    }

                    $path = $request->file('picture')->store('uploads/shoras', 'public');
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

                $shora->update($data);
                $shora->load(['resource:id,name,code_number,phone_number']);

                // 🔔 Notification: UPDATED
                NotificationService::record(
                    $user,
                    module: 'محلي شوراګانې',
                    action: 'updated',
                    title: 'محلي شوراګانې اصلاح شوو',
                    message: ' نوم : ' . ($shora->shora_name ?? ''),
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

            // ✅ delete picture file (same style as SaeenFasadController)
            if ($shora->picture && str_starts_with($shora->picture, 'storage/')) {
                $old = str_replace('storage/', '', $shora->picture);
                Storage::disk('public')->delete($old);
            }

            $shora->delete();

            // 🔔 Notification: DELETED
            NotificationService::record(
                $user,
                module: 'محلي شوراګانې',
                action: 'deleted',
                title: 'محلي شوراګانې حذف شوږ',
                message: 'نوم: ' . ($name ?? ''),
                routeName: null,
                routeParams: ['shoras'],
                level: 'warning',
                changes: null
            );

            return response()->json(['message' => 'Deleted']);
        });
    }
}
