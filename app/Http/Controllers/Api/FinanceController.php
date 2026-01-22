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
        // ✅ validate query filters
        $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'account_id' => ['nullable', 'integer', 'exists:accounts,id'],
            'office_employee_id' => ['nullable', 'integer', 'exists:office_employees,id'],
            'year'  => ['nullable', 'integer', 'min:2000', 'max:2100'],
            'month' => ['nullable', 'integer', 'min:1', 'max:12'],
        ]);

        $search = $request->query('search');
        $accountId = $request->integer('account_id');
        $officeEmployeeId = $request->integer('office_employee_id');
        $year  = $request->integer('year');
        $month = $request->integer('month');

        $query = Finance::query()
            ->with([
                'resource:id,name,code_number,phone_number,nick_name',
                'officeEmployee:id,name,code_number,phone_number',
                'account:id,name,price',
            ])
            ->search($search)
            ->latest('id');

        // ✅ filter by account/employee
        if ($accountId) {
            $query->where('account_id', $accountId);
        }
        if ($officeEmployeeId) {
            $query->where('office_employee_id', $officeEmployeeId);
        }

        // ✅ filter by year/month on finance.date
        if ($year) {
            $query->whereYear('date', $year);
        }
        if ($month) {
            $query->whereMonth('date', $month);
        }

        // ✅ ALWAYS RETURN ALL (NO paginate)
        $rows = $query->get();

        // =========================
        // ✅ SUMMARY TOTALS
        // =========================

        // Total balance = current balances (normally not date-filtered)
        $totalBalanceAllAccounts = (string) Account::query()->sum('price');

        // Total income (filtered by year/month if provided)
        $incomeQuery = Income::query();
        // ⚠️ Change 'received_at' to your real income date column if different
        if ($year) $incomeQuery->whereYear('received_at', $year);
        if ($month) $incomeQuery->whereMonth('received_at', $month);
        $totalIncomeAll = (string) $incomeQuery->sum('amount');

        // Total outcome (filtered by year/month if provided)
        $outcomeQuery = Finance::query();
        if ($year) $outcomeQuery->whereYear('date', $year);
        if ($month) $outcomeQuery->whereMonth('date', $month);
        $totalOutcomeAll = (string) $outcomeQuery->sum('money_amount_number');

        // Filtered outcome = apply same filters as list (search + account + employee + year + month)
        $filteredOutcomeQuery = Finance::query()->search($search);

        if ($accountId) $filteredOutcomeQuery->where('account_id', $accountId);
        if ($officeEmployeeId) $filteredOutcomeQuery->where('office_employee_id', $officeEmployeeId);
        if ($year) $filteredOutcomeQuery->whereYear('date', $year);
        if ($month) $filteredOutcomeQuery->whereMonth('date', $month);

        $filteredOutcome = (string) $filteredOutcomeQuery->sum('money_amount_number');

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
    public function years()
    {
        return Finance::query()
            ->selectRaw('YEAR(date) as year')
            ->whereNotNull('date')
            ->distinct()
            ->orderByDesc('year')
            ->pluck('year');
    }
}
