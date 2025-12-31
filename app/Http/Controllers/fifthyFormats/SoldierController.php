<?php

namespace App\Http\Controllers\fifthyFormats;

use App\Http\Controllers\Controller;
use App\Http\Requests\fifthyFormats\StoreSoldierRequest;
use App\Http\Requests\fifthyFormats\UpdateSoldierRequest;
use App\Http\Resources\fifthyFormats\SoldierResource;
use App\Models\fifthyFormats\Soldier;
use App\Services\NotificationService; // 🔔 notifications
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SoldierController extends Controller
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
                    'دا ریکارډ مخکې ثبت شوی دی (همدا نوم، د پلار نوم او اصلي پته).',
                ],
            ],
        ];
    }

    private function isDuplicateKeyException(QueryException $e): bool
    {
        // MySQL duplicate entry = 1062
        return (int) ($e->errorInfo[1] ?? 0) === 1062;
    }

    public function index(Request $request)
    {
        $perPage = (int) $request->get('perPage', 15);
        $search  = $request->get('search');
        $rid     = $request->get('resource_id');

        $sortBy  = $request->get('sort_by', 'created_at');
        $sortDir = in_array($request->get('sort_dir'), ['asc', 'desc'], true) ? $request->get('sort_dir') : 'desc';

        $q = Soldier::query()->with([
            'resource' => fn ($qr) => $qr->select('id', 'name', 'code_number', 'phone_number'),
        ]);

        if ($search) {
            $q->where(function ($qq) use ($search) {
                $qq->where('name', 'like', "%{$search}%")
                    ->orWhere('father_name', 'like', "%{$search}%")
                    ->orWhere('main_province', 'like', "%{$search}%")
                    ->orWhere('now_province', 'like', "%{$search}%")
                    ->orWhere('specific_place', 'like', "%{$search}%")
                    ->orWhere('old_job', 'like', "%{$search}%")
                    ->orWhere('now_job', 'like', "%{$search}%")
                    ->orWhere('phone_number', 'like', "%{$search}%");
            });
        }

        if ($rid) {
            $q->where('resource_id', $rid);
        }

        $q->orderBy($sortBy, $sortDir);

        return SoldierResource::collection($q->paginate($perPage));
    }

    public function show(Soldier $soldier)
    {
        $soldier->load([
            'resource' => fn ($qr) => $qr->select('id', 'name', 'code_number', 'phone_number'),
        ]);

        return new SoldierResource($soldier);
    }

    public function store(StoreSoldierRequest $request)
    {
        $user = $request->user(); // 🔔 who did this

        try {
            return DB::transaction(function () use ($request, $user) {
                $data = $request->validated();

                $soldier = Soldier::create($data);

                $soldier->load([
                    'resource' => fn ($qr) => $qr->select('id', 'name', 'code_number', 'phone_number'),
                ]);

                // 🔔 Notification: CREATED
                NotificationService::record(
                    $user,
                    module: 'سرباز',
                    action: 'created',
                    title: 'سرباز جدید ثبت شد',
                    message: 'نام سرباز: ' . ($soldier->name ?? ''),
                    routeName: 'soldiers',              // ⚠️ set your real route name
                    routeParams: ['id' => $soldier->id],
                    level: 'success',
                    changes: null
                );

                return (new SoldierResource($soldier))
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

    public function update(UpdateSoldierRequest $request, Soldier $soldier)
    {
        $user = $request->user(); // 🔔

        try {
            return DB::transaction(function () use ($request, $soldier, $user) {
                $validated = $request->validated();

                // 🧮 build changes BEFORE update
                $original = $soldier->getOriginal();
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

                $soldier->update($validated);

                $soldier->load([
                    'resource' => fn ($qr) => $qr->select('id', 'name', 'code_number', 'phone_number'),
                ]);

                // 🔔 Notification: UPDATED + changes
                NotificationService::record(
                    $user,
                    module: 'سرباز',
                    action: 'updated',
                    title: 'سرباز اصلاح شد',
                    message: 'نام سرباز: ' . ($soldier->name ?? ''),
                    routeName: 'soldiers',              // ⚠️ set your real route name
                    routeParams: ['id' => $soldier->id],
                    level: 'info',
                    changes: !empty($changes) ? $changes : null
                );

                return new SoldierResource($soldier);
            });
        } catch (QueryException $e) {
            if ($this->isDuplicateKeyException($e)) {
                return response()->json($this->duplicateErrorPashto(), 422);
            }
            throw $e;
        }
    }

    public function destroy(Request $request, Soldier $soldier)
    {
        $user = $request->user(); // 🔔

        return DB::transaction(function () use ($soldier, $user) {
            $name = $soldier->name;

            $soldier->delete();

            // 🔔 Notification: DELETED
            NotificationService::record(
                $user,
                module: 'سرباز',
                action: 'deleted',
                title: 'سرباز حذف شد',
                message: 'نام سرباز: ' . ($name ?? ''),
                routeName: null,
                routeParams: ['soldiers'],
                level: 'warning',
                changes: null
            );

            return response()->noContent();
        });
    }
}
