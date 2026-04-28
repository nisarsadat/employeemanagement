<?php

namespace App\Http\Controllers\Api\fifthyformatfour;

use App\Http\Controllers\Controller;
use App\Http\Requests\fifthyformatfour\StorePumpRequest;
use App\Http\Requests\fifthyformatfour\UpdatePumpRequest;
use App\Http\Resources\fifthyformatfour\PumpResource;
use App\Models\fifthyformatfour\Pump;
use App\Services\NotificationService; // 🔔 notifications
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PumpController extends Controller
{
    private function isDuplicateKey(QueryException $e): bool
    {
        return (int) ($e->errorInfo[1] ?? 0) === 1062; // MySQL duplicate key
    }

    private function duplicateResponse()
    {
        return response()->json([
            'message' => 'این پمپونه قبلاً ثبت شده است.',
            'errors' => [
                'duplicate' => [
                    'پمپونه با همین مشخصات (نام پمپ، نوع، مالک و ولایت/زون/ولسوالی/قریه) قبلاً در سیستم موجود است.',
                ],
            ],
        ], 422);
    }

    public function index(Request $request)
    {
        $query = Pump::query()->with(['resource:id,name,code_number,phone_number']);

        $search = trim((string) $request->query('search', ''));
        $searchField = trim((string) $request->query('search_field', ''));
        $searchValue = trim((string) $request->query('search_value', ''));

        $allowedSearchFields = [

                'pump_name','pump_type','main_owner',
        'responsible_name','responsible_f_name','responsible_possition','responsible_phone',
        'province','zone','district','village','specefic_place',
        'picture',
        'coordinates','employee_quantity','guard_quantity',
        'Province_addr',
        'resource_id','note',
        ];

        if ($search !== '' && $searchField === '') {
            $query->where(function (Builder $q) use ($search) {
                $q->where('pump_name', 'like', "%{$search}%")
                    ->orWhere('main_owner', 'like', "%{$search}%")
                    ->orWhere('province', 'like', "%{$search}%")
                    ->orWhere('village', 'like', "%{$search}%");
            });
        }

        if ($searchField !== '' && in_array($searchField, $allowedSearchFields, true)) {
            $value = $searchValue !== '' ? $searchValue : $search;

            if ($value !== '') {
                $query->where($searchField, 'like', "%{$value}%");
            }
        }

        $perPage = $request->query('perPage');
        $page    = $request->query('page');
        $perPageInt = filter_var($perPage, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $pageInt    = filter_var($page, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

        // Fallback → return ALL when invalid/missing
        if ($perPageInt === false || $pageInt === false) {
            return PumpResource::collection($query->orderByDesc('id')->get());
        }

        $rows = $query->orderByDesc('id')->paginate($perPageInt, ['*'], 'page', $pageInt);
        return PumpResource::collection($rows);
    }

    public function filterOptions(Request $request)
    {
        $field = trim((string) $request->query('field', ''));
        $q = trim((string) $request->query('q', ''));

        $allowedFields = [

                'pump_name','pump_type','main_owner',
        'responsible_name','responsible_f_name','responsible_possition','responsible_phone',
        'province','zone','district','village','specefic_place',
        'picture',
        'coordinates','employee_quantity','guard_quantity',
        'Province_addr',
        'resource_id','note',
        ];

        if ($field === '' || !in_array($field, $allowedFields, true)) {
            return response()->json([
                'data' => [
                    'options' => [],
                ],
            ]);
        }

        $query = Pump::query()
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
     * POST /pumps
     * Accepts:
     * - picture as FILE or STRING
     */
    public function store(StorePumpRequest $request)
    {
        $user = $request->user(); // 🔔
        $data = $request->validated();

        try {
            return DB::transaction(function () use ($request, $data, $user) {

                // ✅ picture: FILE OR STRING (same style as SaeenFasadController)
                if ($request->hasFile('picture')) {
                    $path = $request->file('picture')->store('uploads/pumps', 'public');
                    $data['picture'] = "storage/{$path}";
                } else {
                    if (array_key_exists('picture', $data) && !is_string($data['picture'])) {
                        unset($data['picture']);
                    }
                }

                $row = Pump::create($data);
                $row->load(['resource:id,name,code_number,phone_number']);

                // 🔔 Notification: CREATED
                NotificationService::record(
                    $user,
                    module: 'پمپونه',
                    action: 'created',
                    title: 'پمپونه جدید ثبت شوو',
                    message: 'نوم: ' . ($row->pump_name ?? ''),
                    routeName: 'pumps',               // ✅ set your real route name
                    routeParams: ['id' => $row->id],
                    level: 'success',
                    changes: null
                );

                return (new PumpResource($row))
                    ->additional(['message' => 'Created'])
                    ->response()
                    ->setStatusCode(201);
            });
        } catch (QueryException $e) {
            if ($this->isDuplicateKey($e)) return $this->duplicateResponse();
            throw $e;
        }
    }

    public function show(Pump $pump)
    {
        $pump->load(['resource:id,name,code_number,phone_number']);
        return PumpResource::make($pump);
    }

    /**
     * PUT /pumps/{pump}
     * Accepts:
     * - picture as FILE or STRING (replaces old file if new uploaded)
     */
    public function update(UpdatePumpRequest $request, Pump $pump)
    {
        $user = $request->user(); // 🔔
        $data = $request->validated();

        try {
            return DB::transaction(function () use ($request, $pump, $data, $user) {
                // 🧮 changes BEFORE update
                $original = $pump->getOriginal();
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
                    if ($pump->picture && str_starts_with($pump->picture, 'storage/')) {
                        $old = str_replace('storage/', '', $pump->picture);
                        Storage::disk('public')->delete($old);
                    }

                    $path = $request->file('picture')->store('uploads/pumps', 'public');
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

                $pump->update($data);
                $pump->load(['resource:id,name,code_number,phone_number']);

                // 🔔 Notification: UPDATED
                NotificationService::record(
                    $user,
                    module: 'پمپونه',
                    action: 'updated',
                    title: 'پمپونه اصلاح شوو',
                    message: 'نوم : ' . ($pump->pump_name ?? ''),
                    routeName: 'pumps',               // ✅ set your real route name
                    routeParams: ['id' => $pump->id],
                    level: 'info',
                    changes: !empty($changes) ? $changes : null
                );

                return new PumpResource($pump);
            });
        } catch (QueryException $e) {
            if ($this->isDuplicateKey($e)) return $this->duplicateResponse();
            throw $e;
        }
    }

    public function destroy(Request $request, Pump $pump)
    {
        $user = $request->user(); // 🔔

        return DB::transaction(function () use ($pump, $user) {
            $name = $pump->pump_name;

            // ✅ delete picture file
            if ($pump->picture && str_starts_with($pump->picture, 'storage/')) {
                $old = str_replace('storage/', '', $pump->picture);
                Storage::disk('public')->delete($old);
            }

            $pump->delete();

            // 🔔 Notification: DELETED
            NotificationService::record(
                $user,
                module: 'پمپونه',
                action: 'deleted',
                title: 'پمپونه حذف شوو',
                message: 'نوم: ' . ($name ?? ''),
                routeName: null,
                routeParams: ['pumps'],
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

        'pump_name',
        'pump_type',
        'main_owner',

        'responsible_name',
        'responsible_f_name',
        'responsible_possition',
        'responsible_phone',

        'province',
        'zone',
        'district',
        'village',
        'specefic_place',
        'Province_addr',

        'coordinates',
        'employee_quantity',
        'guard_quantity',

        'resource_id',
        'resource_code',
        'resource_phone',

        'note',
    ];
}

private function applyPrintFilters($query, Request $request)
{
    $search      = trim((string) $request->query('search', ''));
    $searchField = trim((string) $request->query('search_field', ''));
    $searchValue = trim((string) $request->query('search_value', ''));
    $cardFilter  = trim((string) $request->query('card_filter', 'all'));

    $allowedFields = $this->printSearchFields();

    if ($search !== '' && $searchField === '') {
        $query->where(function (Builder $q) use ($search) {
            $q->where('pump_name', 'like', "%{$search}%")
                ->orWhere('pump_type', 'like', "%{$search}%")
                ->orWhere('main_owner', 'like', "%{$search}%")
                ->orWhere('responsible_name', 'like', "%{$search}%")
                ->orWhere('responsible_f_name', 'like', "%{$search}%")
                ->orWhere('responsible_possition', 'like', "%{$search}%")
                ->orWhere('responsible_phone', 'like', "%{$search}%")
                ->orWhere('province', 'like', "%{$search}%")
                ->orWhere('zone', 'like', "%{$search}%")
                ->orWhere('district', 'like', "%{$search}%")
                ->orWhere('village', 'like', "%{$search}%")
                ->orWhere('specefic_place', 'like', "%{$search}%")
                ->orWhere('Province_addr', 'like', "%{$search}%")
                ->orWhere('coordinates', 'like', "%{$search}%")
                ->orWhere('employee_quantity', 'like', "%{$search}%")
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

    if ($cardFilter === 'withCoordinates') {
        $query->whereNotNull('coordinates')
            ->whereRaw("TRIM(coordinates) != ''");
    } elseif ($cardFilter === 'withPhone') {
        $query->whereNotNull('responsible_phone')
            ->whereRaw("TRIM(responsible_phone) != ''");
    } elseif ($cardFilter === 'withGuards') {
        $query->whereNotNull('guard_quantity')
            ->where('guard_quantity', '>', 0);
    }

    return $query;
}

public function printView(Request $request)
{
    $query = Pump::query()
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
    $title           = $request->input('title', 'چاپ پمپ‌ها');

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
    $pump = Pump::findOrFail($id);

    return $this->removePictureFileAndClear($pump);
}
}