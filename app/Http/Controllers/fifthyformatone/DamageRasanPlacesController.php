<?php

namespace App\Http\Controllers\fifthyformatone;

use App\Http\Controllers\Controller;
use App\Http\Requests\fifthyformatone\StoreDamageRasanPlacesRequest;
use App\Http\Requests\fifthyformatone\UpdateDamageRasanPlacesRequest;
use App\Http\Resources\fifthyformatone\DamageRasanPlacesResource;
use App\Models\fifthyformatone\DamageRasanPlaces;
use App\Services\NotificationService; // 🔔 notifications
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DamageRasanPlacesController extends Controller
{
    /** GET /damage-rasan-places */
    public function index(Request $request)
    {
        $perPage = (int) $request->input('perPage', 10);
        $search  = $request->input('search');

        $q = DamageRasanPlaces::query()
            ->with(['resource:id,name,code_number,phone_number'])
            ->latest();

        if ($search) {
            $q->where(function ($w) use ($search) {
                $w->where('province', 'like', "%{$search}%")
                  ->orWhere('district', 'like', "%{$search}%")
                  ->orWhere('village', 'like', "%{$search}%")
                  ->orWhere('damage_place', 'like', "%{$search}%");
            });
        }

        return DamageRasanPlacesResource::collection($q->paginate($perPage));
    }

    // ✅ SaeenFasad-style picture delete helper (handles "storage/..." too)
    private function deletePictureIfStored(?string $picture): void
    {
        if (!$picture) return;

        $path = $picture;
        if (str_starts_with($path, 'storage/')) {
            $path = str_replace('storage/', '', $path);
        }

        Storage::disk('public')->delete($path);
    }

    /** POST /damage-rasan-places
     * Accepts:
     * - picture as FILE or STRING
     */
    public function store(StoreDamageRasanPlacesRequest $request)
    {
        $user = $request->user(); // 🔔
        $data = $request->validated();

        return DB::transaction(function () use ($request, $data, $user) {

            // ✅ picture: file OR string (same style as SaeenFasad)
            if ($request->hasFile('picture')) {
                $path = $request->file('picture')->store('uploads/damage-rasan-places', 'public');
                $data['picture'] = "storage/{$path}";
            } else {
                if (array_key_exists('picture', $data) && !is_string($data['picture'])) {
                    unset($data['picture']);
                }
            }

            $record = DamageRasanPlaces::create($data);
            $record->load(['resource:id,name,code_number,phone_number']);

            // 🔔 Notification: CREATED
            NotificationService::record(
                $user,
                module: 'محلات آسیب‌رسان',
                action: 'created',
                title: 'ریکارډ ثبت شو',
                message: 'ولایت: ' . ($record->province ?? '') . ' | ځای: ' . ($record->damage_place ?? ''),
                routeName: 'damage-rasan-places',
                routeParams: ['id' => $record->id],
                level: 'success',
                changes: null
            );

            return (new DamageRasanPlacesResource($record))
                ->additional(['message' => 'Created'])
                ->response()
                ->setStatusCode(201);
        });
    }

    /** GET /damage-rasan-places/{damage_rasan_place} */
    public function show(DamageRasanPlaces $damage_rasan_place)
    {
        $damage_rasan_place->load(['resource:id,name,code_number,phone_number']);
        return DamageRasanPlacesResource::make($damage_rasan_place);
    }

    /** PUT /damage-rasan-places/{damage_rasan_place}
     * Accepts:
     * - picture as FILE or STRING (replaces old file if new uploaded)
     */
    public function update(UpdateDamageRasanPlacesRequest $request, DamageRasanPlaces $damage_rasan_place)
    {
        $user = $request->user(); // 🔔
        $data = $request->validated();

        return DB::transaction(function () use ($request, $data, $damage_rasan_place, $user) {

            // 🧮 changes BEFORE update
            $original = $damage_rasan_place->getOriginal();
            $changes  = [];

            foreach ($data as $field => $newValue) {
                if (array_key_exists($field, $original) && $original[$field] != $newValue) {
                    $changes[$field] = ['old' => $original[$field], 'new' => $newValue];
                }
            }

            // ✅ picture replace (same style as SaeenFasad)
            if ($request->hasFile('picture')) {
                $this->deletePictureIfStored($damage_rasan_place->picture);

                $path = $request->file('picture')->store('uploads/damage-rasan-places', 'public');
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

            $damage_rasan_place->update($data);
            $damage_rasan_place->load(['resource:id,name,code_number,phone_number']);

            // 🔔 Notification: UPDATED
            NotificationService::record(
                $user,
                module: 'محلات آسیب‌رسان',
                action: 'updated',
                title: 'ریکارډ اصلاح شو',
                message: 'ولایت: ' . ($damage_rasan_place->province ?? '') . ' | ځای: ' . ($damage_rasan_place->damage_place ?? ''),
                routeName: 'damage-rasan-places',
                routeParams: ['id' => $damage_rasan_place->id],
                level: 'info',
                changes: !empty($changes) ? $changes : null
            );

            return DamageRasanPlacesResource::make($damage_rasan_place);
        });
    }

    /** DELETE /damage-rasan-places/{damage_rasan_place} */
    public function destroy(Request $request, DamageRasanPlaces $damage_rasan_place)
    {
        $user = $request->user(); // 🔔

        return DB::transaction(function () use ($damage_rasan_place, $user) {

            // ✅ delete picture file (same style as SaeenFasad)
            $this->deletePictureIfStored($damage_rasan_place->picture);

            $damage_rasan_place->delete();

            // 🔔 Notification: DELETED
            NotificationService::record(
                $user,
                module: 'محلات آسیب‌رسان',
                action: 'deleted',
                title: 'ریکارډ حذف شو',
                message: 'ID: ' . ($damage_rasan_place->id ?? ''),
                routeName: null,
                routeParams: ['damage-rasan-places'],
                level: 'warning',
                changes: null
            );

            return response()->noContent();
        });
    }

    /** POST /damage-rasan-places/bulk-delete  { "ids": [1,2,3] } */
    public function bulkDelete(Request $request)
    {
        $user = $request->user(); // 🔔

        $ids = $request->input('ids', []);
        $ids = is_array($ids) ? array_values(array_filter($ids)) : [];

        return DB::transaction(function () use ($ids, $user) {
            if (empty($ids)) {
                return response()->noContent();
            }

            // ✅ delete pictures first
            $rows = DamageRasanPlaces::whereIn('id', $ids)->get(['id', 'picture']);
            foreach ($rows as $row) {
                $this->deletePictureIfStored($row->picture);
            }

            DamageRasanPlaces::whereIn('id', $ids)->delete();

            // 🔔 Notification: BULK DELETED
            NotificationService::record(
                $user,
                module: 'محلات آسیب‌رسان',
                action: 'deleted',
                title: 'حذف گروهی انجام شد',
                message: 'تعداد: ' . count($ids),
                routeName: null,
                routeParams: ['damage-rasan-places'],
                level: 'warning',
                changes: null
            );

            return response()->noContent();
        });
    }
}
