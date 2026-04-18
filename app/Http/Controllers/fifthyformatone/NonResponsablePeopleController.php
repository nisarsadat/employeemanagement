<?php

namespace App\Http\Controllers\fifthyformatone;

use App\Http\Controllers\Controller;
use App\Http\Requests\fifthyformatone\StoreNonResponsablePeopleRequest;
use App\Http\Requests\fifthyformatone\UpdateNonResponsablePeopleRequest;
use App\Http\Resources\fifthyformatone\NonResponsablePeopleResource;
use App\Models\fifthyformatone\NonResponsablePeople;
use App\Services\NotificationService;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class NonResponsablePeopleController extends Controller
{
    /**
     * Pashto duplicate error
     */
    private function duplicateErrorPashto(): array
    {
        return [
            'message' => 'دا کس مخکې ثبت شوی دی. مهرباني وکړئ تکراري معلومات مه داخلوی.',
            'errors'  => [
                'duplicate' => [
                    'همدا نوم، د پلار نوم او اصلي پته مخکې ثبت شوې ده.',
                ],
            ],
        ];
    }

    private function isDuplicateKeyException(QueryException $e): bool
    {
        return (int) ($e->errorInfo[1] ?? 0) === 1062;
    }

    /** GET /non-responsable-people */
    public function index(Request $request)
    {
        $page    = $request->query('page');
        $perPage = $request->query('perPage', $request->query('per_page', 15));

        $isValidPage    = is_numeric($page) && (int) $page > 0;
        $isValidPerPage = is_numeric($perPage) && (int) $perPage > 0;

        $search = trim((string) $request->query('search', ''));
        $fiftySixFormatId = $request->query('fifty_six_format_id');
        $searchField = trim((string) $request->query('search_field', ''));
        $searchValue = trim((string) $request->query('search_value', ''));

        $allowedSearchFields = [
             'information_id',

        'nick_name',
        'name',
        'picture',
        'father_name',
        'main_province',
        'main_district',
        'main_village',
        'now_province',
        'now_zone',
        'now_district',
        'now_village',
        'now_specific_place',
        'now_street_number',
        'now_home_number',
        'friends_quantity',
        'Province_addr',
        'supplier',
        'phone_number',
        'history_jorm',
        'activity_place',
        'old_job',
        'now_job',
        'gun_type',
        'tanzim',
        'takhalof_type',
        'note',
        'resource_id',
        'coordinates',
        ];

        $q = NonResponsablePeople::query()
            ->with(['resource:id,name,code_number,phone_number'])
            ->when($search !== '' && $searchField === '', function ($sub) use ($search) {
                $sub->where(function ($w) use ($search) {
                    $w->where('name', 'like', "%{$search}%")
                        ->orWhere('nick_name', 'like', "%{$search}%")
                        ->orWhere('father_name', 'like', "%{$search}%")
                        ->orWhere('phone_number', 'like', "%{$search}%")
                        ->orWhere('old_job', 'like', "%{$search}%")
                        ->orWhere('now_job', 'like', "%{$search}%");
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
            ->when(
                is_numeric($fiftySixFormatId) && (int) $fiftySixFormatId > 0,
                function ($sub) use ($fiftySixFormatId) {
                    $sub->where('id', (int) $fiftySixFormatId);
                }
            )
            ->latest();

        if ($isValidPage && $isValidPerPage) {
            return NonResponsablePeopleResource::collection(
                $q->paginate((int) $perPage, ['*'], 'page', (int) $page)
            );
        }

        return NonResponsablePeopleResource::collection($q->get());
    }

    /**
     * GET /non-responsable-people/filter-options
     */
    public function filterOptions(Request $request)
    {
        $field = trim((string) $request->query('field', ''));
        $q = trim((string) $request->query('q', ''));

        $allowedFields = [
             'information_id',

        'nick_name',
        'name',
        'picture',
        'father_name',
        'main_province',
        'main_district',
        'main_village',
        'now_province',
        'now_zone',
        'now_district',
        'now_village',
        'now_specific_place',
        'now_street_number',
        'now_home_number',
        'friends_quantity',
        'Province_addr',
        'supplier',
        'phone_number',
        'history_jorm',
        'activity_place',
        'old_job',
        'now_job',
        'gun_type',
        'tanzim',
        'takhalof_type',
        'note',
        'resource_id',
        'coordinates',
        ];

        if ($field === '' || !in_array($field, $allowedFields, true)) {
            return response()->json([
                'data' => [
                    'options' => [],
                ],
            ]);
        }

        $query = NonResponsablePeople::query()
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

    /** POST /non-responsable-people */
    public function store(StoreNonResponsablePeopleRequest $request)
    {
        $user = $request->user();

        try {
            return DB::transaction(function () use ($request, $user) {
                $validated = $request->validated();

                // ✅ handle picture upload
                if ($request->hasFile('picture')) {
                    $validated['picture'] = $request->file('picture')
                        ->store('non_responsable_people', 'public');
                }

                $record = NonResponsablePeople::create($validated);

                $record->load(['resource:id,name,code_number,phone_number']);

                NotificationService::record(
                    $user,
                    module: 'غیر مسوله وسلوال',
                    action: 'created',
                    title: 'کس ثبت شوو',
                    message: 'نام: ' . ($record->name ?? ''),
                    routeName: 'non-responsable-people',
                    routeParams: ['id' => $record->id],
                    level: 'success',
                    changes: null
                );

                return (new NonResponsablePeopleResource($record))
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

    /** GET /non-responsable-people/{id} */
    public function show($id)
    {
        $record = NonResponsablePeople::with(['resource:id,name,code_number,phone_number'])
            ->findOrFail($id);

        return NonResponsablePeopleResource::make($record);
    }

    /** PUT /non-responsable-people/{id} */
    public function update(UpdateNonResponsablePeopleRequest $request, $id)
    {
        $user = $request->user();

        try {
            return DB::transaction(function () use ($request, $id, $user) {
                $record = NonResponsablePeople::findOrFail($id);

                $validated = $request->validated();
                $original  = $record->getOriginal();
                $changes   = [];

                // ✅ handle picture upload (replace + delete old)
                if ($request->hasFile('picture')) {
                    if ($record->picture) {
                        Storage::disk('public')->delete($record->picture);
                    }
                    $validated['picture'] = $request->file('picture')
                        ->store('non_responsable_people', 'public');
                }

                foreach ($validated as $field => $newValue) {
                    if (array_key_exists($field, $original) && $original[$field] != $newValue) {
                        $changes[$field] = [
                            'old' => $original[$field],
                            'new' => $newValue,
                        ];
                    }
                }

                $record->update($validated);
                $record->load(['resource:id,name,code_number,phone_number']);

                NotificationService::record(
                    $user,
                    module: 'غیر مسوله وسلوال',
                    action: 'updated',
                    title: 'کس اصلاح شوو',
                    message: 'نام: ' . ($record->name ?? ''),
                    routeName: 'non-responsable-people',
                    routeParams: ['id' => $record->id],
                    level: 'info',
                    changes: !empty($changes) ? $changes : null
                );

                return NonResponsablePeopleResource::make($record);
            });
        } catch (QueryException $e) {
            if ($this->isDuplicateKeyException($e)) {
                return response()->json($this->duplicateErrorPashto(), 422);
            }
            throw $e;
        }
    }

    /** DELETE /non-responsable-people/{id} */
    public function destroy(Request $request, $id)
    {
        $user   = $request->user();
        $record = NonResponsablePeople::findOrFail($id);
        $name   = $record->name;

        // ✅ delete picture file too
        if ($record->picture) {
            Storage::disk('public')->delete($record->picture);
        }

        $record->delete();

        NotificationService::record(
            $user,
            module: 'غیر مسوله وسلوال',
            action: 'deleted',
            title: 'کس حذف شوو',
            message: 'نام: ' . ($name ?? ''),
            routeName: null,
            routeParams: ['non-responsable-people'],
            level: 'warning',
            changes: null
        );

        return response()->noContent();
    }
}