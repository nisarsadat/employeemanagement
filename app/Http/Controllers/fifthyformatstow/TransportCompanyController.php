<?php

namespace App\Http\Controllers\fifthyformatstow;

use App\Http\Controllers\Controller;
use App\Http\Requests\fifthyformatstow\StoreTransportCompanyRequest;
use App\Http\Requests\fifthyformatstow\UpdateTransportCompanyRequest;
use App\Http\Resources\fifthyformatstow\TransportCompanyResource;
use App\Models\fifthyformatstow\TransportCompany;
use Illuminate\Http\Request;

class TransportCompanyController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) $request->input('perPage', 10);
        $search  = $request->input('search');

        $q = TransportCompany::query()
            ->with(['resource:id,name,code_number,phone_number'])
            ->latest();

        if ($search) {
            $q->where(function ($qq) use ($search) {
                $qq->where('transport_company_name', 'like', "%{$search}%")
                   ->orWhere('responsible_name', 'like', "%{$search}%")
                   ->orWhere('responsible_phone', 'like', "%{$search}%")
                   ->orWhere('province', 'like', "%{$search}%")
                   ->orWhere('district', 'like', "%{$search}%");
            });
        }

        return TransportCompanyResource::collection($q->paginate($perPage));
    }

    public function store(StoreTransportCompanyRequest $request)
    {
        $company = TransportCompany::create($request->validated())
            ->load(['resource:id,name,code_number,phone_number']);

        return TransportCompanyResource::make($company);
    }

    public function show(TransportCompany $transportCompany)
    {
        $transportCompany->load(['resource:id,name,code_number,phone_number']);
        return TransportCompanyResource::make($transportCompany);
    }

    public function update(UpdateTransportCompanyRequest $request, TransportCompany $transportCompany)
    {
        $transportCompany->update($request->validated());
        $transportCompany->load(['resource:id,name,code_number,phone_number']);

        return TransportCompanyResource::make($transportCompany);
    }

    public function destroy(TransportCompany $transportCompany)
    {
        $transportCompany->delete();
        return response()->noContent();
    }

    public function bulkDelete(Request $request)
    {
        $ids = $request->input('ids', []);
        TransportCompany::destroy($ids);
        return response()->noContent();
    }
}
