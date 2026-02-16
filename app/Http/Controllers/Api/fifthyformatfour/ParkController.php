<?php

namespace App\Http\Controllers\Api\fifthyformatfour;

use App\Http\Controllers\Controller;
use App\Http\Requests\fifthyformatfour\StoreParkRequest;
use App\Http\Requests\fifthyformatfour\UpdateParkRequest;
use App\Http\Resources\fifthyformatfour\ParkResource;
use App\Models\fifthyformatfour\Park;
use App\Services\NotificationService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ParkController extends Controller
{
    private function isDuplicateKey(QueryException $e): bool
    {
        return (int) ($e->errorInfo[1] ?? 0) === 1062;
    }

    private function duplicateResponse()
    {
        return response()->json([
            'message' => 'این پارک قبلاً ثبت شده است.',
            'errors' => [
                'duplicate' => [
                    'پارک با همین مشخصات (نام، نوع و ولایت/زون/قریه) قبلاً در سیستم موجود است.',
                ],
            ],
        ], 422);
    }

    /**
     * GET /parks
     */
    public function index(Request $request)
    {
        $query = Park::query()
            ->with(['resource:id,name,code_number,phone_number']);

        if ($s = trim((string) $request->query('search', ''))) {
            $query->where(function (Builder $q) use ($s) {
                $q->where('park_name', 'like', "%$s%")
                  ->orWhere('province', 'like', "%$s%")
                  ->orWhere('village', 'like', "%$s%");
            });
        }

        $perPage = $request->query('perPage');
        $page    = $request->query('page');

        $perPageInt = filter_var($perPage, FILTER_VALIDATE_INT, ['options'=>['min_range'=>1]]);
        $pageInt    = filter_var($page,    FILTER_VALIDATE_INT, ['options'=>['min_range'=>1]]);

        if ($perPageInt === false || $pageInt === false) {
            return ParkResource::collection($query->orderByDesc('id')->get());
        }

        $rows = $query->orderByDesc('id')->paginate($perPageInt, ['*'], 'page', $pageInt);

        return ParkResource::collection($rows);
    }

    /**
     * POST /parks
     */
    public function store(StoreParkRequest $request)
    {
        $user = $request->user();

        try {
            return DB::transaction(function () use ($request, $user) {

                $data = $request->validated();

                // ✅ Picture upload
                if ($request->hasFile('picture')) {
                    $path = $request->file('picture')->store('uploads/parks', 'public');
                    $data['picture'] = "storage/{$path}";
                } else {
                    if (array_key_exists('picture', $data) && !is_string($data['picture'])) {
                        unset($data['picture']);
                    }
                }

                $row = Park::create($data);

                $row->load(['resource:id,name,code_number,phone_number']);

                NotificationService::record(
                    $user,
                    module: 'پارک',
                    action: 'created',
                    title: 'پارک جدید ثبت شد',
                    message: 'نام پارک: ' . ($row->park_name ?? ''),
                    routeName: 'parks',
                    routeParams: ['id' => $row->id],
                    level: 'success',
                    changes: null
                );

                return new ParkResource($row);
            });

        } catch (QueryException $e) {
            if ($this->isDuplicateKey($e)) return $this->duplicateResponse();
            throw $e;
        }
    }

    /**
     * GET /parks/{park}
     */
    public function show(Park $park)
    {
        $park->load(['resource:id,name,code_number,phone_number']);
        return new ParkResource($park);
    }

    /**
     * PUT /parks/{park}
     */
    public function update(UpdateParkRequest $request, Park $park)
    {
        $user = $request->user();

        try {
            return DB::transaction(function () use ($request, $park, $user) {

                $original  = $park->getOriginal();
                $validated = $request->validated();
                $changes   = [];

                // Detect field changes
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

                // ✅ Replace picture if new uploaded
                if ($request->hasFile('picture')) {

                    if ($park->picture && str_starts_with($park->picture, 'storage/')) {
                        $oldPath = str_replace('storage/', '', $park->picture);
                        Storage::disk('public')->delete($oldPath);
                    }

                    $path = $request->file('picture')->store('uploads/parks', 'public');
                    $validated['picture'] = "storage/{$path}";

                    $changes['picture'] = [
                        'old' => $original['picture'] ?? null,
                        'new' => $validated['picture'],
                    ];
                } else {
                    if (array_key_exists('picture', $validated) && !is_string($validated['picture'])) {
                        unset($validated['picture']);
                    }
                }

                $park->update($validated);

                $park->load(['resource:id,name,code_number,phone_number']);

                NotificationService::record(
                    $user,
                    module: 'پارک',
                    action: 'updated',
                    title: 'پارک اصلاح شد',
                    message: 'نام پارک: ' . ($park->park_name ?? ''),
                    routeName: 'parks',
                    routeParams: ['id' => $park->id],
                    level: 'info',
                    changes: !empty($changes) ? $changes : null
                );

                return new ParkResource($park);
            });

        } catch (QueryException $e) {
            if ($this->isDuplicateKey($e)) return $this->duplicateResponse();
            throw $e;
        }
    }

    /**
     * DELETE /parks/{park}
     */
    public function destroy(Request $request, Park $park)
    {
        $user = $request->user();

        return DB::transaction(function () use ($park, $user) {

            $name = $park->park_name;

            // ✅ Delete picture file
            if ($park->picture && str_starts_with($park->picture, 'storage/')) {
                $oldPath = str_replace('storage/', '', $park->picture);
                Storage::disk('public')->delete($oldPath);
            }

            $park->delete();

            NotificationService::record(
                $user,
                module: 'پارک',
                action: 'deleted',
                title: 'پارک حذف شد',
                message: 'نام پارک: ' . ($name ?? ''),
                routeName: null,
                routeParams: ['parks'],
                level: 'warning',
                changes: null
            );

            return response()->json(['message' => 'Deleted']);
        });
    }
}
