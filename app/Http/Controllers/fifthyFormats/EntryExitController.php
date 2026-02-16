<?php

namespace App\Http\Controllers\fifthyFormats;

use App\Http\Controllers\Controller;
use App\Http\Requests\fifthyFormats\StoreEntryExitRequest;
use App\Http\Requests\fifthyFormats\UpdateEntryExitRequest;
use App\Http\Resources\fifthyFormats\EntryExitResource;
use App\Models\fifthyFormats\EntryExit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\QueryException;
use App\Services\NotificationService; // 🔔 notifications

class EntryExitController extends Controller
{
    public function index(Request $request)
    {
        $page    = $request->query('page');
        $perPage = $request->query('perPage', $request->query('per_page'));

        $isValidPage    = is_numeric($page) && (int) $page > 0;
        $isValidPerPage = is_numeric($perPage) && (int) $perPage > 0;

        $search = trim((string) $request->query('search', ''));
        $rid    = $request->query('resource_id');

        // ✅ columns that exist
        $sortable = ['id', 'created_at', 'updated_at'];
        $sortBy   = in_array($request->query('sort_by', 'created_at'), $sortable, true)
            ? $request->query('sort_by', 'created_at')
            : 'created_at';

        $sortDir = strtolower((string) $request->query('sort_dir', 'desc'));
        if (!in_array($sortDir, ['asc', 'desc'], true)) {
            $sortDir = 'desc';
        }

        $q = EntryExit::query()
            ->with(['resource:id,name,code_number,phone_number'])
            ->when($search !== '', function ($sub) use ($search) {
                $sub->where(function ($x) use ($search) {
                    $x->where('entry_way', 'like', "%{$search}%")
                        ->orWhere('way_type', 'like', "%{$search}%")
                        ->orWhere('province', 'like', "%{$search}%")
                        ->orWhere('district', 'like', "%{$search}%")
                        ->orWhere('village', 'like', "%{$search}%")
                        ->orWhere('specific_place', 'like', "%{$search}%")
                        ->orWhere('where_went', 'like', "%{$search}%")
                        ->orWhere('where_come', 'like', "%{$search}%");
                });
            })
            ->when($rid, fn ($sub) => $sub->where('resource_id', $rid))
            ->orderBy($sortBy, $sortDir);

        if ($isValidPage && $isValidPerPage) {
            return EntryExitResource::collection(
                $q->paginate((int) $perPage, ['*'], 'page', (int) $page)
            );
        }

        return EntryExitResource::collection($q->get());
    }

    public function show(EntryExit $entry_exit)
    {
        $entry_exit->load(['resource:id,name,code_number,phone_number']);
        return new EntryExitResource($entry_exit);
    }

    /**
     * POST /entry-exits
     * Accepts:
     * - picture as FILE or STRING
     */
    public function store(StoreEntryExitRequest $request)
    {
        $user = $request->user(); // 🔔
        $data = $request->validated();

        try {
            return DB::transaction(function () use ($request, $data, $user) {
                // ✅ picture: file OR string (same style as SaeenFasad)
                if ($request->hasFile('picture')) {
                    $path = $request->file('picture')->store('uploads/entry-exits', 'public');
                    $data['picture'] = "storage/{$path}";
                } else {
                    if (array_key_exists('picture', $data) && !is_string($data['picture'])) {
                        unset($data['picture']);
                    }
                }

                $entry_exit = EntryExit::create($data);

                $entry_exit->load(['resource:id,name,code_number,phone_number']);

                // 🔔 Notification: CREATED
                NotificationService::record(
                    $user,
                    module: 'داخل/خارج',
                    action: 'created',
                    title: 'نوی ریکارډ ثبت شو',
                    message: 'لار: ' . ($entry_exit->entry_way ?? ''),
                    routeName: 'entry-exits', // ⚠️ adjust if your route name differs
                    routeParams: ['id' => $entry_exit->id],
                    level: 'success',
                    changes: null
                );

                return (new EntryExitResource($entry_exit))
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

    /**
     * PUT /entry-exits/{entry_exit}
     * Accepts:
     * - picture as FILE or STRING (replaces old file if new uploaded)
     */
    public function update(UpdateEntryExitRequest $request, EntryExit $entry_exit)
    {
        $user = $request->user(); // 🔔
        $data = $request->validated();

        try {
            return DB::transaction(function () use ($request, $data, $entry_exit, $user) {

                // 🧮 changes BEFORE update
                $original = $entry_exit->getOriginal();
                $changes  = [];

                foreach ($data as $field => $newValue) {
                    if (array_key_exists($field, $original) && $original[$field] != $newValue) {
                        $changes[$field] = [
                            'old' => $original[$field],
                            'new' => $newValue,
                        ];
                    }
                }

                // ✅ picture: replace file if uploaded (same style as SaeenFasad)
                if ($request->hasFile('picture')) {
                    if ($entry_exit->picture && str_starts_with($entry_exit->picture, 'storage/')) {
                        $old = str_replace('storage/', '', $entry_exit->picture);
                        Storage::disk('public')->delete($old);
                    }

                    $path = $request->file('picture')->store('uploads/entry-exits', 'public');
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

                $entry_exit->update($data);
                $entry_exit->load(['resource:id,name,code_number,phone_number']);

                // 🔔 Notification: UPDATED
                NotificationService::record(
                    $user,
                    module: 'داخل/خارج',
                    action: 'updated',
                    title: 'ریکارډ اصلاح شو',
                    message: 'لار: ' . ($entry_exit->entry_way ?? ''),
                    routeName: 'entry-exits',
                    routeParams: ['id' => $entry_exit->id],
                    level: 'info',
                    changes: !empty($changes) ? $changes : null
                );

                return new EntryExitResource($entry_exit);
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

    public function destroy(Request $request, EntryExit $entry_exit)
    {
        $user = $request->user(); // 🔔

        return DB::transaction(function () use ($entry_exit, $user) {

            $way = $entry_exit->entry_way;

            // ✅ delete picture file (same style as SaeenFasad destroy)
            if ($entry_exit->picture && str_starts_with($entry_exit->picture, 'storage/')) {
                $old = str_replace('storage/', '', $entry_exit->picture);
                Storage::disk('public')->delete($old);
            }

            $entry_exit->delete();

            // 🔔 Notification: DELETED
            NotificationService::record(
                $user,
                module: 'داخل/خارج',
                action: 'deleted',
                title: 'ریکارډ حذف شو',
                message: 'لار: ' . ($way ?? ''),
                routeName: null,
                routeParams: [],
                level: 'warning',
                changes: null
            );

            return response()->noContent();
        });
    }
}
