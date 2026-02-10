<?php

namespace App\Http\Controllers\fifthyFormats;

use App\Http\Controllers\Controller;
use App\Http\Requests\fifthyFormats\StoreSarafyanoRequest;
use App\Http\Requests\fifthyFormats\UpdateSarafyanoRequest;
use App\Http\Resources\fifthyFormats\SarafyanoResource;
use App\Models\fifthyFormats\Sarafyano;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use App\Services\NotificationService; // 🔔 notifications

class SarafyanoController extends Controller
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

        $q = Sarafyano::query()
            ->with(['resource:id,name,code_number,phone_number'])
            ->when($search !== '', function ($sub) use ($search) {
                $sub->where(function ($x) use ($search) {
                    $x->where('name', 'like', "%{$search}%")
                        ->orWhere('sarafi_name', 'like', "%{$search}%")
                        ->orWhere('sarafi_type', 'like', "%{$search}%")
                        ->orWhere('father_name', 'like', "%{$search}%")
                        ->orWhere('position', 'like', "%{$search}%")
                        ->orWhere('phone_number', 'like', "%{$search}%")
                        ->orWhere('province', 'like', "%{$search}%")
                        ->orWhere('zone', 'like', "%{$search}%")
                        ->orWhere('district', 'like', "%{$search}%")
                        ->orWhere('village', 'like', "%{$search}%")
                        ->orWhere('market_name', 'like', "%{$search}%")
                        ->orWhere('shop_number', 'like', "%{$search}%");
                });
            })
            ->when($rid, fn ($sub) => $sub->where('resource_id', $rid))
            ->orderBy($sortBy, $sortDir);

        if ($isValidPage && $isValidPerPage) {
            return SarafyanoResource::collection(
                $q->paginate((int) $perPage, ['*'], 'page', (int) $page)
            );
        }

        return SarafyanoResource::collection($q->get());
    }

    public function show(Sarafyano $sarafyano)
    {
        $sarafyano->load(['resource:id,name,code_number,phone_number']);
        return new SarafyanoResource($sarafyano);
    }

    public function store(StoreSarafyanoRequest $request)
    {
        $user = $request->user(); // 🔔
        $data = $request->validated();

        try {
            return DB::transaction(function () use ($data, $user) {
                $sarafyano = Sarafyano::create($data);

                $sarafyano->load(['resource:id,name,code_number,phone_number']);

                // 🔔 Notification: CREATED
                NotificationService::record(
                    $user,
                    module: 'صرافیانو',
                    action: 'created',
                    title: 'نوی ریکارډ ثبت شو',
                    message: 'نوم: ' . ($sarafyano->name ?? ''),
                    routeName: 'sarafyanos', // ⚠️ adjust to your real route name
                    routeParams: ['id' => $sarafyano->id],
                    level: 'success',
                    changes: null
                );

                return (new SarafyanoResource($sarafyano))
                    ->additional(['message' => 'Created'])
                    ->response()
                    ->setStatusCode(201);
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

    public function update(UpdateSarafyanoRequest $request, Sarafyano $sarafyano)
    {
        $user = $request->user(); // 🔔
        $data = $request->validated();

        try {
            return DB::transaction(function () use ($data, $sarafyano, $user) {

                // 🧮 changes BEFORE update
                $original = $sarafyano->getOriginal();
                $changes  = [];

                foreach ($data as $field => $newValue) {
                    if (array_key_exists($field, $original) && $original[$field] != $newValue) {
                        $changes[$field] = [
                            'old' => $original[$field],
                            'new' => $newValue,
                        ];
                    }
                }

                $sarafyano->update($data);
                $sarafyano->load(['resource:id,name,code_number,phone_number']);

                // 🔔 Notification: UPDATED
                NotificationService::record(
                    $user,
                    module: 'صرافیانو',
                    action: 'updated',
                    title: 'ریکارډ اصلاح شو',
                    message: 'نوم: ' . ($sarafyano->name ?? ''),
                    routeName: 'sarafyanos',
                    routeParams: ['id' => $sarafyano->id],
                    level: 'info',
                    changes: !empty($changes) ? $changes : null
                );

                return new SarafyanoResource($sarafyano);
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

    public function destroy(Request $request, Sarafyano $sarafyano)
    {
        $user = $request->user(); // 🔔

        return DB::transaction(function () use ($sarafyano, $user) {
            $name = $sarafyano->name;

            $sarafyano->delete();

            // 🔔 Notification: DELETED
            NotificationService::record(
                $user,
                module: 'صرافیانو',
                action: 'deleted',
                title: 'ریکارډ حذف شو',
                message: 'نوم: ' . ($name ?? ''),
                routeName: null,
                routeParams: [],
                level: 'warning',
                changes: null
            );

            return response()->noContent();
        });
    }
}
