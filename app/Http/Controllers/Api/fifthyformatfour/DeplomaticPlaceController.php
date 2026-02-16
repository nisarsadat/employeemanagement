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
        $query = DeplomaticPlace::query()->with(['resource:id,name,code_number,phone_number']);

        if ($s = trim((string) $request->query('search', ''))) {
            $query->where(function (Builder $q) use ($s) {
                $q->where('deplomatic_place_name', 'like', "%$s%")
                  ->orWhere('province', 'like', "%$s%")
                  ->orWhere('zone', 'like', "%$s%")
                  ->orWhere('district', 'like', "%$s%")
                  ->orWhere('village', 'like', "%$s%")
                  ->orWhere('specefic_place', 'like', "%$s%");
            });
        }

        $perPage = $request->query('perPage');
        $page    = $request->query('page');

        $perPageInt = filter_var($perPage, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $pageInt    = filter_var($page, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

        // Fallback → return ALL when missing/invalid
        if ($perPageInt === false || $pageInt === false) {
            return DeplomaticPlaceResource::collection($query->orderByDesc('id')->get());
        }

        $rows = $query->orderByDesc('id')->paginate($perPageInt, ['*'], 'page', $pageInt);
        return DeplomaticPlaceResource::collection($rows);
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
