<?php

namespace App\Http\Controllers\fifthyformatsthree;

use App\Http\Controllers\Controller;
use App\Models\fifthyformatsthree\Madrasa;
use App\Http\Resources\fifthyformatsthree\MadrasaResource;
use App\Http\Requests\fifthyformatsthree\StoreMadrasaRequest;
use App\Http\Requests\fifthyformatsthree\UpdateMadrasaRequest;
use App\Services\NotificationService; // 🔔 notifications
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MadrasaController extends Controller
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
                    'د همدې معلوماتو سره (نوم، د مسئول نوم او ادرس) ریکارډ مخکې له مخکې موجود دی.',
                ],
            ],
        ], 422);
    }

    public function index(Request $request)
    {
        $perPage = (int) $request->input('perPage', 10);
        $search  = $request->input('search');

        $q = Madrasa::query()
            ->with(['resource:id,name,code_number,phone_number'])
            ->latest();

        if ($search) {
            $q->where(function ($qq) use ($search) {
                $qq->where('name', 'like', "%{$search}%")
                   ->orWhere('manager_name', 'like', "%{$search}%")
                   ->orWhere('main_province', 'like', "%{$search}%")
                   ->orWhere('main_district', 'like', "%{$search}%");
            });
        }

        return MadrasaResource::collection($q->paginate($perPage));
    }

    public function store(StoreMadrasaRequest $request)
    {
        $user = $request->user(); // 🔔
        $data = $request->validated();

        try {
            // ✅ picture: file OR string (same style as SaeenFasad)
            if ($request->hasFile('picture')) {
                $path = $request->file('picture')->store('uploads/madrasas', 'public');
                $data['picture'] = "storage/{$path}";
            } else {
                if (array_key_exists('picture', $data) && !is_string($data['picture'])) {
                    unset($data['picture']);
                }
            }

            $item = Madrasa::create($data)
                ->load(['resource:id,name,code_number,phone_number']);

            // 🔔 Notification: CREATED
            NotificationService::record(
                $user,
                module: 'مدرسې',
                action: 'created',
                title: 'نوې مدرسه ثبت شوه',
                message: 'نوم: ' . ($item->name ?? ''),
                routeName: 'madrasas',        // ⚠️ set to your real route name
                routeParams: ['id' => $item->id],
                level: 'success',
                changes: null
            );

            return MadrasaResource::make($item);
        } catch (QueryException $e) {
            if ($this->isDuplicateKey($e)) {
                return $this->duplicateResponse();
            }
            throw $e;
        }
    }

    public function show(Madrasa $madrasa)
    {
        $madrasa->load(['resource:id,name,code_number,phone_number']);
        return MadrasaResource::make($madrasa);
    }

    public function update(UpdateMadrasaRequest $request, Madrasa $madrasa)
    {
        $user = $request->user(); // 🔔
        $data = $request->validated();

        try {
            $original = $madrasa->getOriginal();
            $changes  = [];

            foreach ($data as $field => $newValue) {
                if (array_key_exists($field, $original)) {
                    $oldValue = $original[$field];
                    if ($oldValue != $newValue) {
                        $changes[$field] = ['old' => $oldValue, 'new' => $newValue];
                    }
                }
            }

            // ✅ picture: replace file if uploaded (same style as SaeenFasad)
            if ($request->hasFile('picture')) {
                if ($madrasa->picture && str_starts_with($madrasa->picture, 'storage/')) {
                    $old = str_replace('storage/', '', $madrasa->picture);
                    Storage::disk('public')->delete($old);
                }

                $path = $request->file('picture')->store('uploads/madrasas', 'public');
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

            $madrasa->update($data);
            $madrasa->load(['resource:id,name,code_number,phone_number']);

            // 🔔 Notification: UPDATED
            NotificationService::record(
                $user,
                module: 'مدرسې',
                action: 'updated',
                title: 'مدرسه اصلاح شوه',
                message: 'نوم: ' . ($madrasa->name ?? ''),
                routeName: 'madrasas',        // ⚠️ set to your real route name
                routeParams: ['id' => $madrasa->id],
                level: 'info',
                changes: !empty($changes) ? $changes : null
            );

            return MadrasaResource::make($madrasa);
        } catch (QueryException $e) {
            if ($this->isDuplicateKey($e)) {
                return $this->duplicateResponse();
            }
            throw $e;
        }
    }

    public function destroy(Request $request, Madrasa $madrasa)
    {
        $user = $request->user(); // 🔔
        $name = $madrasa->name;

        // ✅ delete picture file (same style as SaeenFasad)
        if ($madrasa->picture && str_starts_with($madrasa->picture, 'storage/')) {
            $old = str_replace('storage/', '', $madrasa->picture);
            Storage::disk('public')->delete($old);
        }

        $madrasa->delete();

        // 🔔 Notification: DELETED
        NotificationService::record(
            $user,
            module: 'مدرسې',
            action: 'deleted',
            title: 'مدرسه حذف شوه',
            message: 'نوم: ' . ($name ?? ''),
            routeName: null,
            routeParams: ['madrasas'],
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
        Madrasa::destroy($ids);

        // 🔔 Notification: BULK DELETED
        NotificationService::record(
            $user,
            module: 'مدرسې',
            action: 'bulk_deleted',
            title: 'ډله‌ییز حذف ترسره شو',
            message: 'حذف شوي ریکارډونه: ' . count($ids),
            routeName: null,
            routeParams: ['madrasas'],
            level: 'warning',
            changes: null
        );

        return response()->noContent();
    }
}
