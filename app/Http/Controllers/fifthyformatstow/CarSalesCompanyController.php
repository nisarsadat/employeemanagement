<?php

namespace App\Http\Controllers\fifthyformatstow;

use App\Http\Controllers\Controller;
use App\Http\Requests\fifthyformatstow\StoreCarSalesCompanyRequest;
use App\Http\Requests\fifthyformatstow\UpdateCarSalesCompanyRequest;
use App\Http\Resources\fifthyformatstow\CarSalesCompanyResource;
use App\Models\fifthyformatstow\CarSalesCompany;
use Illuminate\Http\Request;

class CarSalesCompanyController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) $request->input('perPage', 10);
        $search  = $request->input('search');

        $q = CarSalesCompany::query()
            ->with(['resource:id,name,code_number,phone_number'])
            ->latest();

        if ($search) {
            $q->where(function ($qq) use ($search) {
                $qq->where('car_sales_company_name', 'like', "%{$search}%")
                   ->orWhere('responsible_name', 'like', "%{$search}%")
                   ->orWhere('responsible_phone', 'like', "%{$search}%")
                   ->orWhere('province', 'like', "%{$search}%")
                   ->orWhere('district', 'like', "%{$search}%");
            });
        }

        return CarSalesCompanyResource::collection($q->paginate($perPage));
    }

    public function store(StoreCarSalesCompanyRequest $request)
    {
        $company = CarSalesCompany::create($request->validated())
            ->load(['resource:id,name,code_number,phone_number']);

        return CarSalesCompanyResource::make($company);
    }

    public function show(CarSalesCompany $carSalesCompany)
    {
        $carSalesCompany->load(['resource:id,name,code_number,phone_number']);
        return CarSalesCompanyResource::make($carSalesCompany);
    }

    public function update(UpdateCarSalesCompanyRequest $request, CarSalesCompany $carSalesCompany)
    {
        $carSalesCompany->update($request->validated());
        $carSalesCompany->load(['resource:id,name,code_number,phone_number']);

        return CarSalesCompanyResource::make($carSalesCompany);
    }

    public function destroy(CarSalesCompany $carSalesCompany)
    {
        $carSalesCompany->delete();
        return response()->noContent();
    }

    public function bulkDelete(Request $request)
    {
        $ids = $request->input('ids', []);
        CarSalesCompany::destroy($ids);
        return response()->noContent();
    }
}
