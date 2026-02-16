<?php

namespace App\Http\Controllers\fifthyformatsthree;

use App\Http\Controllers\Controller;
use App\Models\fifthyformatsthree\Hostel;
use App\Http\Resources\fifthyformatsthree\HostelResource;
use App\Http\Requests\fifthyformatsthree\StoreHostelRequest;
use App\Http\Requests\fifthyformatsthree\UpdateHostelRequest;
use App\Services\NotificationService; // 🔔 notifications
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class HostelController extends Controller
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
                    'د همدې معلوماتو سره (لیلیه نوم، ډول، د اوسېدونکو معلومات او ادرس) ریکارډ مخکې له مخکې موجود دی.',
                ],
            ],
        ], 422);
    }

    public function index(Request $request)
    {
        $perPage = (int) $request->input('perPage', 10);
        $search  = $request->input('search');

        $q = Hostel::query()
            ->with(['resource:id,name,code_number,phone_number'])
            ->latest();

        if ($search) {
            $q->where(function ($qq) use ($search) {
                $qq->where('hostel_name', 'like', "%{$search}%")
                   ->orWhere('responsible_name', 'like', "%{$search}%")
                   ->orWhere('province', 'like', "%{$search}%");
            });
        }

        return HostelResource::collection($q->paginate($perPage));
    }

    public function store(StoreHostelRequest $request)
    {
        $user = $request->user(); // 🔔 who did this
        $data = $request->validated();

        try {
            // ✅ picture: file OR string (same style as SaeenFasad)
            if ($request->hasFile('picture')) {
                $path = $request->file('picture')->store('uploads/hostels', 'public');
                $data['picture'] = "storage/{$path}";
            } else {
                if (array_key_exists('picture', $data) && !is_string($data['picture'])) {
                    unset($data['picture']);
                }
            }

            $item = Hostel::create($data)
                ->load(['resource:id,name,code_number,phone_number']);

            // 🔔 Notification: CREATED
            NotificationService::record(
                $user,
                module: 'لیلیه',
                action: 'created',
                title: 'نوې لیلیه ثبت شوه',
                message: 'نوم: ' . ($item->hostel_name ?? ''),
                routeName: 'hostels',              // ⚠️ set to your real route name
                routeParams: ['id' => $item->id],
                level: 'success',
                changes: null
            );

            return HostelResource::make($item);
        } catch (QueryException $e) {
            if ($this->isDuplicateKey($e)) {
                return $this->duplicateResponse();
            }
            throw $e;
        }
    }

    public function show(Hostel $hostel)
    {
        $hostel->load(['resource:id,name,code_number,phone_number']);
        return HostelResource::make($hostel);
    }

    public function update(UpdateHostelRequest $request, Hostel $hostel)
    {
        $user = $request->user(); // 🔔
        $data = $request->validated();

        try {
            // 🧮 build changes BEFORE update
            $original = $hostel->getOriginal();
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
                if ($hostel->picture && str_starts_with($hostel->picture, 'storage/')) {
                    $old = str_replace('storage/', '', $hostel->picture);
                    Storage::disk('public')->delete($old);
                }

                $path = $request->file('picture')->store('uploads/hostels', 'public');
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

            $hostel->update($data);
            $hostel->load(['resource:id,name,code_number,phone_number']);

            // 🔔 Notification: UPDATED
            NotificationService::record(
                $user,
                module: 'لیلیه',
                action: 'updated',
                title: 'لیلیه اصلاح شوه',
                message: 'نوم: ' . ($hostel->hostel_name ?? ''),
                routeName: 'hostels',              // ⚠️ set to your real route name
                routeParams: ['id' => $hostel->id],
                level: 'info',
                changes: !empty($changes) ? $changes : null
            );

            return HostelResource::make($hostel);
        } catch (QueryException $e) {
            if ($this->isDuplicateKey($e)) {
                return $this->duplicateResponse();
            }
            throw $e;
        }
    }

    public function destroy(Request $request, Hostel $hostel)
    {
        $user = $request->user(); // 🔔
        $name = $hostel->hostel_name;

        // ✅ delete picture file (same style as SaeenFasad)
        if ($hostel->picture && str_starts_with($hostel->picture, 'storage/')) {
            $old = str_replace('storage/', '', $hostel->picture);
            Storage::disk('public')->delete($old);
        }

        $hostel->delete();

        // 🔔 Notification: DELETED
        NotificationService::record(
            $user,
            module: 'لیلیه',
            action: 'deleted',
            title: 'لیلیه حذف شوه',
            message: 'نوم: ' . ($name ?? ''),
            routeName: null,
            routeParams: ['hostels'],
            level: 'warning',
            changes: null
        );

        return response()->noContent();
    }

    public function bulkDelete(Request $request)
    {
        $user = $request->user(); // 🔔
        $ids = (array) $request->input('ids', []);

        // (kept your original logic — if you want file cleanup here too, tell me)
        Hostel::destroy($ids);

        // 🔔 Notification: BULK DELETED
        NotificationService::record(
            $user,
            module: 'لیلیه',
            action: 'bulk_deleted',
            title: 'ډله‌ییز حذف ترسره شو',
            message: 'حذف شوي ریکارډونه: ' . count($ids),
            routeName: null,
            routeParams: ['hostels'],
            level: 'warning',
            changes: null
        );

        return response()->noContent();
    }
}
