<?php

namespace App\Http\Controllers\fifthyformatstow;

use App\Http\Controllers\Controller;
use App\Http\Requests\fifthyformatstow\StoreBankRequest;
use App\Http\Requests\fifthyformatstow\UpdateBankRequest;
use App\Http\Resources\fifthyformatstow\BankResource;
use App\Models\fifthyformatstow\Bank;
use Illuminate\Http\Request;

class BankController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) $request->input('perPage', 10);
        $search  = $request->input('search');

        $q = Bank::query()
            ->with(['resource:id,name,code_number,phone_number'])
            ->latest();

        if ($search) {
            $q->where(function ($qq) use ($search) {
                $qq->where('bank_name', 'like', "%{$search}%")
                   ->orWhere('bank_type', 'like', "%{$search}%")
                   ->orWhere('bank_owner', 'like', "%{$search}%")
                   ->orWhere('responsible_name', 'like', "%{$search}%")
                   ->orWhere('responsible_phone', 'like', "%{$search}%")
                   ->orWhere('province', 'like', "%{$search}%")
                   ->orWhere('district', 'like', "%{$search}%");
            });
        }

        return BankResource::collection($q->paginate($perPage));
    }

    public function store(StoreBankRequest $request)
    {
        $bank = Bank::create($request->validated())
            ->load(['resource:id,name,code_number,phone_number']);

        return BankResource::make($bank);
    }

    public function show(Bank $bank)
    {
        $bank->load(['resource:id,name,code_number,phone_number']);
        return BankResource::make($bank);
    }

    public function update(UpdateBankRequest $request, Bank $bank)
    {
        $bank->update($request->validated());
        $bank->load(['resource:id,name,code_number,phone_number']);

        return BankResource::make($bank);
    }

    public function destroy(Bank $bank)
    {
        $bank->delete();
        return response()->noContent();
    }

    public function bulkDelete(Request $request)
    {
        $ids = $request->input('ids', []);
        Bank::destroy($ids);
        return response()->noContent();
    }
}
