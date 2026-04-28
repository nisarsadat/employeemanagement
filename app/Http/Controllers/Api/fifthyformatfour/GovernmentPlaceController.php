<?php

namespace App\Http\Controllers\Api\fifthyformatfour;

use App\Http\Controllers\Controller;
use App\Http\Requests\fifthyformatfour\StoreGovernmentPlaceRequest;
use App\Http\Requests\fifthyformatfour\UpdateGovernmentPlaceRequest;
use App\Http\Resources\fifthyformatfour\GovernmentPlaceResource;
use App\Models\fifthyformatfour\GovernmentPlace;
use App\Services\NotificationService; // 🔔 notifications
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class GovernmentPlaceController extends Controller
{
    private function isDuplicateKey(QueryException $e): bool
    {
        // MySQL duplicate key
        return (int) ($e->errorInfo[1] ?? 0) === 1062;
    }

    private function duplicateResponse()
    {
        return response()->json([
            'message' => 'این دولتي ځایونه قبلاً ثبت شده است.',
            'errors' => [
                'duplicate' => [
                    'دولتي ځایونه با همین مشخصات (نام، مسئول، آدرس و سایر مشخصات) قبلاً در سیستم موجود است.',
                ],
            ],
        ], 422);
    }

    /**
     * GET /government-places
     */
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

                'government_place_name','daily_clientele',
        'responsible_name','responsible_f_name','responsible_possition','responsible_phone',
        'province','zone','district','village','specefic_place','coordinates',
        'tafseel','amnyatee_khala','guard_quantity',
        'picture',
        'Province_addr',
        'employee_quantity',
        'resource_id','note',
        ];

        $query = GovernmentPlace::query()
            ->with(['resource:id,name,code_number,phone_number'])
            ->when($search !== '' && $searchField === '', function ($sub) use ($search) {
                $sub->where(function (Builder $w) use ($search) {
                    $w->where('government_place_name', 'like', "%{$search}%")
                        ->orWhere('province', 'like', "%{$search}%")
                        ->orWhere('district', 'like', "%{$search}%")
                        ->orWhere('village', 'like', "%{$search}%");
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
            return GovernmentPlaceResource::collection($rows);
        }

        return GovernmentPlaceResource::collection($query->get());
    }

    /**
     * GET /government-places/filter-options
     */
    public function filterOptions(Request $request)
    {
        $field = trim((string) $request->query('field', ''));
        $q = trim((string) $request->query('q', ''));

        $allowedFields = [

                'government_place_name','daily_clientele',
        'responsible_name','responsible_f_name','responsible_possition','responsible_phone',
        'province','zone','district','village','specefic_place','coordinates',
        'tafseel','amnyatee_khala','guard_quantity',
        'picture',
        'Province_addr',
        'employee_quantity',
        'resource_id','note',
        ];

        if ($field === '' || !in_array($field, $allowedFields, true)) {
            return response()->json([
                'data' => [
                    'options' => [],
                ],
            ]);
        }

        $query = GovernmentPlace::query()
            ->select($field)
            ->whereNotNull($field)
            ->where($field, '!=', '');

        if ($q !== '') {
            $query->where($field, 'like', "%{$q}%");
        }

        $options = $query
            ->distinct()
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
     * POST /government-places
     * Accepts:
     * - picture as FILE or STRING
     */
    public function store(StoreGovernmentPlaceRequest $request)
    {
        $user = $request->user(); // 🔔
        $data = $request->validated();

        try {
            return DB::transaction(function () use ($request, $data, $user) {

                // ✅ picture: FILE OR STRING (same style as SaeenFasadController)
                if ($request->hasFile('picture')) {
                    $path = $request->file('picture')->store('uploads/government-places', 'public');
                    $data['picture'] = "storage/{$path}";
                } else {
                    if (array_key_exists('picture', $data) && !is_string($data['picture'])) {
                        unset($data['picture']);
                    }
                }

                $row = GovernmentPlace::create($data);
                $row->load(['resource:id,name,code_number,phone_number']);

                // 🔔 Notification: CREATED
                NotificationService::record(
                    $user,
                    module: 'دولتي ځایونه',
                    action: 'created',
                    title: 'دولتي ځایونه ثبت شوو',
                    message: 'نوم: ' . ($row->government_place_name ?? ''),
                    routeName: 'government-places',
                    routeParams: ['id' => $row->id],
                    level: 'success',
                    changes: null
                );

                return (new GovernmentPlaceResource($row))
                    ->additional(['message' => 'Created'])
                    ->response()
                    ->setStatusCode(201);
            });
        } catch (QueryException $e) {
            if ($this->isDuplicateKey($e)) return $this->duplicateResponse();
            throw $e;
        }
    }

    /**
     * GET /government-places/{governmentplace}
     */
    public function show(GovernmentPlace $governmentplace)
    {
        $governmentplace->load(['resource:id,name,code_number,phone_number']);
        return GovernmentPlaceResource::make($governmentplace);
    }

    /**
     * PUT /government-places/{governmentplace}
     * Accepts:
     * - picture as FILE or STRING (replaces old file if new uploaded)
     */
    public function update(UpdateGovernmentPlaceRequest $request, GovernmentPlace $governmentplace)
    {
        $user = $request->user(); // 🔔
        $data = $request->validated();

        try {
            return DB::transaction(function () use ($request, $data, $governmentplace, $user) {
                // 🧮 changes BEFORE update
                $original = $governmentplace->getOriginal();
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

                // ✅ picture: replace file if uploaded
                if ($request->hasFile('picture')) {
                    if ($governmentplace->picture && str_starts_with($governmentplace->picture, 'storage/')) {
                        $old = str_replace('storage/', '', $governmentplace->picture);
                        Storage::disk('public')->delete($old);
                    }

                    $path = $request->file('picture')->store('uploads/government-places', 'public');
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

                $governmentplace->update($data);
                $governmentplace->load(['resource:id,name,code_number,phone_number']);

                // 🔔 Notification: UPDATED
                NotificationService::record(
                    $user,
                    module: 'دولتي ځایونه',
                    action: 'updated',
                    title: 'دولتي ځایونه اصلاح شوو',
                    message: 'نوم: ' . ($governmentplace->government_place_name ?? ''),
                    routeName: 'government-places',
                    routeParams: ['id' => $governmentplace->id],
                    level: 'info',
                    changes: !empty($changes) ? $changes : null
                );

                return GovernmentPlaceResource::make($governmentplace);
            });
        } catch (QueryException $e) {
            if ($this->isDuplicateKey($e)) return $this->duplicateResponse();
            throw $e;
        }
    }

    /**
     * DELETE /government-places/{governmentplace}
     */
    public function destroy(Request $request, GovernmentPlace $governmentplace)
    {
        $user = $request->user(); // 🔔

        return DB::transaction(function () use ($governmentplace, $user) {
            $name = $governmentplace->government_place_name;

            // ✅ delete picture file
            if ($governmentplace->picture && str_starts_with($governmentplace->picture, 'storage/')) {
                $old = str_replace('storage/', '', $governmentplace->picture);
                Storage::disk('public')->delete($old);
            }

            $governmentplace->delete();

            // 🔔 Notification: DELETED
            NotificationService::record(
                $user,
                module: 'دولتي ځایونه',
                action: 'deleted',
                title: 'دولتي ځایونه حذف شوو',
                message: 'نوم: ' . ($name ?? ''),
                routeName: null,
                routeParams: ['government-places'],
                level: 'warning',
                changes: null
            );

            return response()->noContent();
        });
    }

    /**
     * Optional bulk delete (if you need it)
     * POST /government-places/bulk-delete  with { "ids": [1,2,3] }
     */
    public function bulkDelete(Request $request)
    {
        $ids = (array) $request->input('ids', []);

        // ✅ delete pictures for selected rows (safe cleanup)
        $rows = GovernmentPlace::query()->whereIn('id', $ids)->get();
        foreach ($rows as $row) {
            if ($row->picture && str_starts_with($row->picture, 'storage/')) {
                $old = str_replace('storage/', '', $row->picture);
                Storage::disk('public')->delete($old);
            }
        }

        GovernmentPlace::destroy($ids);

        return response()->noContent();
    }
    private function printSearchFields(): array
{
    return [
        'government_place_name',
        'daily_clientele',

        'responsible_name',
        'responsible_f_name',
        'responsible_possition',
        'responsible_phone',

        'province',
        'zone',
        'district',
        'village',
        'specefic_place',
        'coordinates',

        'tafseel',
        'amnyatee_khala',
        'employee_quantity',
        'guard_quantity',

        'picture',
        'Province_addr',

        'resource_id',
        'resource_code',
        'resource_phone',

        'note',
    ];
}

private function applyPrintFilters($query, Request $request)
{
    $search           = trim((string) $request->query('search', ''));
    $searchField      = trim((string) $request->query('search_field', ''));
    $searchValue      = trim((string) $request->query('search_value', ''));
    $fiftySixFormatId = $request->query('fifty_six_format_id');
    $resourceId       = $request->query('resource_id');
    $cardFilter       = trim((string) $request->query('card_filter', 'all'));

    $allowedFields = $this->printSearchFields();

    if ($search !== '' && $searchField === '') {
        $query->where(function (Builder $w) use ($search) {
            $w->where('government_place_name', 'like', "%{$search}%")
                ->orWhere('daily_clientele', 'like', "%{$search}%")
                ->orWhere('responsible_name', 'like', "%{$search}%")
                ->orWhere('responsible_f_name', 'like', "%{$search}%")
                ->orWhere('responsible_possition', 'like', "%{$search}%")
                ->orWhere('responsible_phone', 'like', "%{$search}%")
                ->orWhere('province', 'like', "%{$search}%")
                ->orWhere('zone', 'like', "%{$search}%")
                ->orWhere('district', 'like', "%{$search}%")
                ->orWhere('village', 'like', "%{$search}%")
                ->orWhere('specefic_place', 'like', "%{$search}%")
                ->orWhere('coordinates', 'like', "%{$search}%")
                ->orWhere('tafseel', 'like', "%{$search}%")
                ->orWhere('amnyatee_khala', 'like', "%{$search}%")
                ->orWhere('employee_quantity', 'like', "%{$search}%")
                ->orWhere('guard_quantity', 'like', "%{$search}%")
                ->orWhere('Province_addr', 'like', "%{$search}%")
                ->orWhere('note', 'like', "%{$search}%")
                ->orWhereHas('resource', function ($r) use ($search) {
                    $r->where('code_number', 'like', "%{$search}%")
                        ->orWhere('phone_number', 'like', "%{$search}%");
                });
        });
    }

    if ($searchField !== '' && in_array($searchField, $allowedFields, true)) {
        $value = $searchValue !== '' ? $searchValue : $search;

        if ($value !== '') {
            if ($searchField === 'resource_code') {
                $query->whereHas('resource', function ($r) use ($value) {
                    $r->where('code_number', 'like', "%{$value}%");
                });
            } elseif ($searchField === 'resource_phone') {
                $query->whereHas('resource', function ($r) use ($value) {
                    $r->where('phone_number', 'like', "%{$value}%");
                });
            } else {
                $query->where($searchField, 'like', "%{$value}%");
            }
        }
    }

    if (is_numeric($fiftySixFormatId) && (int) $fiftySixFormatId > 0) {
        $query->where('id', (int) $fiftySixFormatId);
    }

    if (is_numeric($resourceId) && (int) $resourceId > 0) {
        $query->where('resource_id', (int) $resourceId);
    }

    if ($cardFilter === 'withCoordinates') {
        $query->whereNotNull('coordinates')
            ->whereRaw("TRIM(coordinates) != ''");
    } elseif ($cardFilter === 'withClients') {
        $query->whereNotNull('daily_clientele')
            ->where('daily_clientele', '>', 0);
    } elseif ($cardFilter === 'withGuards') {
        $query->whereNotNull('guard_quantity')
            ->where('guard_quantity', '>', 0);
    }

    return $query;
}

public function printView(Request $request)
{
    $query = GovernmentPlace::query()
        ->with(['resource:id,name,code_number,phone_number']);

    $this->applyPrintFilters($query, $request);

    $rows = $query
        ->orderByDesc('id')
        ->get()
        ->map(function ($row) {
            $row->resource_code =
                $row->resource?->code_number
                ?? $row->resource_id
                ?? '-';

            $row->resource_phone =
                $row->resource?->phone_number
                ?? '-';

            // Your table header uses resource_id, so print shows resource code there
            $row->resource_id =
                $row->resource?->code_number
                ?? $row->resource_id
                ?? '-';

            return $row;
        });

    $selectedColumns = $request->input('selected_columns', []);
    $columnLabels    = $request->input('column_labels', []);
    $printLabels     = $request->input('print_labels', []);
    $locale          = $request->input('locale', 'fa');
    $title           = $request->input('title', 'چاپ ادارات دولتی');

    return response()->view('prints.table-print', [
        'title'           => $title,
        'rows'            => $rows,
        'selectedColumns' => $selectedColumns,
        'columnLabels'    => $columnLabels,
        'printLabels'     => $printLabels,
        'locale'          => $locale,
    ]);
}
private function removePictureFileAndClear($model)
{
    if (!$model->picture) {
        return response()->json([
            'success' => false,
            'message' => 'عکس نشته',
        ], 404);
    }

    if (Storage::disk('public')->exists($model->picture)) {
        Storage::disk('public')->delete($model->picture);
    }

    $model->update([
        'picture' => null,
    ]);

    return response()->json([
        'success' => true,
        'message' => 'عکس حذف شو',
        'data' => $model->fresh(),
    ]);
}
  public function deletePicture($id)
{
    $governmentPlace = GovernmentPlace::findOrFail($id);

    return $this->removePictureFileAndClear($governmentPlace);
}
}