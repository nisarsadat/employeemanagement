<?php

namespace App\Http\Controllers\Api\fifthyformatfour;

use App\Http\Controllers\Controller;
use App\Http\Requests\fifthyformatfour\StoreUniversityRequest;
use App\Http\Requests\fifthyformatfour\UpdateUniversityRequest;
use App\Http\Resources\fifthyformatfour\UniversityResource;
use App\Models\fifthyformatfour\University;
use App\Services\NotificationService; // 🔔 notifications
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UniversityController extends Controller
{
    private function isDuplicateKey(QueryException $e): bool
    {
        return (int) ($e->errorInfo[1] ?? 0) === 1062;
    }

    private function duplicateResponse()
    {
        return response()->json([
            'message' => 'دا پوهنتون مخکې نه ثبت شوی دی.',
            'errors'  => [
                'duplicate' => [
                    'د همدې معلوماتو (پوهنتون نوم/ډول + ولایت/زون/ولسوالۍ/کلی/ځانګړی ځای/کوڅه/کور) سره پوهنتون مخکې موجود دی.',
                ],
            ],
        ], 422);
    }

    public function index(Request $request)
    {
        $query = University::query()->with(['resource:id,name,code_number,phone_number']);

        if ($s = trim((string) $request->query('search', ''))) {
            $query->where(function (Builder $q) use ($s) {
                $q->where('uni_name', 'like', "%{$s}%")
                  ->orWhere('uni_type', 'like', "%{$s}%")
                  ->orWhere('responsible_name', 'like', "%{$s}%")
                  ->orWhere('responsible_phone', 'like', "%{$s}%")
                  ->orWhere('province', 'like', "%{$s}%")
                  ->orWhere('district', 'like', "%{$s}%")
                  ->orWhere('village', 'like', "%{$s}%");
            });
        }

        $perPage = $request->query('perPage');
        $page    = $request->query('page');

        $perPageInt = filter_var($perPage, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $pageInt    = filter_var($page, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

        // Fallback → return ALL when missing/invalid
        if ($perPageInt === false || $pageInt === false) {
            return UniversityResource::collection($query->orderByDesc('id')->get());
        }

        $rows = $query->orderByDesc('id')->paginate($perPageInt, ['*'], 'page', $pageInt);
        return UniversityResource::collection($rows);
    }

    public function store(StoreUniversityRequest $request)
    {
        $user = $request->user(); // 🔔

        try {
            return DB::transaction(function () use ($request, $user) {
                $university = University::create($request->validated());
                $university->load(['resource:id,name,code_number,phone_number']);

                // 🔔 Notification: CREATED
                NotificationService::record(
                    $user,
                    module: 'پوهنتون',
                    action: 'created',
                    title: 'نوی پوهنتون ثبت شو',
                    message: 'نوم: ' . ($university->uni_name ?? ''),
                    routeName: 'universities',            // ⚠️ set to your real route name
                    routeParams: ['id' => $university->id],
                    level: 'success',
                    changes: null
                );

                return new UniversityResource($university);
            });
        } catch (QueryException $e) {
            if ($this->isDuplicateKey($e)) {
                return $this->duplicateResponse();
            }
            throw $e;
        }
    }

    public function show(University $university)
    {
        $university->load(['resource:id,name,code_number,phone_number']);
        return new UniversityResource($university);
    }

    public function update(UpdateUniversityRequest $request, University $university)
    {
        $user = $request->user(); // 🔔

        try {
            return DB::transaction(function () use ($request, $university, $user) {
                $original  = $university->getOriginal();
                $validated = $request->validated();
                $changes   = [];

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

                $university->update($validated);
                $university->load(['resource:id,name,code_number,phone_number']);

                // 🔔 Notification: UPDATED
                NotificationService::record(
                    $user,
                    module: 'پوهنتون',
                    action: 'updated',
                    title: 'پوهنتون اصلاح شو',
                    message: 'نوم: ' . ($university->uni_name ?? ''),
                    routeName: 'universities',            // ⚠️ set to your real route name
                    routeParams: ['id' => $university->id],
                    level: 'info',
                    changes: !empty($changes) ? $changes : null
                );

                return new UniversityResource($university);
            });
        } catch (QueryException $e) {
            if ($this->isDuplicateKey($e)) {
                return $this->duplicateResponse();
            }
            throw $e;
        }
    }

    public function destroy(Request $request, University $university)
    {
        $user = $request->user(); // 🔔

        return DB::transaction(function () use ($university, $user) {
            $name = $university->uni_name;

            $university->delete();

            // 🔔 Notification: DELETED
            NotificationService::record(
                $user,
                module: 'پوهنتون',
                action: 'deleted',
                title: 'پوهنتون حذف شو',
                message: 'نوم: ' . ($name ?? ''),
                routeName: null,
                routeParams: ['universities'],
                level: 'warning',
                changes: null
            );

            return response()->json(['message' => 'Deleted']);
        });
    }
}
