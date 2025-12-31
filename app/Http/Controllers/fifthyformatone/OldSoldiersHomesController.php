<?php

namespace App\Http\Controllers\fifthyformatone;

use App\Http\Controllers\Controller;
use App\Http\Requests\fifthyformatone\StoreOldSoldiersHomesRequest;
use App\Http\Requests\fifthyformatone\UpdateOldSoldiersHomesRequest;
use App\Http\Resources\fifthyformatone\OldSoldiersHomesResource;
use App\Models\fifthyformatone\OldSoldiersHomes;
use App\Services\NotificationService; // 🔔 notifications
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OldSoldiersHomesController extends Controller
{
    /**
     * Pashto duplicate error message
     */
    private function duplicateErrorPashto(): array
    {
        return [
            'message' => 'دا کور مخکې ثبت شوی دی. مهرباني وکړئ تکراري معلومات مه داخلوی.',
            'errors'  => [
                'duplicate' => [
                    'دا موقعیت مخکې ثبت شوی دی (همدا ولایت، ناحیه، کوڅه او کور نمبر).',
                ],
            ],
        ];
    }

    private function isDuplicateKeyException(QueryException $e): bool
    {
        // MySQL duplicate entry error code
        return (int) ($e->errorInfo[1] ?? 0) === 1062;
    }

    /** GET /old-soldiers-homes */
    public function index(Request $request)
    {
        $perPage = (int) ($request->input('perPage', $request->input('itemsPerPage', 10)));
        $search  = $request->input('search');

        $q = OldSoldiersHomes::query()
            ->with(['resource:id,name,code_number,phone_number'])
            ->latest();

        if ($search) {
            $q->where(function ($w) use ($search) {
                $w->where('name', 'like', "%{$search}%")
                  ->orWhere('father_name', 'like', "%{$search}%")
                  ->orWhere('province', 'like', "%{$search}%")
                  ->orWhere('district', 'like', "%{$search}%")
                  ->orWhere('village', 'like', "%{$search}%")
                  ->orWhere('old_job', 'like', "%{$search}%")
                  ->orWhere('now_activity', 'like', "%{$search}%");
            });
        }

        return OldSoldiersHomesResource::collection($q->paginate($perPage));
    }

    /** POST /old-soldiers-homes */
    public function store(StoreOldSoldiersHomesRequest $request)
    {
        $user = $request->user(); // 🔔

        try {
            return DB::transaction(function () use ($request, $user) {
                $record = OldSoldiersHomes::create($request->validated());

                $record->load(['resource:id,name,code_number,phone_number']);

                // 🔔 Notification: CREATED
                NotificationService::record(
                    $user,
                    module: 'خانه‌های نظامیان سابق',
                    action: 'created',
                    title: 'خانه جدید ثبت شد',
                    message: 'نام: ' . ($record->name ?? ''),
                    routeName: 'old-soldiers-homes',
                    routeParams: ['id' => $record->id],
                    level: 'success',
                    changes: null
                );

                return (new OldSoldiersHomesResource($record))
                    ->additional(['message' => 'Created'])
                    ->response()
                    ->setStatusCode(201);
            });
        } catch (QueryException $e) {
            if ($this->isDuplicateKeyException($e)) {
                return response()->json($this->duplicateErrorPashto(), 422);
            }
            throw $e;
        }
    }

    /** GET /old-soldiers-homes/{oldSoldiersHome} */
    public function show(OldSoldiersHomes $oldSoldiersHome)
    {
        $oldSoldiersHome->load(['resource:id,name,code_number,phone_number']);
        return OldSoldiersHomesResource::make($oldSoldiersHome);
    }

    /** PUT /old-soldiers-homes/{oldSoldiersHome} */
    public function update(UpdateOldSoldiersHomesRequest $request, OldSoldiersHomes $oldSoldiersHome)
    {
        $user = $request->user(); // 🔔

        try {
            return DB::transaction(function () use ($request, $oldSoldiersHome, $user) {
                $validated = $request->validated();

                // 🧮 detect changes
                $original = $oldSoldiersHome->getOriginal();
                $changes  = [];

                foreach ($validated as $field => $newValue) {
                    if (array_key_exists($field, $original) && $original[$field] != $newValue) {
                        $changes[$field] = [
                            'old' => $original[$field],
                            'new' => $newValue,
                        ];
                    }
                }

                $oldSoldiersHome->update($validated);
                $oldSoldiersHome->load(['resource:id,name,code_number,phone_number']);

                // 🔔 Notification: UPDATED
                NotificationService::record(
                    $user,
                    module: 'خانه‌های نظامیان سابق',
                    action: 'updated',
                    title: 'خانه اصلاح شد',
                    message: 'نام: ' . ($oldSoldiersHome->name ?? ''),
                    routeName: 'old-soldiers-homes',
                    routeParams: ['id' => $oldSoldiersHome->id],
                    level: 'info',
                    changes: !empty($changes) ? $changes : null
                );

                return OldSoldiersHomesResource::make($oldSoldiersHome);
            });
        } catch (QueryException $e) {
            if ($this->isDuplicateKeyException($e)) {
                return response()->json($this->duplicateErrorPashto(), 422);
            }
            throw $e;
        }
    }

    /** DELETE /old-soldiers-homes/{oldSoldiersHome} */
    public function destroy(Request $request, OldSoldiersHomes $oldSoldiersHome)
    {
        $user = $request->user(); // 🔔
        $name = $oldSoldiersHome->name;

        $oldSoldiersHome->delete();

        // 🔔 Notification: DELETED
        NotificationService::record(
            $user,
            module: 'خانه‌های نظامیان سابق',
            action: 'deleted',
            title: 'خانه حذف شد',
            message: 'نام: ' . ($name ?? ''),
            routeName: null,
            routeParams: ['old-soldiers-homes'],
            level: 'warning',
            changes: null
        );

        return response()->noContent();
    }

    /** POST /old-soldiers-homes/bulk-delete */
    public function bulkDelete(Request $request)
    {
        $user = $request->user(); // 🔔
        $ids  = is_array($request->input('ids')) ? $request->input('ids') : [];

        if (empty($ids)) {
            return response()->noContent();
        }

        OldSoldiersHomes::whereIn('id', $ids)->delete();

        // 🔔 Notification: BULK DELETE
        NotificationService::record(
            $user,
            module: 'خانه‌های نظامیان سابق',
            action: 'deleted',
            title: 'خانه‌ها به‌صورت گروهی حذف شد',
            message: 'تعداد: ' . count($ids),
            routeName: null,
            routeParams: ['old-soldiers-homes'],
            level: 'warning',
            changes: null
        );

        return response()->noContent();
    }
}
