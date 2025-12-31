<?php

namespace App\Http\Controllers\fifthyformatstow;

use App\Http\Controllers\Controller;
use App\Http\Requests\fifthyformatstow\StoreHosppitalRequest;
use App\Http\Requests\fifthyformatstow\UpdateHosppitalRequest;
use App\Http\Resources\fifthyformatstow\HosppitalResource;
use App\Models\fifthyformatstow\Hosppital;
use App\Services\NotificationService; // 🔔 notifications
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;

class HosppitalController extends Controller
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
                    'د همدې معلوماتو سره (نوم، ډول/مشخصات، او ادرس) ریکارډ مخکې له مخکې موجود دی.',
                ],
            ],
        ], 422);
    }

    public function index(Request $request)
    {
        $perPage = (int) $request->input('perPage', 10);
        $search  = $request->input('search');

        $q = Hosppital::query()
            ->with(['resource:id,name,code_number,phone_number'])
            ->latest();

        if ($search) {
            $q->where(function ($qq) use ($search) {
                $qq->where('name', 'like', "%{$search}%")
                   ->orWhere('hospita_type', 'like', "%{$search}%")
                   ->orWhere('responsible_name', 'like', "%{$search}%")
                   ->orWhere('responsible_phone', 'like', "%{$search}%")
                   ->orWhere('province', 'like', "%{$search}%")
                   ->orWhere('district', 'like', "%{$search}%");
            });
        }

        return HosppitalResource::collection($q->paginate($perPage));
    }

    public function store(StoreHosppitalRequest $request)
    {
        $user = $request->user(); // 🔔 who did this

        try {
            $hospital = Hosppital::create($request->validated())
                ->load(['resource:id,name,code_number,phone_number']);

            // 🔔 Notification: CREATED
            NotificationService::record(
                $user,
                module: 'شفاخانه',
                action: 'created',
                title: 'نوې شفاخانه ثبت شوه',
                message: 'نوم: ' . ($hospital->name ?? ''),
                routeName: 'hosppitals',           // ⚠️ set to your real route name
                routeParams: ['id' => $hospital->id],
                level: 'success',
                changes: null
            );

            return HosppitalResource::make($hospital);
        } catch (QueryException $e) {
            if ($this->isDuplicateKey($e)) {
                return $this->duplicateResponse();
            }
            throw $e;
        }
    }

    public function show(Hosppital $hosppital)
    {
        $hosppital->load(['resource:id,name,code_number,phone_number']);
        return HosppitalResource::make($hosppital);
    }

    public function update(UpdateHosppitalRequest $request, Hosppital $hosppital)
    {
        $user = $request->user(); // 🔔

        try {
            // 🧮 build changes BEFORE update
            $original  = $hosppital->getOriginal();
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

            $hosppital->update($validated);
            $hosppital->load(['resource:id,name,code_number,phone_number']);

            // 🔔 Notification: UPDATED
            NotificationService::record(
                $user,
                module: 'شفاخانه',
                action: 'updated',
                title: 'شفاخانه اصلاح شوه',
                message: 'نوم: ' . ($hosppital->name ?? ''),
                routeName: 'hosppitals',           // ⚠️ set to your real route name
                routeParams: ['id' => $hosppital->id],
                level: 'info',
                changes: !empty($changes) ? $changes : null
            );

            return HosppitalResource::make($hosppital);
        } catch (QueryException $e) {
            if ($this->isDuplicateKey($e)) {
                return $this->duplicateResponse();
            }
            throw $e;
        }
    }

    public function destroy(Request $request, Hosppital $hosppital)
    {
        $user = $request->user(); // 🔔

        $name = $hosppital->name;

        $hosppital->delete();

        // 🔔 Notification: DELETED
        NotificationService::record(
            $user,
            module: 'شفاخانه',
            action: 'deleted',
            title: 'شفاخانه حذف شوه',
            message: 'نوم: ' . ($name ?? ''),
            routeName: null,
            routeParams: ['hosppitals'],
            level: 'warning',
            changes: null
        );

        return response()->noContent();
    }

    public function bulkDelete(Request $request)
    {
        $user = $request->user(); // 🔔

        $ids = $request->input('ids', []);
        Hosppital::destroy($ids);

        // 🔔 Notification: BULK DELETED
        NotificationService::record(
            $user,
            module: 'شفاخانه',
            action: 'bulk_deleted',
            title: 'ډله‌ییز حذف ترسره شو',
            message: 'حذف شوي ریکارډونه: ' . count($ids),
            routeName: null,
            routeParams: ['hosppitals'],
            level: 'warning',
            changes: null
        );

        return response()->noContent();
    }
}
