<?php

namespace App\Http\Controllers\fifthyformatone;

use App\Http\Controllers\Controller;
use App\Http\Requests\fifthyformatone\StoreLostMujremeenRequest;
use App\Http\Requests\fifthyformatone\UpdateLostMujremeenRequest;
use App\Http\Resources\fifthyformatone\LostMujremeenResource;
use App\Models\fifthyformatone\LostMujremeen;
use App\Services\NotificationService; // 🔔 notifications
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class LostMujremeenController extends Controller
{
    /**
     * Pashto duplicate error message (unique constraint)
     */
    private function duplicateErrorPashto(): array
    {
        return [
            'message' => 'دا ریکارډ مخکې ثبت شوی دی. مهرباني وکړئ تکراري معلومات مه داخلوی.',
            'errors'  => [
                'duplicate' => [
                    'دا کس مخکې ثبت شوی دی (همدا نوم، د پلار نوم او اصلي پته).',
                ],
            ],
        ];
    }

    private function isDuplicateKeyException(QueryException $e): bool
    {
        // MySQL duplicate entry = 1062
        return (int) ($e->errorInfo[1] ?? 0) === 1062;
    }

    private function deletePictureIfStored(?string $picture): void
    {
        if ($picture && str_starts_with($picture, 'storage/')) {
            $diskPath = str_replace('storage/', '', $picture);
            Storage::disk('public')->delete($diskPath);
        }
    }

    public function index(Request $request)
    {
        $perPage = (int) $request->get('perPage', 15);
        $search  = $request->get('search');
        $rid     = $request->get('resource_id');

        $sortBy  = $request->get('sort_by', 'created_at');
        $sortDir = in_array($request->get('sort_dir'), ['asc', 'desc'], true) ? $request->get('sort_dir') : 'desc';

        $q = LostMujremeen::query()->with([
            'resource' => fn ($qr) => $qr->select('id', 'name', 'code_number', 'phone_number'),
        ]);

        if ($search) {
            $q->where(function ($qq) use ($search) {
                $qq->where('name', 'like', "%{$search}%")
                    ->orWhere('father_name', 'like', "%{$search}%")
                    ->orWhere('now_province', 'like', "%{$search}%")
                    ->orWhere('jorm', 'like', "%{$search}%");
            });
        }

        if ($rid) {
            $q->where('resource_id', $rid);
        }

        $q->orderBy($sortBy, $sortDir);

        return LostMujremeenResource::collection($q->paginate($perPage));
    }

    public function show(LostMujremeen $lostMujremeen)
    {
        $lostMujremeen->load([
            'resource' => fn ($qr) => $qr->select('id', 'name', 'code_number', 'phone_number'),
        ]);

        return new LostMujremeenResource($lostMujremeen);
    }

    public function store(StoreLostMujremeenRequest $request)
    {
        $user = $request->user(); // 🔔

        try {
            return DB::transaction(function () use ($request, $user) {
                $data = $request->validated();

                // 📷 picture upload
                if ($request->hasFile('picture')) {
                    $path = $request->file('picture')->store('uploads/lost-mujremeens', 'public');
                    $data['picture'] = "storage/{$path}";
                } else {
                    if (array_key_exists('picture', $data) && !is_string($data['picture'])) {
                        unset($data['picture']);
                    }
                }

                $item = LostMujremeen::create($data);

                $item->load([
                    'resource' => fn ($qr) => $qr->select('id', 'name', 'code_number', 'phone_number'),
                ]);

                // 🔔 Notification: CREATED
                NotificationService::record(
                    $user,
                    module: 'مجرمین فراری',
                    action: 'created',
                    title: 'مجرم فراری جدید ثبت شد',
                    message: 'نام: ' . ($item->name ?? ''),
                    routeName: 'lost-mujremeens',      // ⚠️ set your real route name
                    routeParams: ['id' => $item->id],
                    level: 'success',
                    changes: null
                );

                return (new LostMujremeenResource($item))
                    ->additional(['message' => 'Created'])
                    ->response()
                    ->setStatusCode(201);
            });
        } catch (QueryException $e) {
            if ($this->isDuplicateKeyException($e)) {
                return response()->json($this->duplicateErrorPashto(), 422);
            }
            throw $e;
        }
    }

    public function update(UpdateLostMujremeenRequest $request, LostMujremeen $lostMujremeen)
    {
        $user = $request->user(); // 🔔

        try {
            return DB::transaction(function () use ($request, $lostMujremeen, $user) {
                $validated = $request->validated();

                // 🧮 changes BEFORE update
                $original = $lostMujremeen->getOriginal();
                $changes  = [];

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

                // 📷 picture update
                if ($request->hasFile('picture')) {
                    $this->deletePictureIfStored($lostMujremeen->picture);

                    $path = $request->file('picture')->store('uploads/lost-mujremeens', 'public');
                    $validated['picture'] = "storage/{$path}";
                } else {
                    if (!array_key_exists('picture', $validated) || !is_string($validated['picture'])) {
                        unset($validated['picture']);
                    }
                }

                $lostMujremeen->update($validated);

                $lostMujremeen->load([
                    'resource' => fn ($qr) => $qr->select('id', 'name', 'code_number', 'phone_number'),
                ]);

                // 🔔 Notification: UPDATED + changes
                NotificationService::record(
                    $user,
                    module: 'مجرمین فراری',
                    action: 'updated',
                    title: 'مجرم فراری اصلاح شد',
                    message: 'نام: ' . ($lostMujremeen->name ?? ''),
                    routeName: 'lost-mujremeens',      // ⚠️ set your real route name
                    routeParams: ['id' => $lostMujremeen->id],
                    level: 'info',
                    changes: !empty($changes) ? $changes : null
                );

                return new LostMujremeenResource($lostMujremeen);
            });
        } catch (QueryException $e) {
            if ($this->isDuplicateKeyException($e)) {
                return response()->json($this->duplicateErrorPashto(), 422);
            }
            throw $e;
        }
    }

    public function destroy(Request $request, LostMujremeen $lostMujremeen)
    {
        $user = $request->user(); // 🔔

        return DB::transaction(function () use ($lostMujremeen, $user) {
            $name = $lostMujremeen->name;

            $this->deletePictureIfStored($lostMujremeen->picture);

            $lostMujremeen->delete();

            // 🔔 Notification: DELETED
            NotificationService::record(
                $user,
                module: 'مجرمین فراری',
                action: 'deleted',
                title: 'مجرم فراری حذف شد',
                message: 'نام: ' . ($name ?? ''),
                routeName: null,
                routeParams: ['lost-mujremeens'],
                level: 'warning',
                changes: null
            );

            return response()->json(['message' => 'Deleted']);
        });
    }

    public function bulkDelete(Request $request)
    {
        $user = $request->user(); // 🔔

        $ids = $request->input('ids', []);
        $ids = is_array($ids) ? array_values(array_filter($ids)) : [];

        return DB::transaction(function () use ($ids, $user) {
            if (empty($ids)) {
                return response()->noContent();
            }

            $items = LostMujremeen::whereIn('id', $ids)->get();
            foreach ($items as $item) {
                $this->deletePictureIfStored($item->picture);
            }

            LostMujremeen::whereIn('id', $ids)->delete();

            // 🔔 Notification: BULK DELETED
            NotificationService::record(
                $user,
                module: 'مجرمین فراری',
                action: 'deleted',
                title: 'مجرمین فراری به‌صورت گروهی حذف شد',
                message: 'تعداد: ' . count($ids),
                routeName: null,
                routeParams: ['lost-mujremeens'],
                level: 'warning',
                changes: null
            );

            return response()->noContent();
        });
    }
}
