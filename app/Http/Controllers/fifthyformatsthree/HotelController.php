<?php

namespace App\Http\Controllers\fifthyformatsthree;

use App\Http\Controllers\Controller;
use App\Models\fifthyformatsthree\Hotel;
use App\Http\Resources\fifthyformatsthree\HotelResource;
use App\Http\Requests\fifthyformatsthree\StoreHotelRequest;
use App\Http\Requests\fifthyformatsthree\UpdateHotelRequest;
use App\Services\NotificationService; // 🔔 notifications
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class HotelController extends Controller
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
                    'د همدې معلوماتو سره (هوټل نوم، ډول او ادرس) ریکارډ مخکې له مخکې موجود دی.',
                ],
            ],
        ], 422);
    }

    public function index(Request $request)
    {
        $perPage = (int) $request->input('perPage', 10);
        $search  = $request->input('search');

        $q = Hotel::query()
            ->with(['resource:id,name,code_number,phone_number'])
            ->latest();

        if ($search) {
            $q->where(function ($qq) use ($search) {
                $qq->where('hotel_name', 'like', "%{$search}%")
                   ->orWhere('responsible_name', 'like', "%{$search}%")
                   ->orWhere('province', 'like', "%{$search}%");
            });
        }

        return HotelResource::collection($q->paginate($perPage));
    }

    public function store(StoreHotelRequest $request)
    {
        $user = $request->user(); // 🔔 who did this
        $data = $request->validated();

        try {
            // ✅ picture: file OR string (same style as SaeenFasad)
            if ($request->hasFile('picture')) {
                $path = $request->file('picture')->store('uploads/hotels', 'public');
                $data['picture'] = "storage/{$path}";
            } else {
                if (array_key_exists('picture', $data) && !is_string($data['picture'])) {
                    unset($data['picture']);
                }
            }

            $hotel = Hotel::create($data)
                ->load(['resource:id,name,code_number,phone_number']);

            // 🔔 Notification: CREATED
            NotificationService::record(
                $user,
                module: 'هوټل',
                action: 'created',
                title: 'نوی هوټل ثبت شو',
                message: 'نوم: ' . ($hotel->hotel_name ?? ''),
                routeName: 'hotels',               // ⚠️ set to your real route name
                routeParams: ['id' => $hotel->id],
                level: 'success',
                changes: null
            );

            return HotelResource::make($hotel);
        } catch (QueryException $e) {
            if ($this->isDuplicateKey($e)) {
                return $this->duplicateResponse();
            }
            throw $e;
        }
    }

    public function show(Hotel $hotel)
    {
        $hotel->load(['resource:id,name,code_number,phone_number']);
        return HotelResource::make($hotel);
    }

    public function update(UpdateHotelRequest $request, Hotel $hotel)
    {
        $user = $request->user(); // 🔔
        $data = $request->validated();

        try {
            // 🧮 build changes BEFORE update
            $original = $hotel->getOriginal();
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
                if ($hotel->picture && str_starts_with($hotel->picture, 'storage/')) {
                    $old = str_replace('storage/', '', $hotel->picture);
                    Storage::disk('public')->delete($old);
                }

                $path = $request->file('picture')->store('uploads/hotels', 'public');
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

            $hotel->update($data);
            $hotel->load(['resource:id,name,code_number,phone_number']);

            // 🔔 Notification: UPDATED
            NotificationService::record(
                $user,
                module: 'هوټل',
                action: 'updated',
                title: 'هوټل اصلاح شو',
                message: 'نوم: ' . ($hotel->hotel_name ?? ''),
                routeName: 'hotels',               // ⚠️ set to your real route name
                routeParams: ['id' => $hotel->id],
                level: 'info',
                changes: !empty($changes) ? $changes : null
            );

            return HotelResource::make($hotel);
        } catch (QueryException $e) {
            if ($this->isDuplicateKey($e)) {
                return $this->duplicateResponse();
            }
            throw $e;
        }
    }

    public function destroy(Request $request, Hotel $hotel)
    {
        $user = $request->user(); // 🔔
        $name = $hotel->hotel_name;

        // ✅ delete picture file (same style as SaeenFasad)
        if ($hotel->picture && str_starts_with($hotel->picture, 'storage/')) {
            $old = str_replace('storage/', '', $hotel->picture);
            Storage::disk('public')->delete($old);
        }

        $hotel->delete();

        // 🔔 Notification: DELETED
        NotificationService::record(
            $user,
            module: 'هوټل',
            action: 'deleted',
            title: 'هوټل حذف شو',
            message: 'نوم: ' . ($name ?? ''),
            routeName: null,
            routeParams: ['hotels'],
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
        Hotel::destroy($ids);

        // 🔔 Notification: BULK DELETED
        NotificationService::record(
            $user,
            module: 'هوټل',
            action: 'bulk_deleted',
            title: 'ډله‌ییز حذف ترسره شو',
            message: 'حذف شوي ریکارډونه: ' . count($ids),
            routeName: null,
            routeParams: ['hotels'],
            level: 'warning',
            changes: null
        );

        return response()->noContent();
    }
}
