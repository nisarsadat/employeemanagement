<?php

namespace App\Http\Controllers\fifthyformatsthree;

use App\Http\Controllers\Controller;
use App\Models\fifthyformatsthree\OldJahadi;
use App\Http\Resources\fifthyformatsthree\OldJahadiResource;
use App\Http\Requests\fifthyformatsthree\StoreOldJahadiRequest;
use App\Http\Requests\fifthyformatsthree\UpdateOldJahadiRequest;
use App\Services\NotificationService; // 🔔 notifications
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class OldJahadiController extends Controller
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

    // ✅ picture upload helper (same idea as other modules)
    private function storePicture(?\Illuminate\Http\UploadedFile $file): ?string
    {
        if (!$file) return null;

        $ext = strtolower($file->getClientOriginalExtension() ?: 'jpg');
        $name = 'oldjahadi_' . now()->format('Ymd_His') . '_' . Str::random(10) . '.' . $ext;

        // public disk => storage/app/public/oldjahadis/...
        return $file->storeAs('oldjahadis', $name, 'public');
    }

    private function deletePictureIfExists(?string $path): void
    {
        if (!$path) return;

        $p = $path;
        if (str_starts_with($p, 'storage/')) {
            $p = substr($p, strlen('storage/'));
        }

        if (Storage::disk('public')->exists($p)) {
            Storage::disk('public')->delete($p);
        }
    }

    public function index(Request $request)
    {
        $perPage = (int) $request->input('perPage', 10);
        $search  = $request->input('search');

        $q = OldJahadi::query()
            ->with(['resource:id,name,code_number,phone_number'])
            ->latest();

        if ($search) {
            $q->where(function ($qq) use ($search) {
                $qq->where('name', 'like', "%{$search}%")
                   ->orWhere('father_name', 'like', "%{$search}%")
                   ->orWhere('now_province', 'like', "%{$search}%")
                   ->orWhere('tanzim', 'like', "%{$search}%");
            });
        }

        return OldJahadiResource::collection($q->paginate($perPage));
    }

    public function store(StoreOldJahadiRequest $request)
    {
        $user = $request->user(); // 🔔

        try {
            $data = $request->validated();

            // ✅ picture
            if ($request->hasFile('picture')) {
                $data['picture'] = $this->storePicture($request->file('picture'));
            }

            $item = OldJahadi::create($data)
                ->load(['resource:id,name,code_number,phone_number']);

            // 🔔 Notification: CREATED
            NotificationService::record(
                $user,
                module: 'قدیم جهادیان',
                action: 'created',
                title: 'نوی قدیم جهادی ثبت شو',
                message: 'نوم: ' . ($item->name ?? ''),
                routeName: 'old-jahadis',
                routeParams: ['id' => $item->id],
                level: 'success',
                changes: null
            );

            return OldJahadiResource::make($item);
        } catch (QueryException $e) {
            if ($this->isDuplicateKey($e)) {
                return $this->duplicateResponse();
            }
            throw $e;
        }
    }

    public function show(OldJahadi $oldJahadi)
    {
        $oldJahadi->load(['resource:id,name,code_number,phone_number']);
        return OldJahadiResource::make($oldJahadi);
    }

    public function update(UpdateOldJahadiRequest $request, OldJahadi $oldJahadi)
    {
        $user = $request->user(); // 🔔

        try {
            $original  = $oldJahadi->getOriginal();
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

            // ✅ picture replace
            if ($request->hasFile('picture')) {
                $this->deletePictureIfExists($oldJahadi->picture);
                $validated['picture'] = $this->storePicture($request->file('picture'));
                $changes['picture'] = ['old' => $oldJahadi->picture, 'new' => $validated['picture']];
            }

            $oldJahadi->update($validated);
            $oldJahadi->load(['resource:id,name,code_number,phone_number']);

            // 🔔 Notification: UPDATED
            NotificationService::record(
                $user,
                module: 'قدیم جهادیان',
                action: 'updated',
                title: 'قدیم جهادی اصلاح شو',
                message: 'نوم: ' . ($oldJahadi->name ?? ''),
                routeName: 'old-jahadis',
                routeParams: ['id' => $oldJahadi->id],
                level: 'info',
                changes: !empty($changes) ? $changes : null
            );

            return OldJahadiResource::make($oldJahadi);
        } catch (QueryException $e) {
            if ($this->isDuplicateKey($e)) {
                return $this->duplicateResponse();
            }
            throw $e;
        }
    }

    public function destroy(Request $request, OldJahadi $oldJahadi)
    {
        $user = $request->user(); // 🔔

        $name = $oldJahadi->name;

        // ✅ delete picture file
        $this->deletePictureIfExists($oldJahadi->picture);

        $oldJahadi->delete();

        // 🔔 Notification: DELETED
        NotificationService::record(
            $user,
            module: 'قدیم جهادیان',
            action: 'deleted',
            title: 'قدیم جهادی حذف شو',
            message: 'نوم: ' . ($name ?? ''),
            routeName: null,
            routeParams: ['old-jahadis'],
            level: 'warning',
            changes: null
        );

        return response()->noContent();
    }

    public function bulkDelete(Request $request)
    {
        $user = $request->user(); // 🔔

        $ids = (array) $request->input('ids', []);
        if (!empty($ids)) {
            $items = OldJahadi::query()->whereIn('id', $ids)->get(['id', 'picture']);
            foreach ($items as $it) {
                $this->deletePictureIfExists($it->picture);
            }
        }

        OldJahadi::destroy($ids);

        // 🔔 Notification: BULK DELETED
        NotificationService::record(
            $user,
            module: 'قدیم جهادیان',
            action: 'bulk_deleted',
            title: 'ډله‌ییز حذف ترسره شو',
            message: 'حذف شوي ریکارډونه: ' . count($ids),
            routeName: null,
            routeParams: ['old-jahadis'],
            level: 'warning',
            changes: null
        );

        return response()->noContent();
    }
}
