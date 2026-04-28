<?php

namespace App\Http\Controllers\Api\fifthyformatfour;

use App\Http\Controllers\Controller;
use App\Http\Requests\fifthyformatfour\StoreComplexRequest;
use App\Http\Requests\fifthyformatfour\UpdateComplexRequest;
use App\Http\Resources\fifthyformatfour\ComplexResource;
use App\Models\fifthyformatfour\Complex;
use App\Services\NotificationService; // 🔔 notifications
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ComplexController extends Controller
{
    private function isDuplicateKey(QueryException $e): bool
    {
        // MySQL duplicate key
        return (int) ($e->errorInfo[1] ?? 0) === 1062;
    }

    private function duplicateResponse()
    {
        return response()->json([
            'message' => 'دا موسسی او خیریه بنسټونه مخکې نه ثبت شوی دی.',
            'errors'  => [
                'duplicate' => [
                    'د همدې ځای او معلوماتو (ولايت/زون/ولسوالۍ/کلی/ځانګړی ځای/کوڅه/کور + نوم/فعالیت ډول/د فعالیت ځای) سره موسسی او خیریه بنسټونه مخکې موجود دی.',
                ],
            ],
        ], 422);
    }

    public function index(Request $request)
    {
        $query = Complex::query()->with(['resource:id,name,code_number,phone_number']);

        $search = trim((string) $request->query('search', ''));
        $searchField = trim((string) $request->query('search_field', ''));
        $searchValue = trim((string) $request->query('search_value', ''));

        $allowedSearchFields = [

                'province','zone','district','village','specefic_place','street_number','home_number',
        'complex_name','activity_type','activity_place','supplier',
        'enter_male_quantity','outer_male_quantity','enter_femal_quantity','outer_femal_quantity',
        'responsible_name',
        'responsible_job',
        'responsible_f_name','responsible_phone','responsible_country',
        'Province_addr',
        'picture',
        'coordinates',
        'guard_quantity','resource_id','note',
        ];

        if ($search !== '' && $searchField === '') {
            $query->where(function (Builder $q) use ($search) {
                $q->where('complex_name', 'like', "%{$search}%")
                  ->orWhere('province', 'like', "%{$search}%")
                  ->orWhere('zone', 'like', "%{$search}%")
                  ->orWhere('district', 'like', "%{$search}%")
                  ->orWhere('village', 'like', "%{$search}%")
                  ->orWhere('specefic_place', 'like', "%{$search}%");
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

        if ($perPageInt === false || $pageInt === false) {
            return ComplexResource::collection($query->orderByDesc('id')->get());
        }

        $rows = $query->orderByDesc('id')->paginate($perPageInt, ['*'], 'page', $pageInt);
        return ComplexResource::collection($rows);
    }

    public function filterOptions(Request $request)
    {
        $field = trim((string) $request->query('field', ''));
        $q = trim((string) $request->query('q', ''));

        $allowedFields = [

                'province','zone','district','village','specefic_place','street_number','home_number',
        'complex_name','activity_type','activity_place','supplier',
        'enter_male_quantity','outer_male_quantity','enter_femal_quantity','outer_femal_quantity',
        'responsible_name',
        'responsible_job',
        'responsible_f_name','responsible_phone','responsible_country',
        'Province_addr',
        'picture',
        'coordinates',
        'guard_quantity','resource_id','note',
        ];

        if ($field === '' || !in_array($field, $allowedFields, true)) {
            return response()->json([
                'data' => [
                    'options' => [],
                ],
            ]);
        }

        $query = Complex::query()
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

    public function store(StoreComplexRequest $request)
    {
        $user = $request->user(); // 🔔 who did this
        $data = $request->validated();

        try {
            return DB::transaction(function () use ($request, $user, $data) {

                // ✅ picture: file OR string
                if ($request->hasFile('picture')) {
                    $path = $request->file('picture')->store('uploads/complexes', 'public');
                    $data['picture'] = "storage/{$path}";
                } else {
                    if (array_key_exists('picture', $data) && !is_string($data['picture'])) {
                        unset($data['picture']);
                    }
                }

                $row = Complex::create($data);
                $row->load(['resource:id,name,code_number,phone_number']);

                // 🔔 Notification: CREATED
                NotificationService::record(
                    $user,
                    module: 'موسسی او خیریه بنسټونه',
                    action: 'created',
                    title: 'نوی موسسی او خیریه بنسټونه ثبت شو',
                    message: 'د موسسی او خیریه بنسټونه نوم: ' . ($row->complex_name ?? ''),
                    routeName: 'complexes',               // ⚠️ set to your real route name
                    routeParams: ['id' => $row->id],
                    level: 'success',
                    changes: null
                );

                return new ComplexResource($row);
            });
        } catch (QueryException $e) {
            if ($this->isDuplicateKey($e)) {
                return $this->duplicateResponse();
            }
            throw $e;
        }
    }

    public function show(Complex $complex)
    {
        $complex->load(['resource:id,name,code_number,phone_number']);
        return new ComplexResource($complex);
    }

    public function update(UpdateComplexRequest $request, Complex $complex)
    {
        $user = $request->user(); // 🔔

        try {
            return DB::transaction(function () use ($request, $complex, $user) {

                $original  = $complex->getOriginal();
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
                    if ($complex->picture && str_starts_with($complex->picture, 'storage/')) {
                        $old = str_replace('storage/', '', $complex->picture);
                        Storage::disk('public')->delete($old);
                    }

                    // store new
                    $path = $request->file('picture')->store('uploads/complexes', 'public');
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

                $complex->update($validated);
                $complex->load(['resource:id,name,code_number,phone_number']);

                // 🔔 Notification: UPDATED
                NotificationService::record(
                    $user,
                    module: 'موسسی او خیریه بنسټونه',
                    action: 'updated',
                    title: 'موسسی او خیریه بنسټونه اصلاح شو',
                    message: 'د موسسی او خیریه بنسټونه نوم: ' . ($complex->complex_name ?? ''),
                    routeName: 'complexes',               // ⚠️ set to your real route name
                    routeParams: ['id' => $complex->id],
                    level: 'info',
                    changes: !empty($changes) ? $changes : null
                );

                return new ComplexResource($complex);
            });
        } catch (QueryException $e) {
            if ($this->isDuplicateKey($e)) {
                return $this->duplicateResponse();
            }
            throw $e;
        }
    }

    public function destroy(Request $request, Complex $complex)
    {
        $user = $request->user(); // 🔔

        return DB::transaction(function () use ($complex, $user) {
            $name = $complex->complex_name;

            // ✅ delete picture file
            if ($complex->picture && str_starts_with($complex->picture, 'storage/')) {
                $old = str_replace('storage/', '', $complex->picture);
                Storage::disk('public')->delete($old);
            }

            $complex->delete();

            // 🔔 Notification: DELETED
            NotificationService::record(
                $user,
                module: 'موسسی او خیریه بنسټونه',
                action: 'deleted',
                title: 'موسسی او خیریه بنسټونه حذف شو',
                message: 'د موسسی او خیریه بنسټونه نوم: ' . ($name ?? ''),
                routeName: null,
                routeParams: ['complexes'],
                level: 'warning',
                changes: null
            );

            return response()->json(['message' => 'Deleted']);
        });
    }
    private function printSearchFields(): array
{
    return [
        'province',
        'zone',
        'district',
        'village',
        'specefic_place',
        'street_number',
        'home_number',

        'complex_name',
        'activity_type',
        'activity_place',
        'supplier',

        'enter_male_quantity',
        'outer_male_quantity',
        'enter_femal_quantity',
        'outer_femal_quantity',

        'responsible_name',
        'responsible_job',
        'responsible_f_name',
        'responsible_phone',
        'responsible_country',

        'Province_addr',
        'picture',
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
    $fiftySixFormatId = $request->query('fifty_six_format_id');
    $resourceId       = $request->query('resource_id');
    $cardFilter = trim((string) $request->query('card_filter', ''));

    $allowedFields = $this->printSearchFields();

    if ($search !== '' && $searchField === '') {
        $query->where(function (Builder $q) use ($search) {
            $q->where('complex_name', 'like', "%{$search}%")
                ->orWhere('activity_type', 'like', "%{$search}%")
                ->orWhere('activity_place', 'like', "%{$search}%")
                ->orWhere('supplier', 'like', "%{$search}%")
                ->orWhere('province', 'like', "%{$search}%")
                ->orWhere('zone', 'like', "%{$search}%")
                ->orWhere('district', 'like', "%{$search}%")
                ->orWhere('village', 'like', "%{$search}%")
                ->orWhere('specefic_place', 'like', "%{$search}%")
                ->orWhere('street_number', 'like', "%{$search}%")
                ->orWhere('home_number', 'like', "%{$search}%")
                ->orWhere('enter_male_quantity', 'like', "%{$search}%")
                ->orWhere('outer_male_quantity', 'like', "%{$search}%")
                ->orWhere('enter_femal_quantity', 'like', "%{$search}%")
                ->orWhere('outer_femal_quantity', 'like', "%{$search}%")
                ->orWhere('responsible_name', 'like', "%{$search}%")
                ->orWhere('responsible_job', 'like', "%{$search}%")
                ->orWhere('responsible_f_name', 'like', "%{$search}%")
                ->orWhere('responsible_phone', 'like', "%{$search}%")
                ->orWhere('responsible_country', 'like', "%{$search}%")
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

    if (is_numeric($fiftySixFormatId) && (int) $fiftySixFormatId > 0) {
        $query->where('id', (int) $fiftySixFormatId);
    }

    if (is_numeric($resourceId) && (int) $resourceId > 0) {
        $query->where('resource_id', (int) $resourceId);
    }

    if ($cardFilter === 'enterMale') {
        $query->whereNotNull('enter_male_quantity')
            ->where('enter_male_quantity', '>', 0);
    } elseif ($cardFilter === 'enterFemale') {
        $query->whereNotNull('enter_femal_quantity')
            ->where('enter_femal_quantity', '>', 0);
    } elseif ($cardFilter === 'outerMale') {
        $query->whereNotNull('outer_male_quantity')
            ->where('outer_male_quantity', '>', 0);
    } elseif ($cardFilter === 'outerFemale') {
        $query->whereNotNull('outer_femal_quantity')
            ->where('outer_femal_quantity', '>', 0);
    }

    return $query;
}

public function printView(Request $request)
{
    $query = Complex::query()
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
    $title           = $request->input('title', 'چاپ کمپلکس');

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
    $complex = Complex::findOrFail($id);

    return $this->removePictureFileAndClear($complex);
}
}