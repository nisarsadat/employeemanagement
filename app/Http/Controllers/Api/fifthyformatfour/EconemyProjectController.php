<?php

namespace App\Http\Controllers\Api\fifthyformatfour;

use App\Http\Controllers\Controller;
use App\Http\Requests\fifthyformatfour\StoreEconemyProjectRequest;
use App\Http\Requests\fifthyformatfour\UpdateEconemyProjectRequest;
use App\Http\Resources\fifthyformatfour\EconemyProjectResource;
use App\Models\fifthyformatfour\EconemyProject;
use App\Services\NotificationService; // 🔔 notifications
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EconemyProjectController extends Controller
{
    private function isDuplicateKey(QueryException $e): bool
    {
        // MySQL duplicate key code = 1062
        return (int) ($e->errorInfo[1] ?? 0) === 1062;
    }

    private function duplicateResponse()
    {
        return response()->json([
            'message' => 'این پروژه اقتصادی قبلاً ثبت شده است.',
            'errors' => [
                'duplicate' => [
                    'پروژه با همین مشخصات (نام، نوع و ولایت/زون/ولسوالی/قریه) قبلاً در سیستم موجود است.',
                ],
            ],
        ], 422);
    }

    public function index(Request $request)
    {
        $query = EconemyProject::query()
            ->with(['resource:id,name,code_number,phone_number']);

        // 🔍 Search
        if ($s = trim((string) $request->query('search', ''))) {
            $query->where(function (Builder $q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                  ->orWhere('responsiable_name', 'like', "%{$s}%")
                  ->orWhere('province', 'like', "%{$s}%")
                  ->orWhere('district', 'like', "%{$s}%");
            });
        }

        // 📄 Pagination (supports itemsPerPage from Vue + perPage fallback)
        $perPageRaw = $request->query('itemsPerPage', $request->query('perPage'));
        $pageRaw    = $request->query('page');

        $perPageInt = filter_var($perPageRaw, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $pageInt    = filter_var($pageRaw,    FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

        // If no valid pagination → return ALL (for your 100000 pattern)
        if ($perPageInt === false || $pageInt === false) {
            $rows = $query->orderByDesc('id')->get();
            return EconemyProjectResource::collection($rows);
        }

        // Normal paginated response
        $rows = $query
            ->orderByDesc('id')
            ->paginate($perPageInt, ['*'], 'page', $pageInt);

        return EconemyProjectResource::collection($rows);
    }

    public function store(StoreEconemyProjectRequest $request)
    {
        $user = $request->user(); // 🔔

        try {
            return DB::transaction(function () use ($request, $user) {
                $row = EconemyProject::create($request->validated());
                $row->load(['resource:id,name,code_number,phone_number']);

                // 🔔 Notification: CREATED
                NotificationService::record(
                    $user,
                    module: 'پروژه اقتصادی',
                    action: 'created',
                    title: 'پروژه اقتصادی جدید ثبت شد',
                    message: 'نام پروژه: ' . ($row->name ?? ''),
                    routeName: 'econemy-projects',        // ✅ set your real route name
                    routeParams: ['id' => $row->id],
                    level: 'success',
                    changes: null
                );

                return new EconemyProjectResource($row);
            });
        } catch (QueryException $e) {
            if ($this->isDuplicateKey($e)) return $this->duplicateResponse();
            throw $e;
        }
    }

    public function show(EconemyProject $econemyProject)
    {
        $econemyProject->load(['resource:id,name,code_number,phone_number']);
        return new EconemyProjectResource($econemyProject);
    }

    public function update(UpdateEconemyProjectRequest $request, EconemyProject $econemyProject)
    {
        $user = $request->user(); // 🔔

        try {
            return DB::transaction(function () use ($request, $econemyProject, $user) {
                // 🧮 changes BEFORE update (like InformationController)
                $original  = $econemyProject->getOriginal();
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

                $econemyProject->update($validated);
                $econemyProject->load(['resource:id,name,code_number,phone_number']);

                // 🔔 Notification: UPDATED + changes
                NotificationService::record(
                    $user,
                    module: 'پروژه اقتصادی',
                    action: 'updated',
                    title: 'پروژه اقتصادی اصلاح شد',
                    message: 'نام پروژه: ' . ($econemyProject->name ?? ''),
                    routeName: 'econemy-projects',        // ✅ set your real route name
                    routeParams: ['id' => $econemyProject->id],
                    level: 'info',
                    changes: !empty($changes) ? $changes : null
                );

                return new EconemyProjectResource($econemyProject);
            });
        } catch (QueryException $e) {
            if ($this->isDuplicateKey($e)) return $this->duplicateResponse();
            throw $e;
        }
    }

    public function destroy(Request $request, EconemyProject $econemyProject)
    {
        $user = $request->user(); // 🔔

        return DB::transaction(function () use ($econemyProject, $user) {
            $name = $econemyProject->name;

            $econemyProject->delete();

            // 🔔 Notification: DELETED
            NotificationService::record(
                $user,
                module: 'پروژه اقتصادی',
                action: 'deleted',
                title: 'پروژه اقتصادی حذف شد',
                message: 'نام پروژه: ' . ($name ?? ''),
                routeName: null,
                routeParams: ['econemy-projects'],
                level: 'warning',
                changes: null
            );

            return response()->json(['message' => 'Deleted']);
        });
    }
}
