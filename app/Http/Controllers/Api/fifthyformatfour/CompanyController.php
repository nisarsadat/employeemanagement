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
}