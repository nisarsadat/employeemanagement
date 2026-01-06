<?php

namespace App\Http\Controllers\fifthyformatstow;

use App\Http\Controllers\Controller;
use App\Http\Requests\fifthyformatstow\StoreWorkShopRequest;
use App\Http\Requests\fifthyformatstow\UpdateWorkShopRequest;
use App\Http\Resources\fifthyformatstow\WorkShopResource;
use App\Models\fifthyformatstow\WorkShop;
use App\Services\NotificationService; // 🔔 notifications
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;

class WorkShopController extends Controller
{
    private function isDuplicateKey(QueryException $e): bool
    {
        // MySQL duplicate key
        return (int) ($e->errorInfo[1] ?? 0) === 1062;
    }

    private function duplicateResponse()
    {
        return response()->json([
            'message' => 'دا ریکارډ مخکې ثبت شوی دی.',
            'errors'  => [
                'duplicate' => [
                    'د همدې معلوماتو سره (ورکشاپ نوم، ډول، ولایت او زون) ریکارډ مخکې له مخکې موجود دی.',
                ],
            ],
        ], 422);
    }

    public function index(Request $request)
    {
        $perPage = (int) $request->input('perPage', 10);
        $search  = $request->input('search');

        $q = WorkShop::query()
            ->with(['resource:id,name,code_number,phone_number'])
            ->latest();

        if ($search) {
            $q->where(function ($qq) use ($search) {
                $qq->where('workshop_name', 'like', "%{$search}%")
                   ->orWhere('workshop_type', 'like', "%{$search}%")
                   ->orWhere('responsible_name', 'like', "%{$search}%")
                   ->orWhere('responsible_phone', 'like', "%{$search}%")
                   ->orWhere('province', 'like', "%{$search}%")
                   ->orWhere('district', 'like', "%{$search}%");
            });
        }

        return WorkShopResource::collection($q->paginate($perPage));
    }

    public function store(StoreWorkShopRequest $request)
    {
        $user = $request->user(); // 🔔 who did this

        try {
            $workshop = WorkShop::create($request->validated())
                ->load(['resource:id,name,code_number,phone_number']);

            // 🔔 Notification: CREATED
            NotificationService::record(
                $user,
                module: 'ورکشاپ',
                action: 'created',
                title: 'نوی ورکشاپ ثبت شو',
                message: 'نوم: ' . ($workshop->workshop_name ?? ''),
                routeName: 'work-shops',           // ⚠️ set to your real route name
                routeParams: ['id' => $workshop->id],
                level: 'success',
                changes: null
            );

            return WorkShopResource::make($workshop);
        } catch (QueryException $e) {
            if ($this->isDuplicateKey($e)) {
                return $this->duplicateResponse();
            }
            throw $e;
        }
    }

    public function show(WorkShop $workShop)
    {
        $workShop->load(['resource:id,name,code_number,phone_number']);
        return WorkShopResource::make($workShop);
    }

    public function update(UpdateWorkShopRequest $request, WorkShop $workShop)
    {
        $user = $request->user(); // 🔔

        try {
            // 🧮 build changes BEFORE update
            $original  = $workShop->getOriginal();
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

            $workShop->update($validated);
            $workShop->load(['resource:id,name,code_number,phone_number']);

            // 🔔 Notification: UPDATED
            NotificationService::record(
                $user,
                module: 'ورکشاپ',
                action: 'updated',
                title: 'ورکشاپ اصلاح شو',
                message: 'نوم: ' . ($workShop->workshop_name ?? ''),
                routeName: 'work-shops',           // ⚠️ set to your real route name
                routeParams: ['id' => $workShop->id],
                level: 'info',
                changes: !empty($changes) ? $changes : null
            );

            return WorkShopResource::make($workShop);
        } catch (QueryException $e) {
            if ($this->isDuplicateKey($e)) {
                return $this->duplicateResponse();
            }
            throw $e;
        }
    }

    public function destroy(Request $request, WorkShop $workShop)
    {
        $user = $request->user(); // 🔔

        $name = $workShop->workshop_name;

        $workShop->delete();

        // 🔔 Notification: DELETED
        NotificationService::record(
            $user,
            module: 'ورکشاپ',
            action: 'deleted',
            title: 'ورکشاپ حذف شو',
            message: 'نوم: ' . ($name ?? ''),
            routeName: null,
            routeParams: ['work-shops'],
            level: 'warning',
            changes: null
        );

        return response()->noContent();
    }

    public function bulkDelete(Request $request)
    {
        $user = $request->user(); // 🔔

        $ids = $request->input('ids', []);
        WorkShop::destroy($ids);

        // 🔔 Notification: BULK DELETED
        NotificationService::record(
            $user,
            module: 'ورکشاپ',
            action: 'bulk_deleted',
            title: 'ډله‌ییز حذف ترسره شو',
            message: 'حذف شوي ریکارډونه: ' . count($ids),
            routeName: null,
            routeParams: null,
            level: 'warning',
            changes: [
                'ids' => [
                    'old' => null,
                    'new' => $ids,
                ],
            ]
        );

        return response()->json([
            'message' => 'Bulk deleted',
            'deleted' => count($ids),
            'ids'     => $ids,
        ]);
    }
}
