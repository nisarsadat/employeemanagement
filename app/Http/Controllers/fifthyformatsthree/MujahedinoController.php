<?php

namespace App\Http\Controllers\fifthyformatsthree;

use App\Http\Controllers\Controller;
use App\Models\fifthyformatsthree\Mujahedino;
use App\Http\Resources\fifthyformatsthree\MujahedinoResource;
use App\Http\Requests\fifthyformatsthree\StoreMujahedinoRequest;
use App\Http\Requests\fifthyformatsthree\UpdateMujahedinoRequest;
use App\Services\NotificationService; // 🔔 notifications
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;

class MujahedinoController extends Controller
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
                    'د همدې معلوماتو سره (لقب/نوم، د پلار نوم او اصلي ادرس) ریکارډ مخکې له مخکې موجود دی.',
                ],
            ],
        ], 422);
    }

    public function index(Request $request)
    {
        $perPage = (int) $request->input('perPage', 10);
        $search  = $request->input('search');

        $q = Mujahedino::query()
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

        return MujahedinoResource::collection($q->paginate($perPage));
    }

    public function store(StoreMujahedinoRequest $request)
    {
        $user = $request->user(); // 🔔

        try {
            $item = Mujahedino::create($request->validated())
                ->load(['resource:id,name,code_number,phone_number']);

            // 🔔 Notification: CREATED
            NotificationService::record(
                $user,
                module: 'مجاهدین',
                action: 'created',
                title: 'نوی مجاهد ثبت شو',
                message: 'نوم: ' . ($item->name ?? ''),
                routeName: 'mujahedinos',          // ⚠️ set to your real route name
                routeParams: ['id' => $item->id],
                level: 'success',
                changes: null
            );

            return MujahedinoResource::make($item);
        } catch (QueryException $e) {
            if ($this->isDuplicateKey($e)) {
                return $this->duplicateResponse();
            }
            throw $e;
        }
    }

    public function show(Mujahedino $mujahedino)
    {
        $mujahedino->load(['resource:id,name,code_number,phone_number']);
        return MujahedinoResource::make($mujahedino);
    }

    public function update(UpdateMujahedinoRequest $request, Mujahedino $mujahedino)
    {
        $user = $request->user(); // 🔔

        try {
            $original  = $mujahedino->getOriginal();
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

            $mujahedino->update($validated);
            $mujahedino->load(['resource:id,name,code_number,phone_number']);

            // 🔔 Notification: UPDATED
            NotificationService::record(
                $user,
                module: 'مجاهدین',
                action: 'updated',
                title: 'مجاهد اصلاح شو',
                message: 'نوم: ' . ($mujahedino->name ?? ''),
                routeName: 'mujahedinos',
                routeParams: ['id' => $mujahedino->id],
                level: 'info',
                changes: !empty($changes) ? $changes : null
            );

            return MujahedinoResource::make($mujahedino);
        } catch (QueryException $e) {
            if ($this->isDuplicateKey($e)) {
                return $this->duplicateResponse();
            }
            throw $e;
        }
    }

    public function destroy(Request $request, Mujahedino $mujahedino)
    {
        $user = $request->user(); // 🔔

        $name = $mujahedino->name;
        $mujahedino->delete();

        // 🔔 Notification: DELETED
        NotificationService::record(
            $user,
            module: 'مجاهدین',
            action: 'deleted',
            title: 'مجاهد حذف شو',
            message: 'نوم: ' . ($name ?? ''),
            routeName: null,
            routeParams: ['mujahedinos'],
            level: 'warning',
            changes: null
        );

        return response()->noContent();
    }

    public function bulkDelete(Request $request)
    {
        $user = $request->user(); // 🔔

        $ids = (array) $request->input('ids', []);
        Mujahedino::destroy($ids);

        // 🔔 Notification: BULK DELETED
        NotificationService::record(
            $user,
            module: 'مجاهدین',
            action: 'bulk_deleted',
            title: 'ډله‌ییز حذف ترسره شو',
            message: 'حذف شوي ریکارډونه: ' . count($ids),
            routeName: null,
            routeParams: ['mujahedinos'],
            level: 'warning',
            changes: null
        );

        return response()->noContent();
    }
}
