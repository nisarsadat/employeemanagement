<?php

namespace App\Http\Controllers\fifthyFormats;

use App\Http\Controllers\Controller;
use App\Http\Requests\fifthyFormats\StoreClubsRequest;
use App\Http\Requests\fifthyFormats\UpdateClubsRequest;
use App\Http\Resources\fifthyFormats\ClubsResource;
use App\Models\fifthyFormats\Clubs;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use App\Services\NotificationService; // 🔔 notifications

class ClubsController extends Controller
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

        $q = Clubs::query()
            ->with(['resource:id,name,code_number,phone_number'])
            ->when($search !== '', function ($sub) use ($search) {
                $sub->where(function ($x) use ($search) {
                    $x->where('name', 'like', "%{$search}%")
                        ->orWhere('club_name', 'like', "%{$search}%")
                        ->orWhere('club_type', 'like', "%{$search}%")
                        ->orWhere('father_name', 'like', "%{$search}%")
                        ->orWhere('position', 'like', "%{$search}%")
                        ->orWhere('phone_number', 'like', "%{$search}%")
                        ->orWhere('province', 'like', "%{$search}%")
                        ->orWhere('zone', 'like', "%{$search}%")
                        ->orWhere('district', 'like', "%{$search}%")
                        ->orWhere('specific_place', 'like', "%{$search}%");
                });
            })
            ->when($rid, fn ($sub) => $sub->where('resource_id', $rid))
            ->orderBy($sortBy, $sortDir);

        if ($isValidPage && $isValidPerPage) {
            return ClubsResource::collection(
                $q->paginate((int) $perPage, ['*'], 'page', (int) $page)
            );
        }

        return ClubsResource::collection($q->get());
    }

    public function show(Clubs $club)
    {
        $club->load(['resource:id,name,code_number,phone_number']);
        return new ClubsResource($club);
    }

    public function store(StoreClubsRequest $request)
    {
        $user = $request->user(); // 🔔
        $data = $request->validated();

        try {
            return DB::transaction(function () use ($data, $user) {

                $club = Clubs::create($data);
                $club->load(['resource:id,name,code_number,phone_number']);

                // 🔔 Notification: CREATED
                NotificationService::record(
                    $user,
                    module: 'کلبونه',
                    action: 'created',
                    title: 'نوی ریکارډ ثبت شو',
                    message: 'نوم: ' . ($club->name ?? ''),
                    routeName: 'clubs', // ⚠️ adjust if your route name differs
                    routeParams: ['id' => $club->id],
                    level: 'success',
                    changes: null
                );

                return (new ClubsResource($club))
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

    public function update(UpdateClubsRequest $request, Clubs $club)
    {
        $user = $request->user(); // 🔔
        $data = $request->validated();

        try {
            return DB::transaction(function () use ($data, $club, $user) {

                // 🧮 changes BEFORE update
                $original = $club->getOriginal();
                $changes  = [];

                foreach ($data as $field => $newValue) {
                    if (array_key_exists($field, $original) && $original[$field] != $newValue) {
                        $changes[$field] = [
                            'old' => $original[$field],
                            'new' => $newValue,
                        ];
                    }
                }

                $club->update($data);
                $club->load(['resource:id,name,code_number,phone_number']);

                // 🔔 Notification: UPDATED
                NotificationService::record(
                    $user,
                    module: 'کلبونه',
                    action: 'updated',
                    title: 'ریکارډ اصلاح شو',
                    message: 'نوم: ' . ($club->name ?? ''),
                    routeName: 'clubs',
                    routeParams: ['id' => $club->id],
                    level: 'info',
                    changes: !empty($changes) ? $changes : null
                );

                return new ClubsResource($club);
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

    public function destroy(Request $request, Clubs $club)
    {
        $user = $request->user(); // 🔔

        return DB::transaction(function () use ($club, $user) {

            $name = $club->name;
            $club->delete();

            // 🔔 Notification: DELETED
            NotificationService::record(
                $user,
                module: 'کلبونه',
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
