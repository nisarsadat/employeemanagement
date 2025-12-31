<?php

namespace App\Http\Controllers\fifthyformatstow;

use App\Http\Controllers\Controller;
use App\Http\Requests\fifthyformatstow\StoreRahnomaMamlatRequest;
use App\Http\Requests\fifthyformatstow\UpdateRahnomaMamlatRequest;
use App\Http\Resources\fifthyformatstow\RahnomaMamlatResource;
use App\Models\fifthyformatstow\RahnomaMamlat;
use App\Services\NotificationService; // 🔔 notifications
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;

class RahnomaMamlatController extends Controller
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
                    'د همدې معلوماتو سره (نوم، د مسئول معلومات، او ادرس) ریکارډ مخکې له مخکې موجود دی.',
                ],
            ],
        ], 422);
    }

    public function index(Request $request)
    {
        $perPage = (int) $request->input('perPage', 10);
        $search  = $request->input('search');

        $q = RahnomaMamlat::query()
            ->with(['resource:id,name,code_number,phone_number'])
            ->latest();

        if ($search) {
            $q->where(function ($qq) use ($search) {
                $qq->where('name', 'like', "%{$search}%")
                   ->orWhere('responsible_name', 'like', "%{$search}%")
                   ->orWhere('responsible_phone', 'like', "%{$search}%")
                   ->orWhere('province', 'like', "%{$search}%")
                   ->orWhere('district', 'like', "%{$search}%");
            });
        }

        return RahnomaMamlatResource::collection($q->paginate($perPage));
    }

    public function store(StoreRahnomaMamlatRequest $request)
    {
        $user = $request->user(); // 🔔 who did this

        try {
            $rahnoma = RahnomaMamlat::create($request->validated())
                ->load(['resource:id,name,code_number,phone_number']);

            // 🔔 Notification: CREATED
            NotificationService::record(
                $user,
                module: 'رهنما مملات',
                action: 'created',
                title: 'نوی رهنما مملات ثبت شو',
                message: 'نوم: ' . ($rahnoma->name ?? ''),
                routeName: 'rahnoma-mamlats',       // ⚠️ set to your real route name
                routeParams: ['id' => $rahnoma->id],
                level: 'success',
                changes: null
            );

            return RahnomaMamlatResource::make($rahnoma);
        } catch (QueryException $e) {
            if ($this->isDuplicateKey($e)) {
                return $this->duplicateResponse();
            }
            throw $e;
        }
    }

    public function show(RahnomaMamlat $rahnomaMamlat)
    {
        $rahnomaMamlat->load(['resource:id,name,code_number,phone_number']);
        return RahnomaMamlatResource::make($rahnomaMamlat);
    }

    public function update(UpdateRahnomaMamlatRequest $request, RahnomaMamlat $rahnomaMamlat)
    {
        $user = $request->user(); // 🔔

        try {
            // 🧮 build changes BEFORE update
            $original  = $rahnomaMamlat->getOriginal();
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

            $rahnomaMamlat->update($validated);
            $rahnomaMamlat->load(['resource:id,name,code_number,phone_number']);

            // 🔔 Notification: UPDATED
            NotificationService::record(
                $user,
                module: 'رهنما مملات',
                action: 'updated',
                title: 'رهنما مملات اصلاح شو',
                message: 'نوم: ' . ($rahnomaMamlat->name ?? ''),
                routeName: 'rahnoma-mamlats',       // ⚠️ set to your real route name
                routeParams: ['id' => $rahnomaMamlat->id],
                level: 'info',
                changes: !empty($changes) ? $changes : null
            );

            return RahnomaMamlatResource::make($rahnomaMamlat);
        } catch (QueryException $e) {
            if ($this->isDuplicateKey($e)) {
                return $this->duplicateResponse();
            }
            throw $e;
        }
    }

    public function destroy(Request $request, RahnomaMamlat $rahnomaMamlat)
    {
        $user = $request->user(); // 🔔

        $name = $rahnomaMamlat->name;

        $rahnomaMamlat->delete();

        // 🔔 Notification: DELETED
        NotificationService::record(
            $user,
            module: 'رهنما مملات',
            action: 'deleted',
            title: 'رهنما مملات حذف شو',
            message: 'نوم: ' . ($name ?? ''),
            routeName: null,
            routeParams: ['rahnoma-mamlats'],
            level: 'warning',
            changes: null
        );

        return response()->noContent();
    }

    public function bulkDelete(Request $request)
    {
        $user = $request->user(); // 🔔

        $ids = $request->input('ids', []);
        RahnomaMamlat::destroy($ids);

        // 🔔 Notification: BULK DELETED
        NotificationService::record(
            $user,
            module: 'رهنما مملات',
            action: 'bulk_deleted',
            title: 'ډله‌ییز حذف ترسره شو',
            message: 'حذف شوي ریکارډونه: ' . count($ids),
            routeName: null,
            routeParams: ['rahnoma-mamlats'],
            level: 'warning',
            changes: null
        );

        return response()->noContent();
    }
}
