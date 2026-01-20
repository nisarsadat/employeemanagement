<?php

namespace App\Http\Controllers\fifthyformatsthree;

use App\Http\Controllers\Controller;
use App\Models\fifthyformatsthree\Mujahedino;
use App\Http\Resources\fifthyformatsthree\MujahedinoResource;
use App\Http\Requests\fifthyformatsthree\StoreMujahedinoRequest;
use App\Http\Requests\fifthyformatsthree\UpdateMujahedinoRequest;
use App\Services\NotificationService; // 🔔 notifications
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MujahedinoController extends Controller
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
                    'د همدې معلوماتو سره (لقب/نوم، د پلار نوم او اصلي ادرس) ریکارډ مخکې له مخکې موجود دی.',
                ],
            ],
        ];
    }

    private function isDuplicateKeyException(QueryException $e): bool
    {
        return (int) ($e->errorInfo[1] ?? 0) === 1062;
    }

    /**
     * GET /mujahedinos
     */
    public function index(Request $request)
    {
        $perPage = (int) $request->input('perPage', 10);
        $search  = trim((string) $request->input('search'));

        $sortBy  = $request->input('sort_by', 'created_at');
        $sortDir = in_array($request->input('sort_dir'), ['asc', 'desc'], true)
            ? $request->input('sort_dir')
            : 'desc';

        $q = Mujahedino::query()->with([
            'resource' => fn ($qr) => $qr->select('id', 'name', 'code_number', 'phone_number'),
        ]);

        if ($search !== '') {
            $q->where(function ($qq) use ($search) {
                $qq->where('name', 'like', "%{$search}%")
                   ->orWhere('nickname', 'like', "%{$search}%")
                   ->orWhere('father_name', 'like', "%{$search}%")
                   ->orWhere('organ', 'like', "%{$search}%")
                   ->orWhere('now_province', 'like', "%{$search}%")
                   ->orWhere('now_district', 'like', "%{$search}%")
                   ->orWhere('now_village', 'like', "%{$search}%");
            });
        }

        $q->orderBy($sortBy, $sortDir);

        return MujahedinoResource::collection($q->paginate($perPage));
    }

    /**
     * POST /mujahedinos
     */
    public function store(StoreMujahedinoRequest $request)
    {
        $user = $request->user(); // 🔔

        try {
            return DB::transaction(function () use ($request, $user) {
                $item = Mujahedino::create($request->validated());

                $item->load([
                    'resource' => fn ($qr) => $qr->select('id', 'name', 'code_number', 'phone_number'),
                ]);

                // 🔔 Notification: CREATED
                NotificationService::record(
                    $user,
                    module: 'مجاهدین',
                    action: 'created',
                    title: 'نوی مجاهد ثبت شو',
                    message: 'نوم: ' . ($item->name ?? ''),
                    routeName: 'mujahedinos',
                    routeParams: ['id' => $item->id],
                    level: 'success',
                    changes: null
                );

                return (new MujahedinoResource($item))
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
     * GET /mujahedinos/{mujahedino}
     */
    public function show(Mujahedino $mujahedino)
    {
        $mujahedino->load([
            'resource' => fn ($qr) => $qr->select('id', 'name', 'code_number', 'phone_number'),
        ]);

        return MujahedinoResource::make($mujahedino);
    }

    /**
     * PUT /mujahedinos/{mujahedino}
     */
    public function update(UpdateMujahedinoRequest $request, Mujahedino $mujahedino)
    {
        $user = $request->user(); // 🔔

        try {
            return DB::transaction(function () use ($request, $mujahedino, $user) {
                $validated = $request->validated();

                // 🧮 changes BEFORE update
                $original = $mujahedino->getOriginal();
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

                $mujahedino->update($validated);

                $mujahedino->load([
                    'resource' => fn ($qr) => $qr->select('id', 'name', 'code_number', 'phone_number'),
                ]);

                // 🔔 Notification: UPDATED
                NotificationService::record(
                    $user,
                    module: 'مجاهدین',
                    action: 'updated',
                    title: 'مجاهد اصلاح شو',
                    message: 'نوم: ' . ($mujahedino->name ?? ''),
                    routeName: 'mujahedinos',
                    routeParams: ['id' => $mujahedino->id],
                    level: 'info',
                    changes: !empty($changes) ? $changes : null
                );

                return MujahedinoResource::make($mujahedino);
            });
        } catch (QueryException $e) {
            if ($this->isDuplicateKeyException($e)) {
                return response()->json($this->duplicateErrorPashto(), 422);
            }
            throw $e;
        }
    }

    /**
     * DELETE /mujahedinos/{mujahedino}
     */
    public function destroy(Request $request, Mujahedino $mujahedino)
    {
        $user = $request->user(); // 🔔

        return DB::transaction(function () use ($mujahedino, $user) {
            $name = $mujahedino->name;

            $mujahedino->delete();

            // 🔔 Notification: DELETED
            NotificationService::record(
                $user,
                module: 'مجاهدین',
                action: 'deleted',
                title: 'مجاهد حذف شو',
                message: 'نوم: ' . ($name ?? ''),
                routeName: null,
                routeParams: ['mujahedinos'],
                level: 'warning',
                changes: null
            );

            return response()->noContent();
        });
    }

    /**
     * Optional bulk delete:
     * POST /mujahedinos/bulk-delete  with { "ids": [1,2,3] }
     */
    public function bulkDelete(Request $request)
    {
        $user = $request->user(); // 🔔

        $ids = $request->input('ids', []);
        $ids = is_array($ids) ? array_values(array_filter($ids)) : [];

        return DB::transaction(function () use ($ids, $user) {
            if (empty($ids)) {
                return response()->noContent();
            }

            Mujahedino::whereIn('id', $ids)->delete();

            // 🔔 Notification: BULK DELETED
            NotificationService::record(
                $user,
                module: 'مجاهدین',
                action: 'deleted',
                title: 'ډله‌ییز حذف ترسره شو',
                message: 'حذف شوي ریکارډونه: ' . count($ids),
                routeName: null,
                routeParams: ['mujahedinos'],
                level: 'warning',
                changes: null
            );

            return response()->noContent();
        });
    }
}
