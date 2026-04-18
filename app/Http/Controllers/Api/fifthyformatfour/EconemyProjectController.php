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
use Illuminate\Support\Facades\Storage;

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
            'message' => 'این د اسلامي امارت مهم تاسیسات قبلاً ثبت شده است.',
            'errors' => [
                'duplicate' => [
                    'پروژه با همین مشخصات (نام، نوع و ولایت/زون/ولسوالی/قریه) قبلاً در سیستم موجود است.',
                ],
            ],
        ], 422);
    }

    public function index(Request $request)
    {
        $page    = $request->query('page');
        $perPage = $request->query('perPage', $request->query('per_page', $request->query('itemsPerPage')));

        $isValidPage    = is_numeric($page) && (int) $page > 0;
        $isValidPerPage = is_numeric($perPage) && (int) $perPage > 0;

        $search = trim((string) $request->query('search', ''));
        $searchField = trim((string) $request->query('search_field', ''));
        $searchValue = trim((string) $request->query('search_value', ''));

        $allowedSearchFields = [

                'coordinates',
        'name','type',
        'responsiable_name','responsiable_f_name','responsible_possition','responsible_phone',
        'province','zone','district','village','specefic_place',
        'amnyatee_khala','employee_quantity','gaurd_quantity',
        'Province_addr',
        'picture',
        'resource_id','note',
        ];

        $query = EconemyProject::query()
            ->with(['resource:id,name,code_number,phone_number'])
            ->when($search !== '' && $searchField === '', function ($sub) use ($search) {
                $sub->where(function (Builder $q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('responsiable_name', 'like', "%{$search}%")
                        ->orWhere('province', 'like', "%{$search}%")
                        ->orWhere('district', 'like', "%{$search}%");
                });
            })
            ->when(
                $searchField !== '' && in_array($searchField, $allowedSearchFields, true),
                function ($sub) use ($searchField, $searchValue, $search) {
                    $value = $searchValue !== '' ? $searchValue : $search;

                    if ($value !== '') {
                        $sub->where($searchField, 'like', "%{$value}%");
                    }
                }
            )
            ->orderByDesc('id');

        if ($isValidPage && $isValidPerPage) {
            $rows = $query->paginate((int) $perPage, ['*'], 'page', (int) $page);
            return EconemyProjectResource::collection($rows);
        }

        return EconemyProjectResource::collection($query->get());
    }

    /**
     * GET /econemy-projects/filter-options
     */
    public function filterOptions(Request $request)
    {
        $field = trim((string) $request->query('field', ''));
        $q = trim((string) $request->query('q', ''));

        $allowedFields = [

                'coordinates',
        'name','type',
        'responsiable_name','responsiable_f_name','responsible_possition','responsible_phone',
        'province','zone','district','village','specefic_place',
        'amnyatee_khala','employee_quantity','gaurd_quantity',
        'Province_addr',
        'picture',
        'resource_id','note',
        ];

        if ($field === '' || !in_array($field, $allowedFields, true)) {
            return response()->json([
                'data' => [
                    'options' => [],
                ],
            ]);
        }

        $query = EconemyProject::query()
            ->select($field)
            ->whereNotNull($field)
            ->where($field, '!=', '');

        if ($q !== '') {
            $query->where($field, 'like', "%{$q}%");
        }

        $options = $query
            ->distinct()
            ->limit(50)
            ->pluck($field)
            ->filter(fn ($v) => $v !== null && trim((string) $v) !== '')
            ->values()
            ->map(fn ($v) => [
                'title' => (string) $v,
                'value' => (string) $v,
            ]);

        return response()->json([
            'data' => [
                'options' => $options,
            ],
        ]);
    }

    /**
     * POST /econemy-projects
     * Accepts:
     * - picture as FILE or STRING
     */
    public function store(StoreEconemyProjectRequest $request)
    {
        $user = $request->user(); // 🔔
        $data = $request->validated();

        try {
            return DB::transaction(function () use ($request, $data, $user) {

                // ✅ picture: FILE OR STRING (same style as SaeenFasadController)
                if ($request->hasFile('picture')) {
                    $path = $request->file('picture')->store('uploads/econemy-projects', 'public');
                    $data['picture'] = "storage/{$path}";
                } else {
                    if (array_key_exists('picture', $data) && !is_string($data['picture'])) {
                        unset($data['picture']);
                    }
                }

                $row = EconemyProject::create($data);
                $row->load(['resource:id,name,code_number,phone_number']);

                // 🔔 Notification: CREATED
                NotificationService::record(
                    $user,
                    module: 'د اسلامي امارت مهم تاسیسات',
                    action: 'created',
                    title: 'د اسلامي امارت مهم تاسیسات  ثبت شوو',
                    message: 'نوم: ' . ($row->name ?? ''),
                    routeName: 'econemy-projects',
                    routeParams: ['id' => $row->id],
                    level: 'success',
                    changes: null
                );

                return (new EconemyProjectResource($row))
                    ->additional(['message' => 'Created'])
                    ->response()
                    ->setStatusCode(201);
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

    /**
     * PUT /econemy-projects/{econemyProject}
     * Accepts:
     * - picture as FILE or STRING (replaces old file if new uploaded)
     */
    public function update(UpdateEconemyProjectRequest $request, EconemyProject $econemyProject)
    {
        $user = $request->user(); // 🔔
        $data = $request->validated();

        try {
            return DB::transaction(function () use ($request, $econemyProject, $data, $user) {
                // 🧮 changes BEFORE update
                $original = $econemyProject->getOriginal();
                $changes  = [];

                foreach ($data as $field => $newValue) {
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

                // ✅ picture: replace file if uploaded (same style as SaeenFasadController)
                if ($request->hasFile('picture')) {
                    if ($econemyProject->picture && str_starts_with($econemyProject->picture, 'storage/')) {
                        $old = str_replace('storage/', '', $econemyProject->picture);
                        Storage::disk('public')->delete($old);
                    }

                    $path = $request->file('picture')->store('uploads/econemy-projects', 'public');
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

                $econemyProject->update($data);
                $econemyProject->load(['resource:id,name,code_number,phone_number']);

                // 🔔 Notification: UPDATED + changes
                NotificationService::record(
                    $user,
                    module: 'د اسلامي امارت مهم تاسیسات',
                    action: 'updated',
                    title: 'د اسلامي امارت مهم تاسیسات اصلاح شوو',
                    message: 'نوم: ' . ($econemyProject->name ?? ''),
                    routeName: 'econemy-projects',
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

            // ✅ delete picture file (same style as SaeenFasadController)
            if ($econemyProject->picture && str_starts_with($econemyProject->picture, 'storage/')) {
                $old = str_replace('storage/', '', $econemyProject->picture);
                Storage::disk('public')->delete($old);
            }

            $econemyProject->delete();

            // 🔔 Notification: DELETED
            NotificationService::record(
                $user,
                module: 'د اسلامي امارت مهم تاسیسات',
                action: 'deleted',
                title: 'د اسلامي امارت مهم تاسیسات حذف شوو',
                message: 'نوم: ' . ($name ?? ''),
                routeName: null,
                routeParams: ['econemy-projects'],
                level: 'warning',
                changes: null
            );

            return response()->json(['message' => 'Deleted']);
        });
    }
}