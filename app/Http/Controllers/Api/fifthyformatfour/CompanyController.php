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
            'message' => 'این کمپنی قبلاً ثبت شده است.',
            'errors' => [
                'duplicate' => [
                    'کمپنی با همین مشخصات (نام، نوع، نوع محصول و ولایت/زون/ولسوالی/قریه) قبلاً در سیستم موجود است.',
                ],
            ],
        ], 422);
    }

    public function index(Request $request)
    {
        $query = Company::query()->with(['resource:id,name,code_number,phone_number']);

        if ($s = trim((string) $request->query('search', ''))) {
            $query->where(function (Builder $q) use ($s) {
                $q->where('com_name','like',"%$s%")
                  ->orWhere('product_type','like',"%$s%")
                  ->orWhere('com_district','like',"%$s%")
                  ->orWhere('com_village','like',"%$s%");
            });
        }

        $perPage = $request->query('perPage');
        $page    = $request->query('page');

        $perPageInt = filter_var($perPage, FILTER_VALIDATE_INT, ['options'=>['min_range'=>1]]);
        $pageInt    = filter_var($page,    FILTER_VALIDATE_INT, ['options'=>['min_range'=>1]]);

        // Fallback → return all when missing/invalid
        if ($perPageInt === false || $pageInt === false) {
            return CompanyResource::collection($query->orderByDesc('id')->get());
        }

        $rows = $query->orderByDesc('id')->paginate($perPageInt, ['*'], 'page', $pageInt);
        return CompanyResource::collection($rows);
    }

    public function store(StoreCompanyRequest $request)
    {
        $user = $request->user(); // 🔔 who did this

        try {
            return DB::transaction(function () use ($request, $user) {
                $row = Company::create($request->validated());
                $row->load(['resource:id,name,code_number,phone_number']);

                // 🔔 Notification: CREATED
                NotificationService::record(
                    $user,
                    module: 'کمپنی',
                    action: 'created',
                    title: 'کمپنی جدید ثبت شد',
                    message: 'نام کمپنی: ' . ($row->com_name ?? ''),
                    routeName: 'companies',           // ✅ set your real route name
                    routeParams: ['id' => $row->id],
                    level: 'success',
                    changes: null
                );

                return new CompanyResource($row);
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

    public function update(UpdateCompanyRequest $request, Company $company)
    {
        $user = $request->user(); // 🔔

        try {
            return DB::transaction(function () use ($request, $company, $user) {
                // 🧮 build changes BEFORE update
                $original  = $company->getOriginal();
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

                $company->update($validated);
                $company->load(['resource:id,name,code_number,phone_number']);

                // 🔔 Notification: UPDATED + changes
                NotificationService::record(
                    $user,
                    module: 'کمپنی',
                    action: 'updated',
                    title: 'کمپنی اصلاح شد',
                    message: 'نام کمپنی: ' . ($company->com_name ?? ''),
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

            $company->delete();

            // 🔔 Notification: DELETED
            NotificationService::record(
                $user,
                module: 'کمپنی',
                action: 'deleted',
                title: 'کمپنی حذف شد',
                message: 'نام کمپنی: ' . ($name ?? ''),
                routeName: null,
                routeParams: ['companies'],
                level: 'warning',
                changes: null
            );

            return response()->json(['message' => 'Deleted']);
        });
    }
}
