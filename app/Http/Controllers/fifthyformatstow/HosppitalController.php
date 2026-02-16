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
use Illuminate\Support\Facades\Storage;

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
        $data = $request->validated();

        try {
            // ✅ picture: file OR string (same style as SaeenFasad)
            if ($request->hasFile('picture')) {
                $path = $request->file('picture')->store('uploads/hosppitals', 'public');
                $data['picture'] = "storage/{$path}";
            } else {
                if (array_key_exists('picture', $data) && !is_string($data['picture'])) {
                    unset($data['picture']);
                }
            }

            $hospital = Hosppital::create($data)
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
        $data = $request->validated();

        try {
            // 🧮 build changes BEFORE update
            $original = $hosppital->getOriginal();
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

            // ✅ picture: replace file if uploaded (same style as SaeenFasad)
            if ($request->hasFile('picture')) {
                if ($hosppital->picture && str_starts_with($hosppital->picture, 'storage/')) {
                    $old = str_replace('storage/', '', $hosppital->picture);
                    Storage::disk('public')->delete($old);
                }

                $path = $request->file('picture')->store('uploads/hosppitals', 'public');
                $data['picture'] = "storage/{$path}";

                $changes['picture'] = [
                    'old' => $original['picture'] ?? null,
                    'new' => $data['picture'],
                ];
            } else {
                if (array_key_exists('picture', $data) && !is_string($data['picture'])) {
                    unset($data['picture']);
                }
            }

            $hosppital->update($data);
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

        // ✅ delete picture file (same style as SaeenFasad)
        if ($hosppital->picture && str_starts_with($hosppital->picture, 'storage/')) {
            $old = str_replace('storage/', '', $hosppital->picture);
            Storage::disk('public')->delete($old);
        }

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

        // (kept your original logic — if you want file cleanup here too, tell me)
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
