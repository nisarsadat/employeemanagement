<?php

namespace App\Http\Controllers\fifthyFormats;

use App\Http\Controllers\Controller;
use App\Http\Requests\fifthyFormats\StoreHeistRequest;
use App\Http\Requests\fifthyFormats\UpdateHeistRequest;
use App\Http\Resources\fifthyFormats\HeistResource;
use App\Models\fifthyFormats\Heist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\QueryException;
use App\Services\NotificationService; // 🔔 notifications

class HeistController extends Controller
{
    public function index(Request $request)
    {
        $page    = $request->query('page');
        $perPage = $request->query('perPage', $request->query('per_page'));

        $isValidPage    = is_numeric($page) && (int) $page > 0;
        $isValidPerPage = is_numeric($perPage) && (int) $perPage > 0;

        $search = trim((string) $request->query('search', ''));

        $q = Heist::query()
            ->with(['resource:id,name,code_number,phone_number'])
            ->when($search !== '', function ($sub) use ($search) {
                $sub->where(function ($x) use ($search) {
                    $x->where('name', 'like', "%{$search}%")
                      ->orWhere('father_name', 'like', "%{$search}%")
                      ->orWhere('gun_type', 'like', "%{$search}%")
                      ->orWhere('note', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('id');

        if ($isValidPage && $isValidPerPage) {
            return HeistResource::collection(
                $q->paginate((int) $perPage, ['*'], 'page', (int) $page)
            );
        }

        return HeistResource::collection($q->get());
    }

    public function show(Heist $heist)
    {
        $heist->load(['resource:id,name,code_number,phone_number']);
        return new HeistResource($heist);
    }

    public function store(StoreHeistRequest $request)
    {
        $user = $request->user(); // 🔔
        $data = $request->validated();

        try {
            return DB::transaction(function () use ($request, $data, $user) {

                if ($request->hasFile('picture')) {
                    $path = $request->file('picture')->store('uploads/heists', 'public');
                    $data['picture'] = "storage/{$path}";
                } else {
                    if (array_key_exists('picture', $data) && !is_string($data['picture'])) {
                        unset($data['picture']);
                    }
                }

                $heist = Heist::create($data);
                $heist->load(['resource:id,name,code_number,phone_number']);

                // 🔔 Notification: CREATED
                NotificationService::record(
                    $user,
                    module: 'سرقت',
                    action: 'created',
                    title: 'نوی ریکارډ ثبت شو',
                    message: 'نوم: ' . ($heist->name ?? ''),
                    routeName: 'heists',
                    routeParams: ['id' => $heist->id],
                    level: 'success',
                    changes: null
                );

                return (new HeistResource($heist))
                    ->additional(['message' => 'Created'])
                    ->response()
                    ->setStatusCode(201);
            });
        } catch (QueryException $e) {
            if ((int) ($e->errorInfo[1] ?? 0) === 1062) {
                return response()->json([
                    'message' => 'دا ریکارډ له مخکې نه موجود دی (تکراري ثبت اجازه نشته).',
                    'errors'  => [
                        'duplicate' => ['دا ریکارډ له مخکې نه موجود دی (تکراري ثبت اجازه نشته).'],
                    ],
                ], 422);
            }

            throw $e;
        }
    }

    public function update(UpdateHeistRequest $request, Heist $heist)
    {
        $user = $request->user(); // 🔔
        $data = $request->validated();

        try {
            return DB::transaction(function () use ($request, $data, $heist, $user) {

                // 🧮 track changes
                $original = $heist->getOriginal();
                $changes  = [];

                foreach ($data as $field => $newValue) {
                    if (array_key_exists($field, $original) && $original[$field] != $newValue) {
                        $changes[$field] = [
                            'old' => $original[$field],
                            'new' => $newValue,
                        ];
                    }
                }

                if ($request->hasFile('picture')) {
                    if ($heist->picture && str_starts_with($heist->picture, 'storage/')) {
                        Storage::disk('public')->delete(
                            str_replace('storage/', '', $heist->picture)
                        );
                    }

                    $path = $request->file('picture')->store('uploads/heists', 'public');
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

                $heist->update($data);
                $heist->load(['resource:id,name,code_number,phone_number']);

                // 🔔 Notification: UPDATED
                NotificationService::record(
                    $user,
                    module: 'سرقت',
                    action: 'updated',
                    title: 'ریکارډ اصلاح شو',
                    message: 'نوم: ' . ($heist->name ?? ''),
                    routeName: 'heists',
                    routeParams: ['id' => $heist->id],
                    level: 'info',
                    changes: !empty($changes) ? $changes : null
                );

                return new HeistResource($heist);
            });
        } catch (QueryException $e) {
            if ((int) ($e->errorInfo[1] ?? 0) === 1062) {
                return response()->json([
                    'message' => 'دا ریکارډ له مخکې نه موجود دی (تکراري ثبت اجازه نشته).',
                    'errors'  => [
                        'duplicate' => ['دا ریکارډ له مخکې نه موجود دی (تکراري ثبت اجازه نشته).'],
                    ],
                ], 422);
            }

            throw $e;
        }
    }

    public function destroy(Request $request, Heist $heist)
    {
        $user = $request->user(); // 🔔

        return DB::transaction(function () use ($heist, $user) {

            if ($heist->picture && str_starts_with($heist->picture, 'storage/')) {
                Storage::disk('public')->delete(
                    str_replace('storage/', '', $heist->picture)
                );
            }

            $name = $heist->name;
            $heist->delete();

            // 🔔 Notification: DELETED
            NotificationService::record(
                $user,
                module: 'سرقت',
                action: 'deleted',
                title: 'ریکارډ حذف شو',
                message: 'نوم: ' . ($name ?? ''),
                routeName: null,
                routeParams: ['heists'],
                level: 'warning',
                changes: null
            );

            return response()->noContent();
        });
    }
}
