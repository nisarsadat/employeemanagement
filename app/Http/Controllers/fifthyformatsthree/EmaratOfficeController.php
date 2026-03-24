<?php

namespace App\Http\Controllers\fifthyformatsthree;

use App\Http\Controllers\Controller;
use App\Models\fifthyformatsthree\EmaratOffice;
use App\Http\Resources\fifthyformatsthree\EmaratOfficeResource;
use App\Http\Requests\fifthyformatsthree\StoreEmaratOfficeRequest;
use App\Http\Requests\fifthyformatsthree\UpdateEmaratOfficeRequest;
use App\Services\NotificationService; // 🔔 notifications
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class EmaratOfficeController extends Controller
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
                    'د همدې معلوماتو سره (ولایت/زون/ولسوالۍ/کلی او دقیق ادرس) ریکارډ مخکې له مخکې موجود دی.',
                ],
            ],
        ], 422);
    }

    public function index(Request $request)
    {
        $perPage = (int) $request->input('perPage', 10);
        $search  = $request->input('search');

        $q = EmaratOffice::query()
            ->with(['resource:id,name,code_number,phone_number'])
            ->latest();

        if ($search) {
            $q->where(function ($qq) use ($search) {
                $qq->where('province', 'like', "%{$search}%")
                   ->orWhere('district', 'like', "%{$search}%")
                   ->orWhere('responsible_office', 'like', "%{$search}%");
            });
        }

        return EmaratOfficeResource::collection($q->paginate($perPage));
    }

    public function store(StoreEmaratOfficeRequest $request)
    {
        $user = $request->user(); // 🔔 who did this
        $data = $request->validated();

        try {
            // ✅ picture: file OR string (same style as SaeenFasad)
            if ($request->hasFile('picture')) {
                $path = $request->file('picture')->store('uploads/emarat-offices', 'public');
                $data['picture'] = "storage/{$path}";
            } else {
                if (array_key_exists('picture', $data) && !is_string($data['picture'])) {
                    unset($data['picture']);
                }
            }

            $item = EmaratOffice::create($data)
                ->load(['resource:id,name,code_number,phone_number']);

            // 🔔 Notification: CREATED
            NotificationService::record(
                $user,
                module: 'امارتي کورونه او میلمستونونه',
                action: 'created',
                title: 'نوی امارتي کورونه او میلمستونونه ثبت شو',
                message: 'ولایت: ' . ($item->province ?? ''),
                routeName: 'emarat-offices',       // ⚠️ set to your real route name
                routeParams: ['id' => $item->id],
                level: 'success',
                changes: null
            );

            return EmaratOfficeResource::make($item);
        } catch (QueryException $e) {
            if ($this->isDuplicateKey($e)) {
                return $this->duplicateResponse();
            }
            throw $e;
        }
    }

    public function show(EmaratOffice $emaratOffice)
    {
        $emaratOffice->load(['resource:id,name,code_number,phone_number']);
        return EmaratOfficeResource::make($emaratOffice);
    }

    public function update(UpdateEmaratOfficeRequest $request, EmaratOffice $emaratOffice)
    {
        $user = $request->user(); // 🔔
        $data = $request->validated();

        try {
            // 🧮 build changes BEFORE update
            $original = $emaratOffice->getOriginal();
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
                if ($emaratOffice->picture && str_starts_with($emaratOffice->picture, 'storage/')) {
                    $old = str_replace('storage/', '', $emaratOffice->picture);
                    Storage::disk('public')->delete($old);
                }

                $path = $request->file('picture')->store('uploads/emarat-offices', 'public');
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

            $emaratOffice->update($data);
            $emaratOffice->load(['resource:id,name,code_number,phone_number']);

            // 🔔 Notification: UPDATED
            NotificationService::record(
                $user,
                module: 'امارتي کورونه او میلمستونونه',
                action: 'updated',
                title: 'امارتي کورونه او میلمستونونه اصلاح شو',
                message: 'ولایت: ' . ($emaratOffice->province ?? ''),
                routeName: 'emarat-offices',       // ⚠️ set to your real route name
                routeParams: ['id' => $emaratOffice->id],
                level: 'info',
                changes: !empty($changes) ? $changes : null
            );

            return EmaratOfficeResource::make($emaratOffice);
        } catch (QueryException $e) {
            if ($this->isDuplicateKey($e)) {
                return $this->duplicateResponse();
            }
            throw $e;
        }
    }

    public function destroy(Request $request, EmaratOffice $emaratOffice)
    {
        $user = $request->user(); // 🔔
        $province = $emaratOffice->province;

        // ✅ delete picture file (same style as SaeenFasad)
        if ($emaratOffice->picture && str_starts_with($emaratOffice->picture, 'storage/')) {
            $old = str_replace('storage/', '', $emaratOffice->picture);
            Storage::disk('public')->delete($old);
        }

        $emaratOffice->delete();

        // 🔔 Notification: DELETED
        NotificationService::record(
            $user,
            module: 'امارتي کورونه او میلمستونونه',
            action: 'deleted',
            title: 'امارتي کورونه او میلمستونونه حذف شو',
            message: 'ولایت: ' . ($province ?? ''),
            routeName: null,
            routeParams: ['emarat-offices'],
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
        EmaratOffice::destroy($ids);

        // 🔔 Notification: BULK DELETED
        NotificationService::record(
            $user,
            module: 'امارتي کورونه او میلمستونونه',
            action: 'bulk_deleted',
            title: 'ډله‌ییز حذف ترسره شو',
            message: 'حذف شوي ریکارډونه: ' . count($ids),
            routeName: null,
            routeParams: ['emarat-offices'],
            level: 'warning',
            changes: null
        );

        return response()->noContent();
    }
}
