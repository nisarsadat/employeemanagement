<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\IncomeResource;
use App\Models\Account;
use App\Models\Income;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class IncomeController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) $request->get('perPage', 25);
        $search  = $request->get('search');

        $query = Income::query()
            ->with(['account:id,name,price'])
            ->search($search)
            ->latest('id');

        return IncomeResource::collection($query->paginate($perPage));
    }

    public function show(Income $income)
    {
        $income->load(['account:id,name,price']);
        return new IncomeResource($income);
    }

    public function store(Request $request)
    {
        $data = $this->validatePayload($request);

        $row = DB::transaction(function () use ($data) {
            $account = Account::lockForUpdate()->findOrFail($data['account_id']);

            $amount = (float) $data['amount'];
            $account->update(['price' => (float)$account->price + $amount]);

            return Income::create($data);
        });

        $row->load(['account:id,name,price']);
        return new IncomeResource($row);
    }

    public function update(Request $request, Income $income)
    {
        $data = $this->validatePayload($request);

        $updated = DB::transaction(function () use ($data, $income) {
            $oldAccount = Account::lockForUpdate()->findOrFail($income->account_id);
            $newAccount = ($income->account_id == $data['account_id'])
                ? $oldAccount
                : Account::lockForUpdate()->findOrFail($data['account_id']);

            $oldAmount = (float) $income->amount;
            $newAmount = (float) $data['amount'];

            // revert old income
            $oldAccount->update(['price' => (float)$oldAccount->price - $oldAmount]);

            // apply new income
            $newAccount->update(['price' => (float)$newAccount->price + $newAmount]);

            $income->update($data);

            return $income;
        });

        $updated->load(['account:id,name,price']);
        return new IncomeResource($updated);
    }

    public function destroy(Income $income)
    {
        DB::transaction(function () use ($income) {
            $account = Account::lockForUpdate()->findOrFail($income->account_id);

            // revert income on delete
            $amount = (float) $income->amount;
            $account->update(['price' => (float)$account->price - $amount]);

            $income->delete();
        });

        return response()->json(['message' => 'Deleted successfully']);
    }

    private function validatePayload(Request $request): array
    {
        return $request->validate([
            'account_id'   => ['required', 'integer', 'exists:accounts,id'],
            'amount'       => ['required', 'numeric', 'min:0'],
            'received_at'  => ['nullable', 'date'],
            'note'         => ['nullable', 'string'],
        ]);
    }
}
