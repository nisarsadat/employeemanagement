<?php

namespace App\Http\Controllers\fifthyFormats;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\fifthyFormats\Fasad;
use App\Http\Resources\fifthyformats\FasadResource;
use App\Http\Requests\fifthyformats\StoreFasadRequest;
use App\Http\Requests\fifthyformats\UpdateFasadRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use App\Services\NotificationService; // 🔔 notifications

class FasadController extends Controller
{
    public function index(Request $request)
    {
        $page    = $request->query('page');
        $perPage = $request->query('perPage', $request->query('per_page'));

        $isValidPage    = is_numeric($page) && (int) $page > 0;
        $isValidPerPage = is_numeric($perPage) && (int) $perPage > 0;

        $search = trim((string) $request->query('search', ''));

        $q = Fasad::query()
            ->with(['resource:id,name,code_number,phone_number'])
            ->when($search !== '', function ($sub) use ($search) {
                $sub->where(function ($x) use ($search) {
                    $x->where('name', 'like', "%{$search}%")
                      ->orWhere('father_name', 'like', "%{$search}%")
                      ->orWhere('nick_name', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('id');

        if ($isValidPage && $isValidPerPage) {
            $data = $q->paginate((int) $perPage, ['*'], 'page', (int) $page);
            return FasadResource::collection($data);
        }

        return FasadResource::collection($q->get());
    }

    public function store(StoreFasadRequest $request)
    {
        $user = $request->user(); // 🔔 who did this

        try {
            return DB::transaction(function () use ($request, $user) {
                $fasad = Fasad::create($request->validated());

                $fasad->load(['resource:id,name,code_number,phone_number']);

                // 🔔 Notification: CREATED
                NotificationService::record(
                    $user,
                    module: 'فساد',
                    action: 'created',
                    title: 'نوی ریکارډ ثبت شو',
                    message: 'نوم: ' . ($fasad->name ?? ''),
                    routeName: 'fasads',               // ⚠️ adjust to your real route name
                    routeParams: ['id' => $fasad->id],
                    level: 'success',
                    changes: null
                );

                return FasadResource::make($fasad);
            });
        } catch (QueryException $e) {
            // ✅ Duplicate unique constraint (MySQL: 1062)
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

    public function show(Fasad $fasad)
    {
        $fasad->load(['resource:id,name,code_number,phone_number']);
        return FasadResource::make($fasad);
    }

    public function update(UpdateFasadRequest $request, Fasad $fasad)
    {
        $user = $request->user(); // 🔔

        try {
            return DB::transaction(function () use ($request, $fasad, $user) {
                // 🧮 build changes BEFORE update
                $original  = $fasad->getOriginal();
                $validated = $request->validated();
                $changes   = [];

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

                // 🔄 apply update
                $fasad->update($validated);
                $fasad->load(['resource:id,name,code_number,phone_number']);

                // 🔔 Notification: UPDATED + changes
                NotificationService::record(
                    $user,
                    module: 'فساد',
                    action: 'updated',
                    title: 'ریکارډ اصلاح شو',
                    message: 'نوم: ' . ($fasad->name ?? ''),
                    routeName: 'fasads',               // ⚠️ adjust if needed
                    routeParams: ['id' => $fasad->id],
                    level: 'info',
                    changes: !empty($changes) ? $changes : null
                );

                return FasadResource::make($fasad);
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

    public function destroy(Request $request, Fasad $fasad)
    {
        $user = $request->user(); // 🔔

        return DB::transaction(function () use ($fasad, $user) {
            $name = $fasad->name;

            $fasad->delete();

            // 🔔 Notification: DELETED
            NotificationService::record(
                $user,
                module: 'فساد',
                action: 'deleted',
                title: 'ریکارډ حذف شو',
                message: 'نوم: ' . ($name ?? ''),
                routeName: null,
                routeParams: ['fasads'],
                level: 'warning',
                changes: null
            );

            return response()->noContent();
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

            $rows = Fasad::whereIn('id', $ids)->get(['id', 'name']);

            Fasad::destroy($ids);

            // 🔔 Notification: BULK DELETED
            NotificationService::record(
                $user,
                module: 'فساد',
                action: 'bulk_deleted',
                title: 'ډله ییز حذف ترسره شو',
                message: 'ټول حذف شوي: ' . count($ids),
                routeName: null,
                routeParams: ['fasads'],
                level: 'warning',
                changes: [
                    'deleted_ids' => [
                        'old' => $rows->pluck('id')->values()->all(),
                        'new' => [],
                    ],
                    'deleted_names' => [
                        'old' => $rows->pluck('name')->values()->all(),
                        'new' => [],
                    ],
                ],
            );

            return response()->noContent();
        });
    }
}
