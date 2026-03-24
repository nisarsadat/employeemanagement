<?php

namespace App\Http\Controllers\fifthyformatone;

use App\Http\Controllers\Controller;
use App\Http\Requests\fifthyformatone\StoreMawadRequest;
use App\Http\Requests\fifthyformatone\UpdateMawadRequest;
use App\Http\Resources\fifthyformatone\MawadResource;
use App\Models\fifthyformatone\Mawad;
use App\Services\NotificationService; // 🔔 notifications
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class MawadController extends Controller
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
                    'دا مخدره مواد پلورونکي مخکې ثبت شوی دی (نوع، نوم، د پلار نوم او اصلي پته).',
                ],
            ],
        ];
    }

    private function isDuplicateKeyException(QueryException $e): bool
    {
        return (int) ($e->errorInfo[1] ?? 0) === 1062;
    }

    /**
     * GET /mawads
     */
    public function index(Request $request)
    {
        $perPage = (int) $request->input('perPage', 10);
        $search  = $request->input('search');
        $rid     = $request->input('resource_id');

        $sortBy  = $request->input('sort_by', 'created_at');
        $sortDir = in_array($request->input('sort_dir'), ['asc', 'desc'], true)
            ? $request->input('sort_dir')
            : 'desc';

        $q = Mawad::query()->with([
            'resource' => fn ($qr) => $qr->select('id', 'name', 'code_number', 'phone_number'),
        ]);

        if ($search) {
            $q->where('name', 'like', "%{$search}%");
        }

        if ($rid) {
            $q->where('resource_id', $rid);
        }

        $q->orderBy($sortBy, $sortDir);

        return MawadResource::collection($q->paginate($perPage));
    }

    /**
     * POST /mawads
     */
    public function store(StoreMawadRequest $request)
    {
        $user = $request->user();

        try {
            return DB::transaction(function () use ($request, $user) {
                $data = $request->validated();

                // ✅ STORE picture properly (public disk)
                if ($request->hasFile('picture')) {
                    $data['picture'] = $request->file('picture')->store('mawads', 'public');
                }

                $mawad = Mawad::create($data);

                $mawad->load([
                    'resource' => fn ($qr) => $qr->select('id', 'name', 'code_number', 'phone_number'),
                ]);

                NotificationService::record(
                    $user,
                    module: 'مخدره مواد پلورونکي',
                    action: 'created',
                    title: 'مخدره مواد پلورونکي ثبت شوو',
                    message: 'نوم مخدره مواد پلورونکي: ' . ($mawad->name ?? ''),
                    routeName: 'mawads',
                    routeParams: ['id' => $mawad->id],
                    level: 'success',
                    changes: null
                );

                return (new MawadResource($mawad))
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
     * GET /mawads/{mawad}
     */
    public function show(Mawad $mawad)
    {
        $mawad->load([
            'resource' => fn ($qr) => $qr->select('id', 'name', 'code_number', 'phone_number'),
        ]);

        return MawadResource::make($mawad);
    }

    /**
     * PUT /mawads/{mawad}
     */
    public function update(UpdateMawadRequest $request, Mawad $mawad)
    {
        $user = $request->user();

        try {
            return DB::transaction(function () use ($request, $mawad, $user) {
                $validated = $request->validated();

                // 🧮 changes BEFORE update
                $original = $mawad->getOriginal();
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

                // ✅ UPDATE picture properly + delete old
                if ($request->hasFile('picture')) {
                    if ($mawad->picture) {
                        Storage::disk('public')->delete($mawad->picture);
                    }
                    $validated['picture'] = $request->file('picture')->store('mawads', 'public');
                    $changes['picture'] = ['old' => $original['picture'] ?? null, 'new' => $validated['picture']];
                }

                $mawad->update($validated);

                $mawad->load([
                    'resource' => fn ($qr) => $qr->select('id', 'name', 'code_number', 'phone_number'),
                ]);

                NotificationService::record(
                    $user,
                    module: 'مخدره مواد پلورونکي',
                    action: 'updated',
                    title: 'مخدره مواد پلورونکي اصلاح شوو',
                    message: 'نوم مخدره مواد پلورونکي: ' . ($mawad->name ?? ''),
                    routeName: 'mawads',
                    routeParams: ['id' => $mawad->id],
                    level: 'info',
                    changes: !empty($changes) ? $changes : null
                );

                return MawadResource::make($mawad);
            });
        } catch (QueryException $e) {
            if ($this->isDuplicateKeyException($e)) {
                return response()->json($this->duplicateErrorPashto(), 422);
            }
            throw $e;
        }
    }

    /**
     * DELETE /mawads/{mawad}
     */
    public function destroy(Request $request, Mawad $mawad)
    {
        $user = $request->user();

        return DB::transaction(function () use ($mawad, $user) {
            $name = $mawad->name;

            // ✅ delete picture file too
            if ($mawad->picture) {
                Storage::disk('public')->delete($mawad->picture);
            }

            $mawad->delete();

            NotificationService::record(
                $user,
                module: 'مخدره مواد پلورونکي',
                action: 'deleted',
                title: 'مخدره مواد پلورونکي حذف شوو',
                message: 'نوم مخدره مواد پلورونکي: ' . ($name ?? ''),
                routeName: null,
                routeParams: ['mawads'],
                level: 'warning',
                changes: null
            );

            return response()->noContent();
        });
    }

    /**
     * Optional bulk delete:
     * POST /mawads/bulk-delete  with { "ids": [1,2,3] }
     */
    public function bulkDelete(Request $request)
    {
        $user = $request->user();

        $ids = $request->input('ids', []);
        $ids = is_array($ids) ? array_values(array_filter($ids)) : [];

        return DB::transaction(function () use ($ids, $user) {
            if (empty($ids)) {
                return response()->noContent();
            }

            // ✅ delete pictures for all selected
            $pics = Mawad::whereIn('id', $ids)->pluck('picture')->filter()->values()->all();
            foreach ($pics as $p) {
                Storage::disk('public')->delete($p);
            }

            Mawad::whereIn('id', $ids)->delete();

            NotificationService::record(
                $user,
                module: 'مخدره مواد پلورونکي',
                action: 'deleted',
                title: 'مخدره مواد پلورونکي به‌صورت گروهی حذف شوو',
                message: 'تعداد: ' . count($ids),
                routeName: null,
                routeParams: ['mawads'],
                level: 'warning',
                changes: null
            );

            return response()->noContent();
        });
    }
}
