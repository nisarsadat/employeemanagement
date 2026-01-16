<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreExpenseOilRequest;
use App\Http\Requests\UpdateExpenseOilRequest;
use App\Http\Resources\ExpenseOilResource;
use App\Models\ExpenseOil;
use Illuminate\Http\Request;

class ExpenseOilController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) $request->get('perPage', 25);
        $search  = $request->get('search');

        $query = ExpenseOil::query()
            ->with([
                'account:id,name',
                'employee:id,name,code_number,phone_number',
            ])
            ->search($search)
            ->latest('id');

        return ExpenseOilResource::collection($query->paginate($perPage));
    }

    public function show(ExpenseOil $expenseOil)
    {
        return (new ExpenseOilResource(
            $expenseOil->load([
                'account:id,name',
                'employee:id,name,code_number,phone_number',
            ])
        ));
    }

    public function store(StoreExpenseOilRequest $request)
    {
        $expenseOil = ExpenseOil::create($request->validated());

        return (new ExpenseOilResource(
            $expenseOil->load([
                'account:id,name',
                'employee:id,name,code_number,phone_number',
            ])
        ));
    }

    public function update(UpdateExpenseOilRequest $request, ExpenseOil $expenseOil)
    {
        $expenseOil->update($request->validated());

        return (new ExpenseOilResource(
            $expenseOil->load([
                'account:id,name',
                'employee:id,name,code_number,phone_number',
            ])
        ));
    }

    public function destroy(ExpenseOil $expenseOil)
    {
        $expenseOil->delete();

        return response()->json(['message' => 'Deleted successfully.']);
    }
}
