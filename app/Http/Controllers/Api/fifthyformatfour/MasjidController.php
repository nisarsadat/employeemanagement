<?php

namespace App\Http\Controllers\Api\fifthyformatfour;

use App\Http\Controllers\Controller;
use App\Http\Requests\fifthyformatfour\StoreMasjidRequest;
use App\Http\Requests\fifthyformatfour\UpdateMasjidRequest;
use App\Http\Resources\fifthyformatfour\MasjidResource;
use App\Models\fifthyformatfour\Masjid;
use App\Services\NotificationService; // 🔔 notifications
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MasjidController extends Controller
{
    private function isDuplicateKey(QueryException $e): bool
    {
        // MySQL duplicate key code = 1062
        return (int) ($e->errorInfo[1] ?? 0) === 1062;
    }

    private function duplicateResponse()
    {
        return response()->json([
            'message' => 'این مسجد قبلاً ثبت شده است.',
            'errors' => [
                'duplicate' => [
                    'مسجد با همین مشخصات (نام، نوع، ولایت/زون/ولسوالی/قریه) قبلاً در سیستم موجود است.',
                ],
            ],
        ], 422);
    }

    /**
     * GET /masjids
     */
    public function index(Request $request)
    {
        $query = Masjid::query()
            ->with(['resource:id,name,code_number,phone_number']);

        if ($s = trim((string) $request->query('search', ''))) {
            $query->where(function (Builder $q) use ($s) {
                $q->where('masjid_name', 'like', "%$s%")
                  ->orWhere('responsible_name', 'like', "%$s%")
                  ->orWhere('responsible_f_name', 'like', "%$s%")
                  ->orWhere('masjid_village', 'like', "%$s%");
            });
        }

        $perPage = $request->query('perPage');
        $page    = $request->query('page');

        $perPageInt = filter_var($perPage, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $pageInt    = filter_var($page,    FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

        if ($perPageInt === false || $pageInt === false) {
            $rows = $query->orderByDesc('id')->get();
            return MasjidResource::collection($rows);
        }

        $rows = $query
            ->orderByDesc('id')
            ->paginate($perPageInt, ['*'], 'page', $pageInt);

        return MasjidResource::collection($rows);
    }

    /**
     * POST /masjids
     */
    public function store(StoreMasjidRequest $request)
    {
        $user = $request->user(); // 🔔 who did this

        try {
            return DB::transaction(function () use ($request, $user) {
                $data = $request->validated();

                $row = Masjid::create($data);
                $row->load(['resource:id,name,code_number,phone_number']);

                // 🔔 Notification: CREATED
                NotificationService::record(
                    $user,
                    module: 'مسجد',
                    action: 'created',
                    title: 'مسجد جدید ثبت شد',
                    message: 'نام مسجد: ' . ($row->masjid_name ?? ''),
                    routeName: 'masjids',              // ✅ set your real route name
                    routeParams: ['id' => $row->id],
                    level: 'success',
                    changes: null
                );

                return new MasjidResource($row);
            });
        } catch (QueryException $e) {
            if ($this->isDuplicateKey($e)) return $this->duplicateResponse();
            throw $e;
        }
    }

    /**
     * GET /masjids/{masjid}
     */
    public function show(Masjid $masjid)
    {
        $masjid->load(['resource:id,name,code_number,phone_number']);
        return new MasjidResource($masjid);
    }

    /**
     * PUT /masjids/{masjid}
     */
    public function update(UpdateMasjidRequest $request, Masjid $masjid)
    {
        $user = $request->user(); // 🔔

        try {
            return DB::transaction(function () use ($request, $masjid, $user) {
                // 🧮 build changes BEFORE update (like InformationController)
                $original  = $masjid->getOriginal();
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

                // 1️⃣ Update Masjid fields (including resource_id)
                $masjid->update($validated);

                // 2️⃣ (Optional) update linked Resource code/phone if frontend sends them
                $resourceCode  = $request->input('resource_code');   // extra field
                $resourcePhone = $request->input('resource_phone');  // extra field

                if (($resourceCode !== null || $resourcePhone !== null) && $masjid->resource) {
                    $updates = [];

                    if ($resourceCode !== null) {
                        $updates['code_number'] = $resourceCode;
                    }
                    if ($resourcePhone !== null) {
                        $updates['phone_number'] = $resourcePhone;
                    }

                    if (!empty($updates)) {
                        $masjid->resource->update($updates);
                    }
                }

                // 3️⃣ Reload
                $masjid->load(['resource:id,name,code_number,phone_number']);

                // 🔔 Notification: UPDATED + changes
                NotificationService::record(
                    $user,
                    module: 'مسجد',
                    action: 'updated',
                    title: 'مسجد اصلاح شد',
                    message: 'نام مسجد: ' . ($masjid->masjid_name ?? ''),
                    routeName: 'masjids',              // ✅ set your real route name
                    routeParams: ['id' => $masjid->id],
                    level: 'info',
                    changes: !empty($changes) ? $changes : null
                );

                return new MasjidResource($masjid);
            });
        } catch (QueryException $e) {
            if ($this->isDuplicateKey($e)) return $this->duplicateResponse();
            throw $e;
        }
    }

    /**
     * DELETE /masjids/{masjid}
     */
    public function destroy(Request $request, Masjid $masjid)
    {
        $user = $request->user(); // 🔔

        return DB::transaction(function () use ($masjid, $user) {
            $name = $masjid->masjid_name;

            $masjid->delete();

            // 🔔 Notification: DELETED
            NotificationService::record(
                $user,
                module: 'مسجد',
                action: 'deleted',
                title: 'مسجد حذف شد',
                message: 'نام مسجد: ' . ($name ?? ''),
                routeName: null,
                routeParams: ['masjids'],
                level: 'warning',
                changes: null
            );

            return response()->json(['message' => 'Deleted']);
        });
    }
}
