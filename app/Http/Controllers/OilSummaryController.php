<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ExpenseOil;
use App\Models\IncomeOil;
use App\Models\OilAccount;

class OilSummaryController extends Controller
{
    public function index()
    {
        return response()->json([
            'totalExpense' => (string) ExpenseOil::query()->sum('liter_in_number'),
            'totalIncome' => (string) IncomeOil::query()->sum('oil_quantity'),
            'totalOilQuantity' => (string) OilAccount::query()->sum('quantity'),

            'expenseCount' => ExpenseOil::query()->count(),
            'incomeCount' => IncomeOil::query()->count(),
            'oilAccountsCount' => OilAccount::query()->count(),
        ]);
    }
}
