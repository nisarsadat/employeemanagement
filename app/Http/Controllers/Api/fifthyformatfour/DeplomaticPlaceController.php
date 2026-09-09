<?php

namespace App\Http\Controllers\Api\fifthyformatfour;

use App\Http\Controllers\Controller;
use App\Http\Requests\fifthyformatfour\StoreDeplomaticPlaceRequest;
use App\Http\Requests\fifthyformatfour\UpdateDeplomaticPlaceRequest;
use App\Http\Resources\fifthyformatfour\DeplomaticPlaceResource;
use App\Models\fifthyformatfour\DeplomaticPlace;
use App\Services\NotificationService; // 🔔 notifications
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\DeplomaticPlaceExport;

class DeplomaticPlaceController extends Controller
{
    private function isDuplicateKey(QueryException $e): bool
    {
        // MySQL duplicate key
        return (int) ($e->errorInfo[1] ?? 0) === 1062;
    }

    private function duplicateResponse()
    {
        return response()->json([
            'message' => 'دا ډیپلوماټیک ځای مخکې نه ثبت شوی دی.',
            'errors'  => [
                'duplicate' => [
                    'د همدې ځای او معلوماتو (نوم/فعالیت ډول/د فعالیت ځای + ولایت/زون/ولسوالۍ/کلی/ځانګړی ځای/کوڅه/کور) سره ریکارډ مخکې موجود دی.',
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
        'deplomatic_place_name','activity_type','activity_place',
        'enter_male_quantity','outer_male_quantity','enter_female_quantity','outer_female_quantity',
        'responsible_name','responsible_f_name','responsible_possition','responsible_country','responsible_phone',
        'province','zone','district','village','specefic_place','street_number','home_number',
        'supplier_country',
        'picture',
        'Province_addr',
        'guard_quantity','coordinates','resource_id','note',
        ];

        $query = DeplomaticPlace::query()
            ->with(['resource:id,name,code_number,phone_number'])
            ->when($search !== '' && $searchField === '', function (Builder $sub) use ($search) {
                $sub->where(function (Builder $q) use ($search) {
                    $q->where('deplomatic_place_name', 'like', "%{$search}%")
                        ->orWhere('province', 'like', "%{$search}%")
                        ->orWhere('zone', 'like', "%{$search}%")
                        ->orWhere('district', 'like', "%{$search}%")
                        ->orWhere('village', 'like', "%{$search}%")
                        ->orWhere('specefic_place', 'like', "%{$search}%");
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
            return DeplomaticPlaceResource::collection($rows);
        }

        return DeplomaticPlaceResource::collection($query->get());
    }

    /**
     * GET /deplomaticplaces/filter-options
     */
    public function filterOptions(Request $request)
    {
        $field = trim((string) $request->query('field', ''));
        $q = trim((string) $request->query('q', ''));

        $allowedFields = [
        'deplomatic_place_name','activity_type','activity_place',
        'enter_male_quantity','outer_male_quantity','enter_female_quantity','outer_female_quantity',
        'responsible_name','responsible_f_name','responsible_possition','responsible_country','responsible_phone',
        'province','zone','district','village','specefic_place','street_number','home_number',
        'supplier_country',
        'picture',
        'Province_addr',
        'guard_quantity','coordinates','resource_id','note',
        ];

        if ($field === '' || !in_array($field, $allowedFields, true)) {
            return response()->json([
                'data' => [
                    'options' => [],
                ],
            ]);
        }

        $query = DeplomaticPlace::query()
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

    public function store(StoreDeplomaticPlaceRequest $request)
    {
        $user = $request->user(); // 🔔 who did this
        $data = $request->validated();

        try {
            return DB::transaction(function () use ($request, $user, $data) {

                // ✅ picture: file OR string
                if ($request->hasFile('picture')) {
                    $path = $request->file('picture')->store('uploads/deplomaticplaces', 'public');
                    $data['picture'] = "storage/{$path}";
                } else {
                    if (array_key_exists('picture', $data) && !is_string($data['picture'])) {
                        unset($data['picture']);
                    }
                }

                $row = DeplomaticPlace::create($data);
                $row->load(['resource:id,name,code_number,phone_number']);

                // 🔔 Notification: CREATED
                NotificationService::record(
                    $user,
                    module: 'ډیپلوماټیک ځای',
                    action: 'created',
                    title: 'نوی ډیپلوماټیک ځای ثبت شو',
                    message: 'نوم: ' . ($row->deplomatic_place_name ?? ''),
                    routeName: 'deplomaticplaces',          // ⚠️ set to your real route name
                    routeParams: ['id' => $row->id],
                    level: 'success',
                    changes: null
                );

                return new DeplomaticPlaceResource($row);
            });
        } catch (QueryException $e) {
            if ($this->isDuplicateKey($e)) {
                return $this->duplicateResponse();
            }
            throw $e;
        }
    }

    public function show(DeplomaticPlace $deplomaticplace)
    {
        $deplomaticplace->load(['resource:id,name,code_number,phone_number']);
        return new DeplomaticPlaceResource($deplomaticplace);
    }

    public function update(UpdateDeplomaticPlaceRequest $request, DeplomaticPlace $deplomaticplace)
    {
        $user = $request->user(); // 🔔

        try {
            return DB::transaction(function () use ($request, $deplomaticplace, $user) {

                $original  = $deplomaticplace->getOriginal();
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
                    if ($deplomaticplace->picture && str_starts_with($deplomaticplace->picture, 'storage/')) {
                        $old = str_replace('storage/', '', $deplomaticplace->picture);
                        Storage::disk('public')->delete($old);
                    }

                    // store new
                    $path = $request->file('picture')->store('uploads/deplomaticplaces', 'public');
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

                $deplomaticplace->update($validated);
                $deplomaticplace->load(['resource:id,name,code_number,phone_number']);

                // 🔔 Notification: UPDATED
                NotificationService::record(
                    $user,
                    module: 'ډیپلوماټیک ځای',
                    action: 'updated',
                    title: 'ډیپلوماټیک ځای اصلاح شو',
                    message: 'نوم: ' . ($deplomaticplace->deplomatic_place_name ?? ''),
                    routeName: 'deplomaticplaces',          // ⚠️ set to your real route name
                    routeParams: ['id' => $deplomaticplace->id],
                    level: 'info',
                    changes: !empty($changes) ? $changes : null
                );

                return new DeplomaticPlaceResource($deplomaticplace);
            });
        } catch (QueryException $e) {
            if ($this->isDuplicateKey($e)) {
                return $this->duplicateResponse();
            }
            throw $e;
        }
    }

    public function destroy(Request $request, DeplomaticPlace $deplomaticplace)
    {
        $user = $request->user(); // 🔔

        return DB::transaction(function () use ($deplomaticplace, $user) {
            $name = $deplomaticplace->deplomatic_place_name;

            // ✅ delete picture file
            if ($deplomaticplace->picture && str_starts_with($deplomaticplace->picture, 'storage/')) {
                $old = str_replace('storage/', '', $deplomaticplace->picture);
                Storage::disk('public')->delete($old);
            }

            $deplomaticplace->delete();

            // 🔔 Notification: DELETED
            NotificationService::record(
                $user,
                module: 'ډیپلوماټیک ځای',
                action: 'deleted',
                title: 'ډیپلوماټیک ځای حذف شو',
                message: 'نوم: ' . ($name ?? ''),
                routeName: null,
                routeParams: ['deplomaticplaces'],
                level: 'warning',
                changes: null
            );

            return response()->json(['message' => 'Deleted']);
        });
    }
    private function printSearchFields(): array
{
    return [
        'picture',

        'deplomatic_place_name',
        'activity_type',
        'activity_place',
        'supplier_country',

        'enter_male_quantity',
        'outer_male_quantity',
        'enter_female_quantity',
        'outer_female_quantity',

        'responsible_name',
        'responsible_f_name',
        'responsible_possition',
        'responsible_country',
        'responsible_phone',

        'province',
        'zone',
        'district',
        'village',
        'specefic_place',
        'street_number',
        'home_number',
        'Province_addr',

        'coordinates',
        'guard_quantity',

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
    $cardFilter       = trim((string) $request->query('card_filter', 'all'));
    $fiftySixFormatId = $request->query('fifty_six_format_id');

    $allowedFields = $this->printSearchFields();

    if ($search !== '' && $searchField === '') {
        $query->where(function (Builder $q) use ($search) {
            $q->where('deplomatic_place_name', 'like', "%{$search}%")
                ->orWhere('activity_type', 'like', "%{$search}%")
                ->orWhere('activity_place', 'like', "%{$search}%")
                ->orWhere('supplier_country', 'like', "%{$search}%")
                ->orWhere('enter_male_quantity', 'like', "%{$search}%")
                ->orWhere('outer_male_quantity', 'like', "%{$search}%")
                ->orWhere('enter_female_quantity', 'like', "%{$search}%")
                ->orWhere('outer_female_quantity', 'like', "%{$search}%")
                ->orWhere('responsible_name', 'like', "%{$search}%")
                ->orWhere('responsible_f_name', 'like', "%{$search}%")
                ->orWhere('responsible_possition', 'like', "%{$search}%")
                ->orWhere('responsible_country', 'like', "%{$search}%")
                ->orWhere('responsible_phone', 'like', "%{$search}%")
                ->orWhere('province', 'like', "%{$search}%")
                ->orWhere('zone', 'like', "%{$search}%")
                ->orWhere('district', 'like', "%{$search}%")
                ->orWhere('village', 'like', "%{$search}%")
                ->orWhere('specefic_place', 'like', "%{$search}%")
                ->orWhere('street_number', 'like', "%{$search}%")
                ->orWhere('home_number', 'like', "%{$search}%")
                ->orWhere('Province_addr', 'like', "%{$search}%")
                ->orWhere('coordinates', 'like', "%{$search}%")
                ->orWhere('guard_quantity', 'like', "%{$search}%")
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

    /**
     * If your deplomatic_places table has a real fifty_six_format_id column,
     * uncomment this block.
     */
    // if (is_numeric($fiftySixFormatId) && (int) $fiftySixFormatId > 0) {
    //     $query->where('fifty_six_format_id', (int) $fiftySixFormatId);
    // }

    if ($cardFilter === 'enterMale') {
        $query->whereNotNull('enter_male_quantity')
            ->where('enter_male_quantity', '>', 0);
    } elseif ($cardFilter === 'enterFemale') {
        $query->whereNotNull('enter_female_quantity')
            ->where('enter_female_quantity', '>', 0);
    } elseif ($cardFilter === 'outerMale') {
        $query->whereNotNull('outer_male_quantity')
            ->where('outer_male_quantity', '>', 0);
    } elseif ($cardFilter === 'outerFemale') {
        $query->whereNotNull('outer_female_quantity')
            ->where('outer_female_quantity', '>', 0);
    }

    return $query;
}

public function printView(Request $request)
{
    $query = DeplomaticPlace::query()
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

            return $row;
        });

    $selectedColumns = $request->input('selected_columns', []);
    $columnLabels    = $request->input('column_labels', []);
    $printLabels     = $request->input('print_labels', []);
    $locale          = $request->input('locale', 'fa');
    $title           = $request->input('title', 'چاپ محلات دیپلوماتیک');

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
    $deplomaticPlace = DeplomaticPlace::findOrFail($id);

    return $this->removePictureFileAndClear($deplomaticPlace);
}

public function exportExcel(Request $request)
{
    $search = trim((string) $request->get('search', ''));
    $searchField = $request->get('search_field');
    $searchValue = $request->get('search_value');
    $cardFilter = trim((string) $request->get('card_filter', 'all'));

    $fileName = 'deplomatic_places_' . now()->format('Y_m_d_H_i_s') . '.xlsx';

    return Excel::download(
        new DeplomaticPlaceExport([
            'search'       => $search,
            'search_field' => $searchField,
            'search_value' => $searchValue,
            'card_filter'  => $cardFilter,
            'resource_id'  => $request->get('resource_id'),
        ]),
        $fileName
    );
}
}