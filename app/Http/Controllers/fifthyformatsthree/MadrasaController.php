<?php

namespace App\Http\Controllers\fifthyformatsthree;

use App\Http\Controllers\Controller;
use App\Models\fifthyformatsthree\MashkokPerson;
use App\Http\Resources\fifthyformatsthree\MashkokPersonResource;
use App\Http\Requests\fifthyformatsthree\StoreMashkokPersonRequest;
use App\Http\Requests\fifthyformatsthree\UpdateMashkokPersonRequest;
use App\Services\NotificationService; // 🔔 notifications
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;

class MashkokPersonController extends Controller
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

        $q = MashkokPerson::query()
            ->with(['resource:id,name,code_number,phone_number'])
            ->latest();

        if ($search) {
            $q->where(function ($qq) use ($search) {
                $qq->where('name', 'like', "%{$search}%")
                   ->orWhere('father_name', 'like', "%{$search}%")
                   ->orWhere('main_province', 'like', "%{$search}%")
                   ->orWhere('main_district', 'like', "%{$search}%");
            });
        }

        return MashkokPersonResource::collection($q->paginate($perPage));
    }

    public function store(StoreMashkokPersonRequest $request)
    {
        $user = $request->user(); // 🔔

        try {
            $item = MashkokPerson::create($request->validated())
                ->load(['resource:id,name,code_number,phone_number']);

            // 🔔 Notification: CREATED
            NotificationService::record(
                $user,
                module: 'مشکوک اشخاص',
                action: 'created',
                title: 'نوی مشکوک شخص ثبت شو',
                message: 'نوم: ' . ($item->name ?? ''),
                routeName: 'mashkok-people',       // ⚠️ set to your real route name
                routeParams: ['id' => $item->id],
                level: 'success',
                changes: null
            );

            return MashkokPersonResource::make($item);
        } catch (QueryException $e) {
            if ($this->isDuplicateKey($e)) {
                return $this->duplicateResponse();
            }
            throw $e;
        }
    }

    public function show(MashkokPerson $mashkokPerson)
    {
        $mashkokPerson->load(['resource:id,name,code_number,phone_number']);
        return MashkokPersonResource::make($mashkokPerson);
    }

    public function update(UpdateMashkokPersonRequest $request, MashkokPerson $mashkokPerson)
    {
        $user = $request->user(); // 🔔

        try {
            $original  = $mashkokPerson->getOriginal();
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

            $mashkokPerson->update($validated);
            $mashkokPerson->load(['resource:id,name,code_number,phone_number']);

            // 🔔 Notification: UPDATED
            NotificationService::record(
                $user,
                module: 'مشکوک اشخاص',
                action: 'updated',
                title: 'مشکوک شخص اصلاح شو',
                message: 'نوم: ' . ($mashkokPerson->name ?? ''),
                routeName: 'mashkok-people',       // ⚠️ set to your real route name
                routeParams: ['id' => $mashkokPerson->id],
                level: 'info',
                changes: !empty($changes) ? $changes : null
            );

            return MashkokPersonResource::make($mashkokPerson);
        } catch (QueryException $e) {
            if ($this->isDuplicateKey($e)) {
                return $this->duplicateResponse();
            }
            throw $e;
        }
    }

    public function destroy(Request $request, MashkokPerson $mashkokPerson)
    {
        $user = $request->user(); // 🔔

        $name = $mashkokPerson->name;
        $mashkokPerson->delete();

        // 🔔 Notification: DELETED
        NotificationService::record(
            $user,
            module: 'مشکوک اشخاص',
            action: 'deleted',
            title: 'مشکوک شخص حذف شو',
            message: 'نوم: ' . ($name ?? ''),
            routeName: null,
            routeParams: ['mashkok-people'],
            level: 'warning',
            changes: null
        );

        return response()->noContent();
    }

    public function bulkDelete(Request $request)
    {
        $user = $request->user(); // 🔔

        $ids = (array) $request->input('ids', []);
        MashkokPerson::destroy($ids);

        // 🔔 Notification: BULK DELETED
        NotificationService::record(
            $user,
            module: 'مشکوک اشخاص',
            action: 'bulk_deleted',
            title: 'ډله‌ییز حذف ترسره شو',
            message: 'حذف شوي ریکارډونه: ' . count($ids),
            routeName: null,
            routeParams: ['mashkok-people'],
            level: 'warning',
            changes: null
        );

        return response()->noContent();
    }
}
