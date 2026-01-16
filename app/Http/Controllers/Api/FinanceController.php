<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\FinanceResource;
use App\Models\Account;
use App\Models\Finance;
use App\Models\Income;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FinanceController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search');

        $query = Finance::query()
            ->with([
                'resource:id,name,code_number,phone_number,nick_name',
                'officeEmployee:id,name,code_number,phone_number',
                'account:id,name,price',
            ])
            ->search($search)
            ->latest('id');

        // ✅ SUMMARY TOTALS
        $totalBalanceAllAccounts = (string) Account::query()->sum('price');
        $totalIncomeAll          = (string) Income::query()->sum('amount');
        $totalOutcomeAll         = (string) Finance::query()->sum('money_amount_number');
        $filteredOutcome         = (string) Finance::query()->search($search)->sum('money_amount_number');

        // ✅ ALWAYS RETURN ALL (NO paginate)
        $rows = $query->get();

        return FinanceResource::collection($rows)->additional([
            'summary' => [
                'total_balance_all_accounts' => $totalBalanceAllAccounts,
                'total_income_all'           => $totalIncomeAll,
                'total_outcome_all'          => $totalOutcomeAll,
                'filtered_outcome'           => $filteredOutcome,
            ],
        ]);
    }
    public function show(Finance $finance)
    {
        $finance->load([
            'resource:id,name,code_number,phone_number,nick_name',
            'officeEmployee:id,name,code_number,phone_number',
            'account:id,name,price',
        ]);

        return new FinanceResource($finance);
    }

    public function store(Request $request)
    {
        $data = $this->validatePayload($request);

        $row = DB::transaction(function () use ($data) {
            /** @var \App\Models\Account $account */
            $account = Account::lockForUpdate()->findOrFail($data['account_id']);

            // Subtract money (Outcome)
            $amount = (float) $data['money_amount_number'];
            $account->update(['price' => (float) $account->price - $amount]);

            return Finance::create($data);
        });

        $row->load([
            'resource:id,name,code_number,phone_number,nick_name',
            'officeEmployee:id,name,code_number,phone_number',
            'account:id,name,price',
        ]);

        return new FinanceResource($row);
    }

    public function update(Request $request, Finance $finance)
    {
        $data = $this->validatePayload($request);

        $updated = DB::transaction(function () use ($data, $finance) {
            // lock old account
            $oldAccount = Account::lockForUpdate()->findOrFail($finance->account_id);
            $newAccount = ($finance->account_id == $data['account_id'])
                ? $oldAccount
                : Account::lockForUpdate()->findOrFail($data['account_id']);

            $oldAmount = (float) $finance->money_amount_number;
            $newAmount = (float) $data['money_amount_number'];

            // revert old outcome
            $oldAccount->update(['price' => (float) $oldAccount->price + $oldAmount]);

            // apply new outcome
            $newAccount->update(['price' => (float) $newAccount->price - $newAmount]);

            $finance->update($data);

            return $finance;
        });

        $updated->load([
            'resource:id,name,code_number,phone_number,nick_name',
            'officeEmployee:id,name,code_number,phone_number',
            'account:id,name,price',
        ]);

        return new FinanceResource($updated);
    }

    public function destroy(Finance $finance)
    {
        DB::transaction(function () use ($finance) {
            $account = Account::lockForUpdate()->findOrFail($finance->account_id);

            // revert outcome on delete
            $amount = (float) $finance->money_amount_number;
            $account->update(['price' => (float) $account->price + $amount]);

            $finance->delete();
        });

        return response()->json(['message' => 'Deleted successfully']);
    }

    private function validatePayload(Request $request): array
    {
        return $request->validate([
            'office_employee_id' => ['nullable', 'integer', 'exists:office_employees,id'],
            'resource_id'        => ['nullable', 'integer', 'exists:resources,id'],
            'account_id'         => ['required', 'integer', 'exists:accounts,id'],

            'money_amount_number' => ['required', 'numeric', 'min:0'],
            'money_amount_words'  => ['nullable', 'string', 'max:500'],

            'date' => ['nullable', 'date'],

            'employee_phone' => ['nullable', 'string', 'max:50'],
            'expense_result' => ['nullable', 'string', 'max:255'],

            'money_distributer' => ['nullable', 'string', 'max:255'],

            'payer'    => ['nullable', 'string', 'max:255'],
            'receiver' => ['nullable', 'string', 'max:255'],

            'account_type' => ['nullable', 'string', 'max:120'],
            'notes'        => ['nullable', 'string'],
        ]);
    }
}
