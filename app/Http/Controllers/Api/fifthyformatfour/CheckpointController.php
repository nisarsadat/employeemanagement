<?php

namespace App\Http\Controllers\Api\fifthyformatfour;

use App\Exports\CheckpointExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\fifthyformatfour\StoreCheckpointRequest;
use App\Http\Requests\fifthyformatfour\UpdateCheckpointRequest;
use App\Http\Resources\fifthyformatfour\CheckpointResource;
use App\Models\fifthyformatfour\Checkpoint;
use App\Services\NotificationService; // 🔔 notifications
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class CheckpointController extends Controller
{
    private function isDuplicateKey(QueryException $e): bool
    {
        // MySQL duplicate key code = 1062
        return (int) ($e->errorInfo[1] ?? 0) === 1062;
    }

    private function duplicateResponse()
    {
        return response()->json([
            'message' => 'این چک‌پوینت قبلاً ثبت شده است.',
            'errors' => [
                'duplicate' => [
                    'چک‌پوینت با همین مشخصات (نام، تعداد کارمند، ارگان، دلیل ایجاد، نوع سلاح و آدرس) قبلاً در سیستم موجود است.',
                ],
            ],
        ], 422);
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
    public function index(Request $request)
    {
        $query = Checkpoint::query()->with(['resource:id,name,code_number,phone_number']);

        $search = trim((string) $request->query('search', ''));
        $searchField = trim((string) $request->query('search_field', ''));
        $searchValue = trim((string) $request->query('search_value', ''));

        $allowedSearchFields = [
        'check_point_place',
        'checkpoint_name','employee_quantity',
        'organ','create_reason',
        'officer_name','officer_f_name','officer_phone',
        'gun_type',
        'province','zone','district','village','specefic_place',
        'coordinates',
        'resource_id','note',
        'Province_addr'
        ];

        if ($search !== '' && $searchField === '') {
            $query->where(function (Builder $q) use ($search) {
                $q->where('checkpoint_name', 'like', "%{$search}%")
                    ->orWhere('organ', 'like', "%{$search}%")
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

        // Fallback → return ALL when missing/invalid
        if ($perPageInt === false || $pageInt === false) {
            return CheckpointResource::collection($query->orderByDesc('id')->get());
        }

        $rows = $query->orderByDesc('id')->paginate($perPageInt, ['*'], 'page', $pageInt);
        return CheckpointResource::collection($rows);
    }

    public function filterOptions(Request $request)
    {
        $field = trim((string) $request->query('field', ''));
        $q = trim((string) $request->query('q', ''));

        $allowedFields = [
        'check_point_place',
        'checkpoint_name','employee_quantity',
        'organ','create_reason',
        'officer_name','officer_f_name','officer_phone',
        'gun_type',
        'province','zone','district','village','specefic_place',
        'coordinates',
        'resource_id','note',
        'Province_addr'
        ];

        if ($field === '' || !in_array($field, $allowedFields, true)) {
            return response()->json([
                'data' => [
                    'options' => [],
                ],
            ]);
        }

        $query = Checkpoint::query()
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

    public function store(StoreCheckpointRequest $request)
    {
        $user = $request->user(); // 🔔 who did this
        $data = $request->validated();

        try {
            return DB::transaction(function () use ($request, $data, $user) {

                // ✅ picture: FILE OR STRING (same style as SaeenFasadController)
                if ($request->hasFile('picture')) {
                    $path = $request->file('picture')->store('uploads/checkpoints', 'public');
                    $data['picture'] = "storage/{$path}";
                } else {
                    if (array_key_exists('picture', $data) && !is_string($data['picture'])) {
                        unset($data['picture']);
                    }
                }

                $row = Checkpoint::create($data);
                $row->load(['resource:id,name,code_number,phone_number']);

                // 🔔 Notification: CREATED
                NotificationService::record(
                    $user,
                    module: 'چک‌پوینت',
                    action: 'created',
                    title: 'نوی چک پواینټ ذخیره شو',
                    message: 'چک‌پوینت: ' . ($row->checkpoint_name ?? ''),
                    routeName: 'checkpoints',          // ✅ set your real route name
                    routeParams: ['id' => $row->id],
                    level: 'success',
                    changes: null
                );

                return (new CheckpointResource($row))
                    ->additional(['message' => 'Created'])
                    ->response()
                    ->setStatusCode(201);
            });
        } catch (QueryException $e) {
            if ($this->isDuplicateKey($e)) return $this->duplicateResponse();
            throw $e;
        }
    }

    public function show(Checkpoint $checkpoint)
    {
        $checkpoint->load(['resource:id,name,code_number,phone_number']);
        return CheckpointResource::make($checkpoint);
    }

    public function update(UpdateCheckpointRequest $request, Checkpoint $checkpoint)
    {
        $user = $request->user(); // 🔔
        $data = $request->validated();

        try {
            return DB::transaction(function () use ($request, $checkpoint, $data, $user) {
                // 🧮 build changes BEFORE update
                $original = $checkpoint->getOriginal();
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
                    if ($checkpoint->picture && str_starts_with($checkpoint->picture, 'storage/')) {
                        $old = str_replace('storage/', '', $checkpoint->picture);
                        Storage::disk('public')->delete($old);
                    }

                    $path = $request->file('picture')->store('uploads/checkpoints', 'public');
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

                $checkpoint->update($data);
                $checkpoint->load(['resource:id,name,code_number,phone_number']);

                // 🔔 Notification: UPDATED + changes
                NotificationService::record(
                    $user,
                    module: 'چک‌پوینت',
                    action: 'updated',
                    title: 'چیک پواینټ اصلاح شو',
                    message: 'چک‌پوینت: ' . ($checkpoint->checkpoint_name ?? ''),
                    routeName: 'checkpoints',          // ✅ set your real route name
                    routeParams: ['id' => $checkpoint->id],
                    level: 'info',
                    changes: !empty($changes) ? $changes : null
                );

                return new CheckpointResource($checkpoint);
            });
        } catch (QueryException $e) {
            if ($this->isDuplicateKey($e)) return $this->duplicateResponse();
            throw $e;
        }
    }

    public function destroy(Request $request, Checkpoint $checkpoint)
    {
        $user = $request->user(); // 🔔

        return DB::transaction(function () use ($checkpoint, $user) {
            $name = $checkpoint->checkpoint_name;

            // ✅ delete picture file (same style as SaeenFasadController)
            if ($checkpoint->picture && str_starts_with($checkpoint->picture, 'storage/')) {
                $old = str_replace('storage/', '', $checkpoint->picture);
                Storage::disk('public')->delete($old);
            }

            $checkpoint->delete();

            // 🔔 Notification: DELETED
            NotificationService::record(
                $user,
                module: 'چک‌پوینت',
                action: 'deleted',
                title: 'چک پواینټ حذف شوو',
                message: 'نام چک‌پوینت: ' . ($name ?? ''),
                routeName: null,
                routeParams: ['checkpoints'],
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

        'check_point_place',
        'checkpoint_name',
        'employee_quantity',
        'organ',
        'create_reason',

        'officer_name',
        'officer_f_name',
        'officer_phone',

        'gun_type',

        'province',
        'zone',
        'district',
        'village',
        'specefic_place',
        'Province_addr',

        'coordinates',

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
            $q->where('checkpoint_name', 'like', "%{$search}%")
                ->orWhere('check_point_place', 'like', "%{$search}%")
                ->orWhere('employee_quantity', 'like', "%{$search}%")
                ->orWhere('organ', 'like', "%{$search}%")
                ->orWhere('create_reason', 'like', "%{$search}%")
                ->orWhere('officer_name', 'like', "%{$search}%")
                ->orWhere('officer_f_name', 'like', "%{$search}%")
                ->orWhere('officer_phone', 'like', "%{$search}%")
                ->orWhere('gun_type', 'like', "%{$search}%")
                ->orWhere('province', 'like', "%{$search}%")
                ->orWhere('zone', 'like', "%{$search}%")
                ->orWhere('district', 'like', "%{$search}%")
                ->orWhere('village', 'like', "%{$search}%")
                ->orWhere('specefic_place', 'like', "%{$search}%")
                ->orWhere('Province_addr', 'like', "%{$search}%")
                ->orWhere('coordinates', 'like', "%{$search}%")
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
     * If your checkpoints table has a real fifty_six_format_id column,
     * uncomment this block.
     */
    // if (is_numeric($fiftySixFormatId) && (int) $fiftySixFormatId > 0) {
    //     $query->where('fifty_six_format_id', (int) $fiftySixFormatId);
    // }

    if ($cardFilter === 'withCoordinates') {
        $query->whereNotNull('coordinates')
            ->whereRaw("TRIM(coordinates) != ''");
    } elseif ($cardFilter === 'withOfficerPhone') {
        $query->whereNotNull('officer_phone')
            ->whereRaw("TRIM(officer_phone) != ''");
    } elseif ($cardFilter === 'withResource') {
        $query->whereNotNull('resource_id');
    }

    return $query;
}

public function printView(Request $request)
{
    $query = Checkpoint::query()
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
    $title           = $request->input('title', 'چاپ چک‌پوینت ها');

    return response()->view('prints.table-print', [
        'title'           => $title,
        'rows'            => $rows,
        'selectedColumns' => $selectedColumns,
        'columnLabels'    => $columnLabels,
        'printLabels'     => $printLabels,
        'locale'          => $locale,
    ]);
}
public function deletePicture($id)
{
    $checkpoint = Checkpoint::findOrFail($id);

    return $this->removePictureFileAndClear($checkpoint);
}

public function exportExcel(Request $request)
{
    $search = trim((string) $request->get('search', ''));
    $searchField = $request->get('search_field');
    $searchValue = $request->get('search_value');
    $cardFilter = trim((string) $request->get('card_filter', 'all'));

    $fileName = 'checkpoints_' . now()->format('Y_m_d_H_i_s') . '.xlsx';

    return Excel::download(
        new CheckpointExport([
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