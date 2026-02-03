<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreExpenseOilRequest;
use App\Http\Requests\UpdateExpenseOilRequest;
use App\Http\Resources\ExpenseOilResource;
use App\Models\ExpenseOil;
use App\Models\IncomeOil;
use App\Models\OilAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExpenseOilController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'perPage' => ['nullable', 'integer', 'min:1', 'max:1000'],
            'search'  => ['nullable', 'string', 'max:255'],

            // ✅ year/month filter (ALWAYS Gregorian values)
            'year'    => ['nullable', 'integer', 'min:2000', 'max:2100'],
            'month'   => ['nullable', 'integer', 'min:1', 'max:12'],

            // optional filters (if you use them)
            'oil_account_id'     => ['nullable', 'integer', 'exists:oil_accounts,id'],
            'office_employee_id' => ['nullable', 'integer', 'exists:office_employees,id'],
        ]);

        $perPage = (int) $request->get('perPage', 25);
        $search  = trim((string)$request->get('search', ''));

        $year  = $request->integer('year');
        $month = $request->integer('month');

        $accountId  = $request->integer('oil_account_id');
        $employeeId = $request->integer('office_employee_id');

        $query = ExpenseOil::query()
            ->with([
                'account:id,name,quantity',
                'employee:id,name,code_number,phone_number',
            ])
            ->search($search)
            ->latest('id');

        if ($accountId)  $query->where('oil_account_id', $accountId);
        if ($employeeId) $query->where('office_employee_id', $employeeId);

        // ✅ filter by year/month on expense_oils.date
        if ($year)  $query->whereYear('date', $year);
        if ($month) $query->whereMonth('date', $month);

        // =========================
        // ✅ SUMMARY (cards) - also follow year/month
        // =========================
        $totalQuantityAllAccounts = (string) OilAccount::query()->sum('quantity');

        $incomeTotalQuery = IncomeOil::query();
        if ($year)  $incomeTotalQuery->whereYear('date', $year);
        if ($month) $incomeTotalQuery->whereMonth('date', $month);
        $totalIncomeQuantity = (string) $incomeTotalQuery->sum('oil_quantity');

        $expenseTotalQuery = ExpenseOil::query();
        if ($year)  $expenseTotalQuery->whereYear('date', $year);
        if ($month) $expenseTotalQuery->whereMonth('date', $month);
        $totalExpenseQuantity = (string) $expenseTotalQuery->sum('liter_in_number');

        $filteredExpenseQuery = ExpenseOil::query()->search($search);
        if ($accountId)  $filteredExpenseQuery->where('oil_account_id', $accountId);
        if ($employeeId) $filteredExpenseQuery->where('office_employee_id', $employeeId);
        if ($year)  $filteredExpenseQuery->whereYear('date', $year);
        if ($month) $filteredExpenseQuery->whereMonth('date', $month);

        $filteredExpenseQuantity = (string) $filteredExpenseQuery->sum('liter_in_number');

        return ExpenseOilResource::collection($query->paginate($perPage))->additional([
            'summary' => [
                'total_quantity_all_accounts' => $totalQuantityAllAccounts,
                'total_income_quantity'       => $totalIncomeQuantity,
                'total_expense_quantity'      => $totalExpenseQuantity,
                'filtered_expense_quantity'   => $filteredExpenseQuantity,
            ],
        ]);
    }

    public function show(ExpenseOil $expenseOil)
    {
        return new ExpenseOilResource(
            $expenseOil->load([
                'account:id,name,quantity',
                'employee:id,name,code_number,phone_number',
            ])
        );
    }

    public function store(StoreExpenseOilRequest $request)
    {
        $payload = $request->validated();

        $expenseOil = DB::transaction(function () use ($payload) {
            /** @var OilAccount $account */
            $account = OilAccount::lockForUpdate()->findOrFail($payload['oil_account_id']);

            $row = ExpenseOil::create($payload);

            $qty = (string) ($payload['liter_in_number'] ?? '0');
            $account->quantity = bcsub((string)$account->quantity, $qty, 3);
            $account->save();

            return $row;
        });

        return new ExpenseOilResource(
            $expenseOil->load([
                'account:id,name,quantity',
                'employee:id,name,code_number,phone_number',
            ])
        );
    }

    public function update(UpdateExpenseOilRequest $request, ExpenseOil $expenseOil)
    {
        $payload = $request->validated();

        $expenseOil = DB::transaction(function () use ($payload, $expenseOil) {
            $oldAccount = OilAccount::lockForUpdate()->findOrFail($expenseOil->oil_account_id);

            $newAccount = ((int)$expenseOil->oil_account_id === (int)$payload['oil_account_id'])
                ? $oldAccount
                : OilAccount::lockForUpdate()->findOrFail($payload['oil_account_id']);

            $oldQty = (string) ($expenseOil->liter_in_number ?? '0');
            $newQty = (string) ($payload['liter_in_number'] ?? '0');

            // revert old
            $oldAccount->quantity = bcadd((string)$oldAccount->quantity, $oldQty, 3);
            $oldAccount->save();

            // update row
            $expenseOil->update($payload);

            // apply new
            $newAccount->quantity = bcsub((string)$newAccount->quantity, $newQty, 3);
            $newAccount->save();

            return $expenseOil;
        });

        return new ExpenseOilResource(
            $expenseOil->load([
                'account:id,name,quantity',
                'employee:id,name,code_number,phone_number',
            ])
        );
    }

    public function destroy(ExpenseOil $expenseOil)
    {
        DB::transaction(function () use ($expenseOil) {
            /** @var OilAccount $account */
            $account = OilAccount::lockForUpdate()->findOrFail($expenseOil->oil_account_id);

            $oldQty = (string) ($expenseOil->liter_in_number ?? '0');
            $account->quantity = bcadd((string)$account->quantity, $oldQty, 3);
            $account->save();

            $expenseOil->delete();
        });

        return response()->json(['message' => 'Deleted successfully.']);
    }

    // ✅ years endpoint for dropdown (Gregorian values)
    public function years()
    {
        return ExpenseOil::query()
            ->selectRaw('YEAR(date) as year')
            ->whereNotNull('date')
            ->distinct()
            ->orderByDesc('year')
            ->pluck('year');
    }
}
