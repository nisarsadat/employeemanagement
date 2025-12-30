<?php

namespace App\Http\Controllers\fifthyFormats;

use App\Http\Controllers\Controller;
use App\Http\Requests\fifthyFormats\StoreMahajeratRequest;
use App\Http\Requests\fifthyFormats\UpdateMahajeratRequest;
use App\Http\Resources\fifthyFormats\MahajeratResource;
use App\Models\fifthyFormats\Mahajerat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use App\Services\NotificationService; // 🔔 notifications

class MahajeratController extends Controller
{
    public function index(Request $request)
    {
        $page    = $request->query('page');
        $perPage = $request->query('perPage', $request->query('per_page'));

        $isValidPage    = is_numeric($page) && (int) $page > 0;
        $isValidPerPage = is_numeric($perPage) && (int) $perPage > 0;

        $search = trim((string) $request->query('search', ''));
        $rid    = $request->query('resource_id');

        $sortable = ['id', 'name', 'created_at', 'updated_at'];
        $sortBy   = in_array($request->query('sort_by', 'created_at'), $sortable, true)
            ? $request->query('sort_by', 'created_at')
            : 'created_at';

        $sortDir = strtolower((string) $request->query('sort_dir', 'desc'));
        if (!in_array($sortDir, ['asc', 'desc'], true)) {
            $sortDir = 'desc';
        }

        $q = Mahajerat::query()
            ->with(['resource:id,name,code_number,phone_number'])
            ->when($search !== '', function ($sub) use ($search) {
                $sub->where(function ($x) use ($search) {
                    $x->where('name', 'like', "%{$search}%")
                      ->orWhere('father_name', 'like', "%{$search}%")
                      ->orWhere('scientific_level', 'like', "%{$search}%")
                      ->orWhere('main_province', 'like', "%{$search}%")
                      ->orWhere('main_district', 'like', "%{$search}%")
                      ->orWhere('main_village', 'like', "%{$search}%")
                      ->orWhere('now_province', 'like', "%{$search}%")
                      ->orWhere('now_zone', 'like', "%{$search}%")
                      ->orWhere('now_district', 'like', "%{$search}%")
                      ->orWhere('now_vinage', 'like', "%{$search}%")
                      ->orWhere('specific_place', 'like', "%{$search}%")
                      ->orWhere('country', 'like', "%{$search}%");
                });
            })
            ->when($rid, fn ($sub) => $sub->where('resource_id', $rid))
            ->orderBy($sortBy, $sortDir);

        if ($isValidPage && $isValidPerPage) {
            return MahajeratResource::collection(
                $q->paginate((int) $perPage, ['*'], 'page', (int) $page)
            );
        }

        return MahajeratResource::collection($q->get());
    }

    public function show(Mahajerat $mahajerat)
    {
        $mahajerat->load(['resource:id,name,code_number,phone_number']);
        return new MahajeratResource($mahajerat);
    }

    public function store(StoreMahajeratRequest $request)
    {
        $user = $request->user(); // 🔔
        $data = $request->validated();

        try {
            return DB::transaction(function () use ($data, $user) {
                $item = Mahajerat::create($data);

                $item->load(['resource:id,name,code_number,phone_number']);

                // 🔔 Notification: CREATED
                NotificationService::record(
                    $user,
                    module: 'مهاجرات',
                    action: 'created',
                    title: 'نوی ریکارډ ثبت شو',
                    message: 'نوم: ' . ($item->name ?? ''),
                    routeName: 'mahajerats', // ⚠️ adjust if your route is different
                    routeParams: ['id' => $item->id],
                    level: 'success',
                    changes: null
                );

                return (new MahajeratResource($item))
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

    public function update(UpdateMahajeratRequest $request, Mahajerat $mahajerat)
    {
        $user = $request->user(); // 🔔
        $data = $request->validated();

        try {
            return DB::transaction(function () use ($data, $mahajerat, $user) {

                // 🧮 changes BEFORE update
                $original = $mahajerat->getOriginal();
                $changes  = [];

                foreach ($data as $field => $newValue) {
                    if (array_key_exists($field, $original) && $original[$field] != $newValue) {
                        $changes[$field] = [
                            'old' => $original[$field],
                            'new' => $newValue,
                        ];
                    }
                }

                $mahajerat->update($data);
                $mahajerat->load(['resource:id,name,code_number,phone_number']);

                // 🔔 Notification: UPDATED
                NotificationService::record(
                    $user,
                    module: 'مهاجرات',
                    action: 'updated',
                    title: 'ریکارډ اصلاح شو',
                    message: 'نوم: ' . ($mahajerat->name ?? ''),
                    routeName: 'mahajerats',
                    routeParams: ['id' => $mahajerat->id],
                    level: 'info',
                    changes: !empty($changes) ? $changes : null
                );

                return new MahajeratResource($mahajerat);
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

    public function destroy(Request $request, Mahajerat $mahajerat)
    {
        $user = $request->user(); // 🔔

        return DB::transaction(function () use ($mahajerat, $user) {

            $name = $mahajerat->name;
            $mahajerat->delete();

            // 🔔 Notification: DELETED
            NotificationService::record(
                $user,
                module: 'مهاجرات',
                action: 'deleted',
                title: 'ریکارډ حذف شو',
                message: 'نوم: ' . ($name ?? ''),
                routeName: null,
                routeParams: ['mahajerats'],
                level: 'warning',
                changes: null
            );

            return response()->noContent();
        });
    }
}
