<?php

namespace App\Http\Controllers\fifthyFormats;

use App\Http\Controllers\Controller;
use App\Http\Requests\fifthyFormats\StoreMulkanoRequest;
use App\Http\Requests\fifthyFormats\UpdateMulkanoRequest;
use App\Http\Resources\fifthyFormats\MulkanoResource;
use App\Models\fifthyFormats\Mulkano;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\QueryException;
use App\Services\NotificationService; // 🔔 notifications

class MulkanoController extends Controller
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

        $q = Mulkano::query()
            ->with(['resource:id,name,code_number,phone_number'])
            ->when($search !== '', function ($sub) use ($search) {
                $sub->where(function ($x) use ($search) {
                    $x->where('name', 'like', "%{$search}%")
                      ->orWhere('father_name', 'like', "%{$search}%")
                      ->orWhere('grand_father_name', 'like', "%{$search}%")
                      ->orWhere('phone_number', 'like', "%{$search}%")
                      ->orWhere('specific_place', 'like', "%{$search}%");
                });
            })
            ->when($rid, fn ($sub) => $sub->where('resource_id', $rid))
            ->orderBy($sortBy, $sortDir);

        if ($isValidPage && $isValidPerPage) {
            return MulkanoResource::collection(
                $q->paginate((int) $perPage, ['*'], 'page', (int) $page)
            );
        }

        return MulkanoResource::collection($q->get());
    }

    public function show(Mulkano $mulkano)
    {
        $mulkano->load(['resource:id,name,code_number,phone_number']);
        return new MulkanoResource($mulkano);
    }

    public function store(StoreMulkanoRequest $request)
    {
        $user = $request->user(); // 🔔
        $data = $request->validated();

        try {
            return DB::transaction(function () use ($request, $data, $user) {

                // ✅ picture
                if ($request->hasFile('picture')) {
                    $path = $request->file('picture')->store('uploads/mulkanos', 'public');
                    $data['picture'] = "storage/{$path}";
                } else {
                    if (array_key_exists('picture', $data) && !is_string($data['picture'])) {
                        unset($data['picture']);
                    }
                }

                $mulkano = Mulkano::create($data);
                $mulkano->load(['resource:id,name,code_number,phone_number']);

                // 🔔 Notification: CREATED
                NotificationService::record(
                    $user,
                    module: 'ملکانو',
                    action: 'created',
                    title: 'نوی ریکارډ ثبت شو',
                    message: 'نوم: ' . ($mulkano->name ?? ''),
                    routeName: 'mulkanos',
                    routeParams: ['id' => $mulkano->id],
                    level: 'success',
                    changes: null
                );

                return (new MulkanoResource($mulkano))
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

    public function update(UpdateMulkanoRequest $request, Mulkano $mulkano)
    {
        $user = $request->user(); // 🔔
        $data = $request->validated();

        try {
            return DB::transaction(function () use ($request, $data, $mulkano, $user) {

                // 🧮 changes BEFORE update
                $original = $mulkano->getOriginal();
                $changes  = [];

                foreach ($data as $field => $newValue) {
                    if (array_key_exists($field, $original) && $original[$field] != $newValue) {
                        $changes[$field] = [
                            'old' => $original[$field],
                            'new' => $newValue,
                        ];
                    }
                }

                // ✅ picture replace
                if ($request->hasFile('picture')) {
                    if ($mulkano->picture && str_starts_with($mulkano->picture, 'storage/')) {
                        Storage::disk('public')->delete(str_replace('storage/', '', $mulkano->picture));
                    }

                    $path = $request->file('picture')->store('uploads/mulkanos', 'public');
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

                $mulkano->update($data);
                $mulkano->load(['resource:id,name,code_number,phone_number']);

                // 🔔 Notification: UPDATED
                NotificationService::record(
                    $user,
                    module: 'ملکانو',
                    action: 'updated',
                    title: 'ریکارډ اصلاح شو',
                    message: 'نوم: ' . ($mulkano->name ?? ''),
                    routeName: 'mulkanos',
                    routeParams: ['id' => $mulkano->id],
                    level: 'info',
                    changes: !empty($changes) ? $changes : null
                );

                return new MulkanoResource($mulkano);
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

    public function destroy(Request $request, Mulkano $mulkano)
    {
        $user = $request->user(); // 🔔

        return DB::transaction(function () use ($mulkano, $user) {

            if ($mulkano->picture && str_starts_with($mulkano->picture, 'storage/')) {
                Storage::disk('public')->delete(str_replace('storage/', '', $mulkano->picture));
            }

            $name = $mulkano->name;
            $mulkano->delete();

            // 🔔 Notification: DELETED
            NotificationService::record(
                $user,
                module: 'ملکانو',
                action: 'deleted',
                title: 'ریکارډ حذف شو',
                message: 'نوم: ' . ($name ?? ''),
                routeName: null,
                routeParams: ['mulkanos'],
                level: 'warning',
                changes: null
            );

            return response()->noContent();
        });
    }
}
