<?php

namespace App\Http\Controllers\fifthyformatsthree;

use App\Http\Controllers\Controller;
use App\Models\fifthyformatsthree\Sarayano;
use App\Http\Resources\fifthyformatsthree\SarayanoResource;
use App\Http\Requests\fifthyformatsthree\StoreSarayanoRequest;
use App\Http\Requests\fifthyformatsthree\UpdateSarayanoRequest;
use App\Services\NotificationService; // 🔔 notifications
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;

class SarayanoController extends Controller
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
                    'د همدې معلوماتو سره (سراي نوم، ډول، او ادرس) ریکارډ مخکې له مخکې موجود دی.',
                ],
            ],
        ], 422);
    }

    public function index(Request $request)
    {
        $perPage = (int) $request->input('perPage', 10);
        $search  = $request->input('search');

        $q = Sarayano::query()
            ->with(['resource:id,name,code_number,phone_number'])
            ->latest();

        if ($search) {
            $q->where(function ($qq) use ($search) {
                $qq->where('saray_name', 'like', "%{$search}%")
                   ->orWhere('responsible_name', 'like', "%{$search}%")
                   ->orWhere('province', 'like', "%{$search}%");
            });
        }

        return SarayanoResource::collection($q->paginate($perPage));
    }

    public function store(StoreSarayanoRequest $request)
    {
        $user = $request->user(); // 🔔 who did this

        try {
            $saray = Sarayano::create($request->validated())
                ->load(['resource:id,name,code_number,phone_number']);

            // 🔔 Notification: CREATED
            NotificationService::record(
                $user,
                module: 'سرايان',
                action: 'created',
                title: 'نوی سراي ثبت شو',
                message: 'نوم: ' . ($saray->saray_name ?? ''),
                routeName: 'sarayano',               // ⚠️ set to your real route name
                routeParams: ['id' => $saray->id],
                level: 'success',
                changes: null
            );

            return SarayanoResource::make($saray);
        } catch (QueryException $e) {
            if ($this->isDuplicateKey($e)) {
                return $this->duplicateResponse();
            }
            throw $e;
        }
    }

    public function show(Sarayano $sarayano)
    {
        $sarayano->load(['resource:id,name,code_number,phone_number']);
        return SarayanoResource::make($sarayano);
    }

    public function update(UpdateSarayanoRequest $request, Sarayano $sarayano)
    {
        $user = $request->user(); // 🔔

        try {
            // 🧮 build changes BEFORE update
            $original  = $sarayano->getOriginal();
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

            $sarayano->update($validated);
            $sarayano->load(['resource:id,name,code_number,phone_number']);

            // 🔔 Notification: UPDATED
            NotificationService::record(
                $user,
                module: 'سرايان',
                action: 'updated',
                title: 'سراي اصلاح شو',
                message: 'نوم: ' . ($sarayano->saray_name ?? ''),
                routeName: 'sarayano',               // ⚠️ set to your real route name
                routeParams: ['id' => $sarayano->id],
                level: 'info',
                changes: !empty($changes) ? $changes : null
            );

            return SarayanoResource::make($sarayano);
        } catch (QueryException $e) {
            if ($this->isDuplicateKey($e)) {
                return $this->duplicateResponse();
            }
            throw $e;
        }
    }

    public function destroy(Request $request, Sarayano $sarayano)
    {
        $user = $request->user(); // 🔔
        $name = $sarayano->saray_name;

        $sarayano->delete();

        // 🔔 Notification: DELETED
        NotificationService::record(
            $user,
            module: 'سرايان',
            action: 'deleted',
            title: 'سراي حذف شو',
            message: 'نوم: ' . ($name ?? ''),
            routeName: null,
            routeParams: ['sarayano'],
            level: 'warning',
            changes: null
        );

        return response()->noContent();
    }

    public function bulkDelete(Request $request)
    {
        $user = $request->user(); // 🔔

        $ids = $request->input('ids', []);
        Sarayano::destroy($ids);

        // 🔔 Notification: BULK DELETED
        NotificationService::record(
            $user,
            module: 'سرايان',
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
