<?php

namespace App\Http\Controllers\fifthyformatone;

use App\Http\Controllers\Controller;
use App\Http\Requests\fifthyformatone\StoreRobbersRequest;
use App\Http\Requests\fifthyformatone\UpdateRobbersRequest;
use App\Http\Resources\fifthyformatone\RobbersResource;
use App\Models\fifthyformatone\Robbers;
use App\Services\NotificationService; // 🔔 notifications
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RobbersController extends Controller
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
        // MySQL duplicate entry error code
        return (int) ($e->errorInfo[1] ?? 0) === 1062;
    }

    public function index(Request $request)
    {
        $perPage = (int) $request->get('perPage', 15);
        $search  = $request->get('search');
        $rid     = $request->get('resource_id');

        $sortBy  = $request->get('sort_by', 'created_at');
        $sortDir = in_array($request->get('sort_dir'), ['asc', 'desc'], true)
            ? $request->get('sort_dir')
            : 'desc';

        $query = Robbers::query()->with([
            'resource' => fn ($qr) => $qr->select('id', 'name', 'code_number', 'phone_number'),
        ]);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('nickname', 'like', "%{$search}%")
                    ->orWhere('father_name', 'like', "%{$search}%")
                    ->orWhere('phone_number', 'like', "%{$search}%");
            });
        }

        if ($rid) {
            $query->where('resource_id', $rid);
        }

        $query->orderBy($sortBy, $sortDir);

        return RobbersResource::collection($query->paginate($perPage));
    }

    public function store(StoreRobbersRequest $request)
    {
        $user = $request->user(); // 🔔

        try {
            return DB::transaction(function () use ($request, $user) {
                $robber = Robbers::create($request->validated());

                $robber->load([
                    'resource' => fn ($qr) => $qr->select('id', 'name', 'code_number', 'phone_number'),
                ]);

                // 🔔 Notification: CREATED
                NotificationService::record(
                    $user,
                    module: 'دزدان',
                    action: 'created',
                    title: 'ریکارډ جدید ثبت شد',
                    message: 'نام: ' . ($robber->name ?? ''),
                    routeName: 'robbers', // ⚠️ set your real route name
                    routeParams: ['id' => $robber->id],
                    level: 'success',
                    changes: null
                );

                return (new RobbersResource($robber))
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

    /**
     * IMPORTANT:
     * This endpoint must ONLY be called with a VALID ID
     */
    public function show($id)
    {
        $robber = Robbers::with([
            'resource' => fn ($qr) => $qr->select('id', 'name', 'code_number', 'phone_number'),
        ])->findOrFail($id);

        return RobbersResource::make($robber);
    }

    public function update(UpdateRobbersRequest $request, $id)
    {
        $user = $request->user(); // 🔔

        try {
            return DB::transaction(function () use ($request, $id, $user) {
                $robber = Robbers::findOrFail($id);

                $validated = $request->validated();

                // 🧮 changes BEFORE update
                $original = $robber->getOriginal();
                $changes  = [];

                foreach ($validated as $field => $newValue) {
                    if (array_key_exists($field, $original) && $original[$field] != $newValue) {
                        $changes[$field] = [
                            'old' => $original[$field],
                            'new' => $newValue,
                        ];
                    }
                }

                $robber->update($validated);

                $robber->load([
                    'resource' => fn ($qr) => $qr->select('id', 'name', 'code_number', 'phone_number'),
                ]);

                // 🔔 Notification: UPDATED
                NotificationService::record(
                    $user,
                    module: 'دزدان',
                    action: 'updated',
                    title: 'ریکارډ اصلاح شد',
                    message: 'نام: ' . ($robber->name ?? ''),
                    routeName: 'robbers', // ⚠️ set your real route name
                    routeParams: ['id' => $robber->id],
                    level: 'info',
                    changes: !empty($changes) ? $changes : null
                );

                return RobbersResource::make($robber);
            });
        } catch (QueryException $e) {
            if ($this->isDuplicateKeyException($e)) {
                return response()->json($this->duplicateErrorPashto(), 422);
            }
            throw $e;
        }
    }

    public function destroy(Request $request, $id)
    {
        $user = $request->user(); // 🔔

        return DB::transaction(function () use ($id, $user) {
            $robber = Robbers::findOrFail($id);
            $name   = $robber->name;

            $robber->delete();

            // 🔔 Notification: DELETED
            NotificationService::record(
                $user,
                module: 'دزدان',
                action: 'deleted',
                title: 'ریکارډ حذف شد',
                message: 'نام: ' . ($name ?? ''),
                routeName: null,
                routeParams: ['robbers'],
                level: 'warning',
                changes: null
            );

            return response()->noContent();
        });
    }
}
