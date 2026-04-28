<?php

namespace App\Http\Controllers\Api\fifthyformatfour;

use App\Http\Controllers\Controller;
use App\Http\Requests\fifthyformatfour\StoreCompanyRequest;
use App\Http\Requests\fifthyformatfour\UpdateCompanyRequest;
use App\Http\Resources\fifthyformatfour\CompanyResource;
use App\Models\fifthyformatfour\Company;
use App\Services\NotificationService; // 🔔 notifications
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CompanyController extends Controller
{
    private function isDuplicateKey(QueryException $e): bool
    {
        // MySQL duplicate key code = 1062
        return (int) ($e->errorInfo[1] ?? 0) === 1062;
    }

    private function duplicateResponse()
    {
        return response()->json([
            'message' => 'این شرکتونه او فابریکې قبلاً ثبت شده است.',
            'errors' => [
                'duplicate' => [
                    'شرکتونه او فابریکې با همین مشخصات (نام، نوع، نوع محصول و ولایت/زون/ولسوالی/قریه) قبلاً در سیستم موجود است.',
                ],
            ],
        ], 422);
    }

    public function index(Request $request)
    {
        $query = Company::query()->with(['resource:id,name,code_number,phone_number']);

        $search = trim((string) $request->query('search', ''));
        $searchField = trim((string) $request->query('search_field', ''));
        $searchValue = trim((string) $request->query('search_value', ''));

        $allowedSearchFields = [

                'com_name','com_type','product_type','com_level','main_owner',
        'responsible_name','responsible_f_name','responsible_position','responsible_phone',
        'com_province','com_zone','com_district','com_village','com_specific_place','com_coordinates',
        'male_employee_quantity_inner','female_employee_quantity_inner',
        'male_employee_quantity_out','female_employee_quantity_out','guard_quantity',
        'resource_id','note',
        'Province_addr',
        ];

        if ($search !== '' && $searchField === '') {
            $query->where(function (Builder $q) use ($search) {
                $q->where('com_name', 'like', "%{$search}%")
                    ->orWhere('product_type', 'like', "%{$search}%")
                    ->orWhere('com_district', 'like', "%{$search}%")
                    ->orWhere('com_village', 'like', "%{$search}%");
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

        // Fallback → return all when missing/invalid
        if ($perPageInt === false || $pageInt === false) {
            return CompanyResource::collection($query->orderByDesc('id')->get());
        }

        $rows = $query->orderByDesc('id')->paginate($perPageInt, ['*'], 'page', $pageInt);
        return CompanyResource::collection($rows);
    }

    public function filterOptions(Request $request)
    {
        $field = trim((string) $request->query('field', ''));
        $q = trim((string) $request->query('q', ''));

        $allowedFields = [

                'com_name','com_type','product_type','com_level','main_owner',
        'responsible_name','responsible_f_name','responsible_position','responsible_phone',
        'com_province','com_zone','com_district','com_village','com_specific_place','com_coordinates',
        'male_employee_quantity_inner','female_employee_quantity_inner',
        'male_employee_quantity_out','female_employee_quantity_out','guard_quantity',
        'resource_id','note',
        'Province_addr',
        ];

        if ($field === '' || !in_array($field, $allowedFields, true)) {
            return response()->json([
                'data' => [
                    'options' => [],
                ],
            ]);
        }

        $query = Company::query()
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
     * POST /companies
     * Accepts:
     * - picture as FILE or STRING
     */
    public function store(StoreCompanyRequest $request)
    {
        $user = $request->user(); // 🔔 who did this
        $data = $request->validated();

        try {
            return DB::transaction(function () use ($request, $data, $user) {

                // ✅ picture: FILE OR STRING (same style as SaeenFasadController)
                if ($request->hasFile('picture')) {
                    $path = $request->file('picture')->store('uploads/companies', 'public');
                    $data['picture'] = "storage/{$path}";
                } else {
                    if (array_key_exists('picture', $data) && !is_string($data['picture'])) {
                        unset($data['picture']);
                    }
                }

                $row = Company::create($data);
                $row->load(['resource:id,name,code_number,phone_number']);

                // 🔔 Notification: CREATED
                NotificationService::record(
                    $user,
                    module: 'شرکتونه او فابریکې',
                    action: 'created',
                    title: 'شرکتونه او فابریکې  ثبت شوو',
                    message: 'نام شرکتونه او فابریکې: ' . ($row->com_name ?? ''),
                    routeName: 'companies',           // ✅ set your real route name
                    routeParams: ['id' => $row->id],
                    level: 'success',
                    changes: null
                );

                return (new CompanyResource($row))
                    ->additional(['message' => 'Created'])
                    ->response()
                    ->setStatusCode(201);
            });
        } catch (QueryException $e) {
            if ($this->isDuplicateKey($e)) return $this->duplicateResponse();
            throw $e;
        }
    }

    public function show(Company $company)
    {
        $company->load(['resource:id,name,code_number,phone_number']);
        return new CompanyResource($company);
    }

    /**
     * PUT /companies/{company}
     * Accepts:
     * - picture as FILE or STRING (replaces old file if new uploaded)
     */
    public function update(UpdateCompanyRequest $request, Company $company)
    {
        $user = $request->user(); // 🔔
        $data = $request->validated();

        try {
            return DB::transaction(function () use ($request, $company, $data, $user) {
                // 🧮 build changes BEFORE update
                $original = $company->getOriginal();
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
                    if ($company->picture && str_starts_with($company->picture, 'storage/')) {
                        $old = str_replace('storage/', '', $company->picture);
                        Storage::disk('public')->delete($old);
                    }

                    $path = $request->file('picture')->store('uploads/companies', 'public');
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

                $company->update($data);
                $company->load(['resource:id,name,code_number,phone_number']);

                // 🔔 Notification: UPDATED + changes
                NotificationService::record(
                    $user,
                    module: 'شرکتونه او فابریکې',
                    action: 'updated',
                    title: 'شرکتونه او فابریکې اصلاح شوو',
                    message: 'شرکتونه او فابریکې: ' . ($company->com_name ?? ''),
                    routeName: 'companies',           // ✅ set your real route name
                    routeParams: ['id' => $company->id],
                    level: 'info',
                    changes: !empty($changes) ? $changes : null
                );

                return new CompanyResource($company);
            });
        } catch (QueryException $e) {
            if ($this->isDuplicateKey($e)) return $this->duplicateResponse();
            throw $e;
        }
    }

    public function destroy(Request $request, Company $company)
    {
        $user = $request->user(); // 🔔

        return DB::transaction(function () use ($company, $user) {
            $name = $company->com_name;

            // ✅ delete picture file (same style as SaeenFasadController)
            if ($company->picture && str_starts_with($company->picture, 'storage/')) {
                $old = str_replace('storage/', '', $company->picture);
                Storage::disk('public')->delete($old);
            }

            $company->delete();

            // 🔔 Notification: DELETED
            NotificationService::record(
                $user,
                module: 'شرکتونه او فابریکې',
                action: 'deleted',
                title: 'شرکتونه او فابریکې حذف شوو',
                message: 'شرکتونه او فابریکې: ' . ($name ?? ''),
                routeName: null,
                routeParams: ['companies'],
                level: 'warning',
                changes: null
            );

            return response()->json(['message' => 'Deleted']);
        });
    }
    private function printSearchFields(): array
{
    return [
        'com_name',
        'com_type',
        'product_type',
        'com_level',
        'main_owner',

        'responsible_name',
        'responsible_f_name',
        'responsible_position',
        'responsible_phone',

        'com_province',
        'com_zone',
        'com_district',
        'com_village',
        'com_specific_place',
        'com_coordinates',

        'male_employee_quantity_inner',
        'female_employee_quantity_inner',
        'male_employee_quantity_out',
        'female_employee_quantity_out',
        'guard_quantity',

        'resource_id',
        'resource_code',
        'resource_phone_number',
        'note',
        'Province_addr',
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
        $query->where(function (Builder $q) use ($search) {
            $q->where('com_name', 'like', "%{$search}%")
                ->orWhere('com_type', 'like', "%{$search}%")
                ->orWhere('product_type', 'like', "%{$search}%")
                ->orWhere('com_level', 'like', "%{$search}%")
                ->orWhere('main_owner', 'like', "%{$search}%")
                ->orWhere('responsible_name', 'like', "%{$search}%")
                ->orWhere('responsible_f_name', 'like', "%{$search}%")
                ->orWhere('responsible_position', 'like', "%{$search}%")
                ->orWhere('responsible_phone', 'like', "%{$search}%")
                ->orWhere('com_province', 'like', "%{$search}%")
                ->orWhere('com_zone', 'like', "%{$search}%")
                ->orWhere('com_district', 'like', "%{$search}%")
                ->orWhere('com_village', 'like', "%{$search}%")
                ->orWhere('com_specific_place', 'like', "%{$search}%")
                ->orWhere('com_coordinates', 'like', "%{$search}%")
                ->orWhere('male_employee_quantity_inner', 'like', "%{$search}%")
                ->orWhere('female_employee_quantity_inner', 'like', "%{$search}%")
                ->orWhere('male_employee_quantity_out', 'like', "%{$search}%")
                ->orWhere('female_employee_quantity_out', 'like', "%{$search}%")
                ->orWhere('guard_quantity', 'like', "%{$search}%")
                ->orWhere('note', 'like', "%{$search}%")
                ->orWhere('Province_addr', 'like', "%{$search}%")
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
            } elseif ($searchField === 'resource_phone_number') {
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
        $query->whereNotNull('com_coordinates')
            ->whereRaw("TRIM(com_coordinates) != ''");
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
    $query = Company::query()
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

            $row->resource_phone_number =
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
    $title           = $request->input('title', 'چاپ شرکت‌ها');

    return response()->view('prints.table-print', [
        'title'           => $title,
        'rows'            => $rows,
        'selectedColumns' => $selectedColumns,
        'columnLabels'    => $columnLabels,
        'printLabels'     => $printLabels,
        'locale'          => $locale,
    ]);
}private function removePictureFileAndClear($model)
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
    $company = Company::findOrFail($id);

    return $this->removePictureFileAndClear($company);
}
}