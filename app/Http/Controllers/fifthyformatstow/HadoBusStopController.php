<?php

namespace App\Http\Controllers\fifthyformatstow;

use App\Http\Controllers\Controller;
use App\Http\Requests\fifthyformatstow\StoreHadoBusStopRequest;
use App\Http\Requests\fifthyformatstow\UpdateHadoBusStopRequest;
use App\Http\Resources\fifthyformatstow\HadoBusStopResource;
use App\Models\fifthyformatstow\HadoBusStop;
use App\Services\NotificationService; // 🔔
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class HadoBusStopController extends Controller
{
    /* =======================
     | Helpers
     ======================= */
    private function duplicateErrorPashto(): array
    {
        return [
            'message' => 'دا تمځای مخکې ثبت شوی دی. مهرباني وکړئ تکراري معلومات مه داخلوی.',
            'errors'  => [
                'duplicate' => [
                    'دا د بس تمځای مخکې ثبت شوی دی (نوم، ډول، مسؤل او پته یو شان دي).',
                ],
            ],
        ];
    }

    private function isDuplicateKey(QueryException $e): bool
    {
        return (int) ($e->errorInfo[1] ?? 0) === 1062;
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

    /* =======================
     | GET /hado-bus-stops
     ======================= */
    public function index(Request $request)
    {
        $perPage = (int) $request->input('perPage', 10);
        $search  = $request->input('search');

        $q = HadoBusStop::query()
            ->with(['resource:id,name,code_number,phone_number'])
            ->latest();

        if ($search) {
            $q->where(function ($qq) use ($search) {
                $qq->where('name', 'like', "%{$search}%")
                   ->orWhere('hada_type', 'like', "%{$search}%")
                   ->orWhere('responsible_name', 'like', "%{$search}%")
                   ->orWhere('responsible_phone', 'like', "%{$search}%")
                   ->orWhere('province', 'like', "%{$search}%")
                   ->orWhere('district', 'like', "%{$search}%");
            });
        }

        return HadoBusStopResource::collection($q->paginate($perPage));
    }

    /* =======================
     | POST /hado-bus-stops
     | Accepts:
     | - picture as FILE or STRING
     ======================= */
    public function store(StoreHadoBusStopRequest $request)
    {
        $user = $request->user(); // 🔔
        $data = $request->validated();

        try {
            return DB::transaction(function () use ($request, $data, $user) {

                // ✅ picture: file OR string (same style as SaeenFasad)
                if ($request->hasFile('picture')) {
                    $path = $request->file('picture')->store('uploads/hado-bus-stops', 'public');
                    $data['picture'] = "storage/{$path}";
                } else {
                    if (array_key_exists('picture', $data) && !is_string($data['picture'])) {
                        unset($data['picture']);
                    }
                }

                $stop = HadoBusStop::create($data);

                $stop->load(['resource:id,name,code_number,phone_number']);

                NotificationService::record(
                    $user,
                    module: 'تمځای‌های هادو',
                    action: 'created',
                    title: 'تمځای جدید ثبت شد',
                    message: 'نام: ' . ($stop->name ?? ''),
                    routeName: 'hado-bus-stops', // ⚠️ adjust if needed
                    routeParams: ['id' => $stop->id],
                    level: 'success',
                    changes: null
                );

                return (new HadoBusStopResource($stop))
                    ->additional(['message' => 'Created'])
                    ->response()
                    ->setStatusCode(201);
            });
        } catch (QueryException $e) {
            if ($this->isDuplicateKey($e)) {
                return response()->json($this->duplicateErrorPashto(), 422);
            }
            throw $e;
        }
    }

    /* =======================
     | GET /hado-bus-stops/{id}
     ======================= */
    public function show(HadoBusStop $hadoBusStop)
    {
        $hadoBusStop->load(['resource:id,name,code_number,phone_number']);
        return HadoBusStopResource::make($hadoBusStop);
    }

    /* =======================
     | PUT /hado-bus-stops/{id}
     | Accepts:
     | - picture as FILE or STRING (replaces old file if new uploaded)
     ======================= */
    public function update(UpdateHadoBusStopRequest $request, HadoBusStop $hadoBusStop)
    {
        $user = $request->user(); // 🔔
        $validated = $request->validated();

        try {
            return DB::transaction(function () use ($request, $validated, $hadoBusStop, $user) {

                // 🧮 detect changes
                $original = $hadoBusStop->getOriginal();
                $changes  = [];

                foreach ($validated as $field => $newValue) {
                    if (array_key_exists($field, $original) && $original[$field] != $newValue) {
                        $changes[$field] = [
                            'old' => $original[$field],
                            'new' => $newValue,
                        ];
                    }
                }

                // ✅ picture replace (same style as SaeenFasad)
                if ($request->hasFile('picture')) {
                    $this->deletePictureIfStored($hadoBusStop->picture);

                    $path = $request->file('picture')->store('uploads/hado-bus-stops', 'public');
                    $validated['picture'] = "storage/{$path}";

                    $changes['picture'] = [
                        'old' => $original['picture'] ?? null,
                        'new' => $validated['picture'],
                    ];
                } else {
                    if (array_key_exists('picture', $validated) && !is_string($validated['picture'])) {
                        unset($validated['picture']);
                    }
                }

                $hadoBusStop->update($validated);
                $hadoBusStop->load(['resource:id,name,code_number,phone_number']);

                NotificationService::record(
                    $user,
                    module: 'تمځای‌های هادو',
                    action: 'updated',
                    title: 'تمځای اصلاح شد',
                    message: 'نام: ' . ($hadoBusStop->name ?? ''),
                    routeName: 'hado-bus-stops',
                    routeParams: ['id' => $hadoBusStop->id],
                    level: 'info',
                    changes: !empty($changes) ? $changes : null
                );

                return HadoBusStopResource::make($hadoBusStop);
            });
        } catch (QueryException $e) {
            if ($this->isDuplicateKey($e)) {
                return response()->json($this->duplicateErrorPashto(), 422);
            }
            throw $e;
        }
    }

    /* =======================
     | DELETE /hado-bus-stops/{id}
     ======================= */
    public function destroy(Request $request, HadoBusStop $hadoBusStop)
    {
        $user = $request->user(); // 🔔

        return DB::transaction(function () use ($hadoBusStop, $user) {
            $name = $hadoBusStop->name;

            // ✅ delete picture file
            $this->deletePictureIfStored($hadoBusStop->picture);

            $hadoBusStop->delete();

            NotificationService::record(
                $user,
                module: 'تمځای‌های هادو',
                action: 'deleted',
                title: 'تمځای حذف شد',
                message: 'نام: ' . ($name ?? ''),
                routeName: null,
                routeParams: ['hado-bus-stops'],
                level: 'warning',
                changes: null
            );

            return response()->noContent();
        });
    }

    /* =======================
     | POST /hado-bus-stops/bulk-delete
     ======================= */
    public function bulkDelete(Request $request)
    {
        $user = $request->user(); // 🔔

        $ids = $request->input('ids', []);
        $ids = is_array($ids) ? array_values(array_filter($ids)) : [];

        return DB::transaction(function () use ($ids, $user) {
            if (empty($ids)) {
                return response()->noContent();
            }

            // ✅ delete pictures first (so files don't remain)
            $rows = HadoBusStop::whereIn('id', $ids)->get(['id', 'picture']);
            foreach ($rows as $row) {
                $this->deletePictureIfStored($row->picture);
            }

            HadoBusStop::whereIn('id', $ids)->delete();

            NotificationService::record(
                $user,
                module: 'تمځای‌های هادو',
                action: 'deleted',
                title: 'تمځای‌ها به‌صورت گروهی حذف شد',
                message: 'تعداد: ' . count($ids),
                routeName: null,
                routeParams: ['hado-bus-stops'],
                level: 'warning',
                changes: null
            );

            return response()->noContent();
        });
    }
}
