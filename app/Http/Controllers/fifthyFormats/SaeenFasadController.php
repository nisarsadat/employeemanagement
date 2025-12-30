<?php

namespace App\Http\Controllers\fifthyFormats;

use App\Http\Controllers\Controller;
use App\Http\Requests\fifthyFormats\StoreSaeenFasadRequest;
use App\Http\Requests\fifthyFormats\UpdateSaeenFasadRequest;
use App\Http\Resources\fifthyFormats\SaeenFasadResource;
use App\Models\fifthyFormats\SaeenFasad;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\QueryException;
use App\Services\NotificationService; // 🔔 notifications

class SaeenFasadController extends Controller
{
    /**
     * GET /saeen-fasadas
     */
    public function index(Request $request)
    {
        $page    = $request->query('page');
        $perPage = $request->query('perPage', $request->query('per_page'));

        $isValidPage    = is_numeric($page) && (int) $page > 0;
        $isValidPerPage = is_numeric($perPage) && (int) $perPage > 0;

        $search = trim((string) $request->query('search', ''));

        $q = SaeenFasad::query()
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
            return SaeenFasadResource::collection($data);
        }

        return SaeenFasadResource::collection($q->get());
    }

    /**
     * GET /saeen-fasadas/{saeenFasad}
     */
    public function show(SaeenFasad $saeenFasad)
    {
        $saeenFasad->load(['resource:id,name,code_number,phone_number']);
        return new SaeenFasadResource($saeenFasad);
    }

    /**
     * POST /saeen-fasadas
     * Accepts `picture` either as a FILE or a STRING path/url.
     */
    public function store(StoreSaeenFasadRequest $request)
    {
        $user = $request->user(); // 🔔 who did this
        $data = $request->validated();

        try {
            return DB::transaction(function () use ($request, $data, $user) {
                // picture: file OR string
                if ($request->hasFile('picture')) {
                    $path = $request->file('picture')->store('uploads/saeen-fasads', 'public');
                    $data['picture'] = "storage/{$path}";
                } else {
                    if (array_key_exists('picture', $data) && !is_string($data['picture'])) {
                        unset($data['picture']);
                    }
                }

                $item = SaeenFasad::create($data);
                $item->load(['resource:id,name,code_number,phone_number']);

                // 🔔 Notification: CREATED
                NotificationService::record(
                    $user,
                    module: 'سعین فساد',
                    action: 'created',
                    title: 'نوی ریکارډ ثبت شو',
                    message: 'نوم: ' . ($item->name ?? ''),
                    routeName: 'saeen-fasads',          // ⚠️ adjust to your real route name
                    routeParams: ['id' => $item->id],
                    level: 'success',
                    changes: null
                );

                return (new SaeenFasadResource($item))
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
     * PUT /saeen-fasadas/{saeenFasad}
     * Accepts `picture` either as a FILE or a STRING; replaces file if a new one is uploaded.
     */
    public function update(UpdateSaeenFasadRequest $request, SaeenFasad $saeenFasad)
    {
        $user = $request->user(); // 🔔
        $data = $request->validated();

        try {
            return DB::transaction(function () use ($request, $data, $saeenFasad, $user) {
                // 🧮 build changes BEFORE update
                $original = $saeenFasad->getOriginal();
                $changes  = [];

                foreach ($data as $field => $newValue) {
                    if (array_key_exists($field, $original)) {
                        $oldValue = $original[$field];
                        if ($oldValue != $newValue) {
                            $changes[$field] = ['old' => $oldValue, 'new' => $newValue];
                        }
                    }
                }

                // picture: replace file if uploaded
                if ($request->hasFile('picture')) {
                    if ($saeenFasad->picture && str_starts_with($saeenFasad->picture, 'storage/')) {
                        $old = str_replace('storage/', '', $saeenFasad->picture);
                        Storage::disk('public')->delete($old);
                    }

                    $path = $request->file('picture')->store('uploads/saeen-fasads', 'public');
                    $data['picture'] = "storage/{$path}";

                    // reflect change in changes array
                    $changes['picture'] = [
                        'old' => $original['picture'] ?? null,
                        'new' => $data['picture'],
                    ];
                } else {
                    if (array_key_exists('picture', $data) && !is_string($data['picture'])) {
                        unset($data['picture']);
                    }
                }

                $saeenFasad->update($data);
                $saeenFasad->load(['resource:id,name,code_number,phone_number']);

                // 🔔 Notification: UPDATED + changes
                NotificationService::record(
                    $user,
                    module: 'سعین فساد',
                    action: 'updated',
                    title: 'ریکارډ اصلاح شو',
                    message: 'نوم: ' . ($saeenFasad->name ?? ''),
                    routeName: 'saeen-fasads',          // ⚠️ adjust if needed
                    routeParams: ['id' => $saeenFasad->id],
                    level: 'info',
                    changes: !empty($changes) ? $changes : null
                );

                return new SaeenFasadResource($saeenFasad);
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

    /**
     * DELETE /saeen-fasadas/{saeenFasad}
     */
    public function destroy(Request $request, SaeenFasad $saeenFasad)
    {
        $user = $request->user(); // 🔔

        return DB::transaction(function () use ($saeenFasad, $user) {
            $name = $saeenFasad->name;

            // ✅ Remove the stored file if it lives under /storage
            if ($saeenFasad->picture && str_starts_with($saeenFasad->picture, 'storage/')) {
                $old = str_replace('storage/', '', $saeenFasad->picture);
                Storage::disk('public')->delete($old);
            }

            $saeenFasad->delete();

            // 🔔 Notification: DELETED
            NotificationService::record(
                $user,
                module: 'سعین فساد',
                action: 'deleted',
                title: 'ریکارډ حذف شو',
                message: 'نوم: ' . ($name ?? ''),
                routeName: null,
                routeParams: ['saeen-fasads'],
                level: 'warning',
                changes: null
            );

            return response()->noContent();
        });
    }
}
