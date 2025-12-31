<?php

namespace App\Http\Controllers\fifthyformatsthree;

use App\Http\Controllers\Controller;
use App\Models\fifthyformatsthree\Tujaran;
use App\Http\Resources\fifthyformatsthree\TujaranResource;
use App\Http\Requests\fifthyformatsthree\StoreTujaranRequest;
use App\Http\Requests\fifthyformatsthree\UpdateTujaranRequest;
use App\Services\NotificationService; // 🔔 notifications
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;

class TujaranController extends Controller
{
    private function isDuplicateKey(QueryException $e): bool
    {
        return (int) ($e->errorInfo[1] ?? 0) === 1062;
    }

    private function duplicateResponse()
    {
        return response()->json([
            'message' => 'دا ریکارډ مخکې ثبت شوی دی.',
            'errors'  => [
                'duplicate' => [
                    'د همدې معلوماتو سره (نوم، د پلار نوم او اصلي ادرس) ریکارډ مخکې له مخکې موجود دی.',
                ],
            ],
        ], 422);
    }

    public function index(Request $request)
    {
        $perPage = (int) $request->input('perPage', 10);
        $search  = $request->input('search');

        $q = Tujaran::query()
            ->with(['resource:id,name,code_number,phone_number'])
            ->latest();

        if ($search) {
            $q->where(function ($qq) use ($search) {
                $qq->where('name', 'like', "%{$search}%")
                   ->orWhere('father_name', 'like', "%{$search}%")
                   ->orWhere('organ', 'like', "%{$search}%")
                   ->orWhere('now_province', 'like', "%{$search}%");
            });
        }

        return TujaranResource::collection($q->paginate($perPage));
    }

    public function store(StoreTujaranRequest $request)
    {
        $user = $request->user(); // 🔔

        try {
            $item = Tujaran::create($request->validated())
                ->load(['resource:id,name,code_number,phone_number']);

            // 🔔 Notification: CREATED
            NotificationService::record(
                $user,
                module: 'تاجران',
                action: 'created',
                title: 'نوی تاجر ثبت شو',
                message: 'نوم: ' . ($item->name ?? ''),
                routeName: 'tujarans',             // ⚠️ adjust to real route name
                routeParams: ['id' => $item->id],
                level: 'success',
                changes: null
            );

            return TujaranResource::make($item);
        } catch (QueryException $e) {
            if ($this->isDuplicateKey($e)) {
                return $this->duplicateResponse();
            }
            throw $e;
        }
    }

    public function show(Tujaran $tujaran)
    {
        $tujaran->load(['resource:id,name,code_number,phone_number']);
        return TujaranResource::make($tujaran);
    }

    public function update(UpdateTujaranRequest $request, Tujaran $tujaran)
    {
        $user = $request->user(); // 🔔

        try {
            $original  = $tujaran->getOriginal();
            $validated = $request->validated();
            $changes   = [];

            foreach ($validated as $field => $newValue) {
                if (array_key_exists($field, $original)) {
                    $oldValue = $original[$field];
                    if ($oldValue != $newValue) {
                        $changes[$field] = ['old' => $oldValue, 'new' => $newValue];
                    }
                }
            }

            $tujaran->update($validated);
            $tujaran->load(['resource:id,name,code_number,phone_number']);

            // 🔔 Notification: UPDATED
            NotificationService::record(
                $user,
                module: 'تاجران',
                action: 'updated',
                title: 'تاجر اصلاح شو',
                message: 'نوم: ' . ($tujaran->name ?? ''),
                routeName: 'tujarans',
                routeParams: ['id' => $tujaran->id],
                level: 'info',
                changes: !empty($changes) ? $changes : null
            );

            return TujaranResource::make($tujaran);
        } catch (QueryException $e) {
            if ($this->isDuplicateKey($e)) {
                return $this->duplicateResponse();
            }
            throw $e;
        }
    }

    public function destroy(Request $request, Tujaran $tujaran)
    {
        $user = $request->user(); // 🔔

        $name = $tujaran->name;
        $tujaran->delete();

        // 🔔 Notification: DELETED
        NotificationService::record(
            $user,
            module: 'تاجران',
            action: 'deleted',
            title: 'تاجر حذف شو',
            message: 'نوم: ' . ($name ?? ''),
            routeName: null,
            routeParams: ['tujarans'],
            level: 'warning',
            changes: null
        );

        return response()->noContent();
    }

    public function bulkDelete(Request $request)
    {
        $user = $request->user(); // 🔔

        $ids = (array) $request->input('ids', []);
        Tujaran::destroy($ids);

        // 🔔 Notification: BULK DELETED
        NotificationService::record(
            $user,
            module: 'تاجران',
            action: 'bulk_deleted',
            title: 'ډله‌ییز حذف ترسره شو',
            message: 'حذف شوي ریکارډونه: ' . count($ids),
            routeName: null,
            routeParams: ['tujarans'],
            level: 'warning',
            changes: null
        );

        return response()->noContent();
    }
}
