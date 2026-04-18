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
use Illuminate\Support\Facades\Storage;

class UniversityController extends Controller
{
    private function isDuplicateKey(QueryException $e): bool
    {
        return (int) ($e->errorInfo[1] ?? 0) === 1062;
    }

    private function duplicateResponse()
    {
        return response()->json([
            'message' => 'دا پوهنتونونه مخکې نه ثبت شوي دي.',
            'errors'  => [
                'duplicate' => [
                    'د همدې معلوماتو (د پوهنتون نوم/ډول + ولایت/زون/ولسوالۍ/کلی/ځانګړی ځای/کوڅه/کور) سره پوهنتونونه مخکې موجود دي.',
                ],
            ],
        ], 422);
    }

    public function index(Request $request)
    {
        $page    = $request->query('page');
        $perPage = $request->query('perPage', $request->query('per_page'));

        $isValidPage    = is_numeric($page) && (int) $page > 0;
        $isValidPerPage = is_numeric($perPage) && (int) $perPage > 0;

        $search = trim((string) $request->query('search', ''));
        $searchField = trim((string) $request->query('search_field', ''));
        $searchValue = trim((string) $request->query('search_value', ''));

        $allowedSearchFields = [

                'uni_name','uni_type',
        'responsible_name','responsible_f_name','responsible_position','responsible_phone',
        'male_teachers_quantity','female_teachers_quantity',
        'picture',
        'male_students_quantity','female_students_quantity',
        'province','zone','district','village','specific_place','street_number','home_number',
        'Province_addr',
        'coordinates',
        'resource_id',
        'note',
        ];

        $query = University::query()
            ->with(['resource:id,name,code_number,phone_number'])
            ->when($search !== '' && $searchField === '', function (Builder $sub) use ($search) {
                $sub->where(function (Builder $q) use ($search) {
                    $q->where('uni_name', 'like', "%{$search}%")
                        ->orWhere('uni_type', 'like', "%{$search}%")
                        ->orWhere('responsible_name', 'like', "%{$search}%")
                        ->orWhere('responsible_phone', 'like', "%{$search}%")
                        ->orWhere('province', 'like', "%{$search}%")
                        ->orWhere('district', 'like', "%{$search}%")
                        ->orWhere('village', 'like', "%{$search}%");
                });
            })
            ->when(
                $searchField !== '' && in_array($searchField, $allowedSearchFields, true),
                function (Builder $sub) use ($searchField, $searchValue, $search) {
                    $value = $searchValue !== '' ? $searchValue : $search;

                    if ($value !== '') {
                        $sub->where($searchField, 'like', "%{$value}%");
                    }
                }
            )
            ->orderByDesc('id');

        if ($isValidPage && $isValidPerPage) {
            $rows = $query->paginate((int) $perPage, ['*'], 'page', (int) $page);
            return UniversityResource::collection($rows);
        }

        return UniversityResource::collection($query->get());
    }

    /**
     * GET /universities/filter-options
     */
    public function filterOptions(Request $request)
    {
        $field = trim((string) $request->query('field', ''));
        $q = trim((string) $request->query('q', ''));

        $allowedFields = [

                'uni_name','uni_type',
        'responsible_name','responsible_f_name','responsible_position','responsible_phone',
        'male_teachers_quantity','female_teachers_quantity',
        'picture',
        'male_students_quantity','female_students_quantity',
        'province','zone','district','village','specific_place','street_number','home_number',
        'Province_addr',
        'coordinates',
        'resource_id',
        'note',
        ];

        if ($field === '' || !in_array($field, $allowedFields, true)) {
            return response()->json([
                'data' => [
                    'options' => [],
                ],
            ]);
        }

        $query = University::query()
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
     * POST /universities
     * Accepts:
     * - picture as FILE or STRING
     */
    public function store(StoreUniversityRequest $request)
    {
        $user = $request->user(); // 🔔
        $data = $request->validated();

        try {
            return DB::transaction(function () use ($request, $user, $data) {

                // ✅ picture: file OR string
                if ($request->hasFile('picture')) {
                    $path = $request->file('picture')->store('uploads/universities', 'public');
                    $data['picture'] = "storage/{$path}";
                } else {
                    if (array_key_exists('picture', $data) && !is_string($data['picture'])) {
                        unset($data['picture']);
                    }
                }

                $university = University::create($data);
                $university->load(['resource:id,name,code_number,phone_number']);

                // 🔔 Notification: CREATED
                NotificationService::record(
                    $user,
                    module: 'پوهنتونونه',
                    action: 'created',
                    title: 'نوی پوهنتونونه  شو',
                    message: 'نوم: ' . ($university->uni_name ?? ''),
                    routeName: 'universities',
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

    /**
     * PUT /universities/{university}
     * Accepts:
     * - picture as FILE or STRING (replaces old file if new uploaded)
     */
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

                // ✅ picture: replace file if uploaded
                if ($request->hasFile('picture')) {

                    // delete old
                    if ($university->picture && str_starts_with($university->picture, 'storage/')) {
                        $old = str_replace('storage/', '', $university->picture);
                        Storage::disk('public')->delete($old);
                    }

                    // store new
                    $path = $request->file('picture')->store('uploads/universities', 'public');
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

                $university->update($validated);
                $university->load(['resource:id,name,code_number,phone_number']);

                // 🔔 Notification: UPDATED
                NotificationService::record(
                    $user,
                    module: 'پوهنتونونه',
                    action: 'updated',
                    title: 'پوهنتونونه اصلاح شو',
                    message: 'نوم: ' . ($university->uni_name ?? ''),
                    routeName: 'universities',
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

            // ✅ delete picture file
            if ($university->picture && str_starts_with($university->picture, 'storage/')) {
                $old = str_replace('storage/', '', $university->picture);
                Storage::disk('public')->delete($old);
            }

            $university->delete();

            // 🔔 Notification: DELETED
            NotificationService::record(
                $user,
                module: 'پوهنتونونه',
                action: 'deleted',
                title: 'پوهنتونونه حذف شو',
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