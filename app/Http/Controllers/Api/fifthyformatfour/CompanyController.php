<?php

namespace App\Http\Controllers\Api\fifthyformatfour;

use App\Http\Controllers\Controller;
use App\Http\Requests\fifthyformatfour\StoreCompanyRequest;
use App\Http\Requests\fifthyformatfour\UpdateCompanyRequest;
use App\Http\Resources\fifthyformatfour\CompanyResource;
use App\Models\fifthyformatfour\Company;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
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
        $row = Company::create($request->validated());
        $row->load(['resource:id,name,code_number,phone_number']);
        return new CompanyResource($row);
    }

    public function show(Company $company)
    {
        $company->load(['resource:id,name,code_number,phone_number']);
        return new CompanyResource($company);
    }

    public function update(UpdateCompanyRequest $request, Company $company)
    {
        $company->update($request->validated());
        $company->load(['resource:id,name,code_number,phone_number']);
        return new CompanyResource($company);
    }

    public function destroy(Company $company)
    {
        $company->delete();
        return response()->json(['message' => 'Deleted']);
    }
}
