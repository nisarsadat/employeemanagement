<?php

namespace App\Http\Controllers\Api\fifthyformatfour;

use App\Http\Controllers\Controller;
use App\Http\Requests\fifthyformatfour\StoreGovernmentPlaceRequest;
use App\Http\Requests\fifthyformatfour\UpdateGovernmentPlaceRequest;
use App\Http\Resources\fifthyformatfour\GovernmentPlaceResource;
use App\Models\fifthyformatfour\GovernmentPlace;
use App\Services\NotificationService; // 🔔 notifications
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GovernmentPlaceController extends Controller
{
    private function isDuplicateKey(QueryException $e): bool
    {
        // MySQL duplicate key
        return (int) ($e->errorInfo[1] ?? 0) === 1062;
    }

    private function duplicateResponse()
    {
        return response()->json([
            'message' => 'این مکان دولتی قبلاً ثبت شده است.',
            'errors' => [
                'duplicate' => [
                    'مکان دولتی با همین مشخصات (نام، مسئول، آدرس و سایر مشخصات) قبلاً در سیستم موجود است.',
                ],
            ],
        ], 422);
    }

    /**
     * GET /government-places
     */
    public function index(Request $request)
    {
        $perPage = (int) $request->input('perPage', 10);
        $search  = trim((string) $request->input('search', ''));

        $q = GovernmentPlace::query()
            ->with(['resource:id,name,code_number,phone_number'])
            ->latest('id');

        if ($search !== '') {
            $q->where(function (Builder $w) use ($search) {
                $w->where('government_place_name', 'like', "%{$search}%")
                  ->orWhere('province', 'like', "%{$search}%")
                  ->orWhere('district', 'like', "%{$search}%")
                  ->orWhere('village', 'like', "%{$search}%");
            });
        }

        return GovernmentPlaceResource::collection($q->paginate($perPage));
    }

    /**
     * POST /government-places
     */
    public function store(StoreGovernmentPlaceRequest $request)
    {
        $user = $request->user(); // 🔔
        $data = $request->validated();

        try {
            return DB::transaction(function () use ($data, $user) {
                $row = GovernmentPlace::create($data);
                $row->load(['resource:id,name,code_number,phone_number']);

                // 🔔 Notification: CREATED
                NotificationService::record(
                    $user,
                    module: 'مکان دولتی',
                    action: 'created',
                    title: 'مکان دولتی جدید ثبت شد',
                    message: 'نام مکان: ' . ($row->government_place_name ?? ''),
                    routeName: 'government-places',     // ✅ set your real route name
                    routeParams: ['id' => $row->id],
                    level: 'success',
                    changes: null
                );

                return GovernmentPlaceResource::make($row);
            });
        } catch (QueryException $e) {
            if ($this->isDuplicateKey($e)) return $this->duplicateResponse();
            throw $e;
        }
    }

    /**
     * GET /government-places/{governmentplace}
     */
    public function show(GovernmentPlace $governmentplace)
    {
        $governmentplace->load(['resource:id,name,code_number,phone_number']);
        return GovernmentPlaceResource::make($governmentplace);
    }

    /**
     * PUT /government-places/{governmentplace}
     */
    public function update(UpdateGovernmentPlaceRequest $request, GovernmentPlace $governmentplace)
    {
        $user = $request->user(); // 🔔
        $data = $request->validated();

        try {
            return DB::transaction(function () use ($data, $governmentplace, $user) {
                // 🧮 changes BEFORE update
                $original = $governmentplace->getOriginal();
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

                $governmentplace->update($data);
                $governmentplace->load(['resource:id,name,code_number,phone_number']);

                // 🔔 Notification: UPDATED
                NotificationService::record(
                    $user,
                    module: 'مکان دولتی',
                    action: 'updated',
                    title: 'مکان دولتی اصلاح شد',
                    message: 'نام مکان: ' . ($governmentplace->government_place_name ?? ''),
                    routeName: 'government-places',     // ✅ set your real route name
                    routeParams: ['id' => $governmentplace->id],
                    level: 'info',
                    changes: !empty($changes) ? $changes : null
                );

                return GovernmentPlaceResource::make($governmentplace);
            });
        } catch (QueryException $e) {
            if ($this->isDuplicateKey($e)) return $this->duplicateResponse();
            throw $e;
        }
    }

    /**
     * DELETE /government-places/{governmentplace}
     */
    public function destroy(Request $request, GovernmentPlace $governmentplace)
    {
        $user = $request->user(); // 🔔

        return DB::transaction(function () use ($governmentplace, $user) {
            $name = $governmentplace->government_place_name;

            $governmentplace->delete();

            // 🔔 Notification: DELETED
            NotificationService::record(
                $user,
                module: 'مکان دولتی',
                action: 'deleted',
                title: 'مکان دولتی حذف شد',
                message: 'نام مکان: ' . ($name ?? ''),
                routeName: null,
                routeParams: ['government-places'],
                level: 'warning',
                changes: null
            );

            return response()->noContent();
        });
    }

    /**
     * Optional bulk delete (if you need it)
     * POST /government-places/bulk-delete  with { "ids": [1,2,3] }
     */
    public function bulkDelete(Request $request)
    {
        $ids = (array) $request->input('ids', []);
        GovernmentPlace::destroy($ids);

        return response()->noContent();
    }
}
