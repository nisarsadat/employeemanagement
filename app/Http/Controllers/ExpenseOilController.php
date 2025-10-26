<?php

namespace App\Http\Controllers;

use App\Http\Requests\ExpenseOilRequest;
use App\Http\Resources\ExpenseOilResource;
use App\Models\ExpenseOil;
use App\Models\OilAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExpenseOilController extends Controller
{
    public function index(Request $request) {
        $perPage = (int)($request->integer('perPage') ?: 15);
        $search  = trim((string)$request->get('search', ''));
        $from    = $request->date('from');
        $to      = $request->date('to');

        $query = ExpenseOil::query()->with(['account:id,name,quantity']);

        if ($search !== '') {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                ->orWhere('destination', 'like', "%{$search}%")
                ->orWhere('note', 'like', "%{$search}%");
            });
        }

        if ($from && $to)      { $query->whereBetween('date', [$from, $to]); }
        elseif ($from)         { $query->whereDate('date', '>=', $from); }
        elseif ($to)           { $query->whereDate('date', '<=', $to); }

        $query->orderByDesc('date')->orderByDesc('id'); // <-- sort by date first

        return ExpenseOilResource::collection($query->paginate($perPage));
    }


    public function store(ExpenseOilRequest $request) {
        $payload = $request->validated();

        $result = DB::transaction(function () use ($payload) {
            /** @var OilAccount $account */
            $account = OilAccount::lockForUpdate()->findOrFail($payload['oil_account_id']);

            $expense = ExpenseOil::create($payload);

            // decrease account quantity
            $qty = $payload['oil_quantity']; // remove these 2 lines if you don’t keep qty on expense
            $account->quantity = bcsub((string)$account->quantity, (string)$qty, 3);

            if (bccomp((string)$account->quantity, '0', 3) < 0) {
                // optional guard: prevent negative
                throw new \RuntimeException('Oil account would go negative. Check quantities.');
            }

            $account->save();

            return $expense->load(['account:id,name,quantity']);
        });

        return new ExpenseOilResource($result);
    }

    public function show(ExpenseOil $expense_oil) {
        $expense_oil->load(['account:id,name,quantity']);
        return new ExpenseOilResource($expense_oil);
    }

    public function update(ExpenseOilRequest $request, ExpenseOil $expense_oil) {
        $payload = $request->validated();

        $updated = DB::transaction(function () use ($payload, $expense_oil) {
            // revert previous effect on old account
            $oldAccount = OilAccount::lockForUpdate()->findOrFail($expense_oil->oil_account_id);
            $oldQty = $expense_oil->oil_quantity; // remove if you don’t keep qty
            $oldAccount->quantity = bcadd((string)$oldAccount->quantity, (string)$oldQty, 3); // add back
            $oldAccount->save();

            // update row
            $expense_oil->update($payload);

            // apply new effect
            $newAccount = OilAccount::lockForUpdate()->findOrFail($expense_oil->oil_account_id);
            $newQty = $expense_oil->oil_quantity; // remove if you don’t keep qty
            $newAccount->quantity = bcsub((string)$newAccount->quantity, (string)$newQty, 3);

            if (bccomp((string)$newAccount->quantity, '0', 3) < 0) {
                throw new \RuntimeException('Oil account would go negative. Check quantities.');
            }

            $newAccount->save();

            return $expense_oil->load(['account:id,name,quantity']);
        });

        return new ExpenseOilResource($updated);
    }

    public function destroy(ExpenseOil $expense_oil) {
        DB::transaction(function () use ($expense_oil) {
            $account = OilAccount::lockForUpdate()->findOrFail($expense_oil->oil_account_id);
            $account->quantity = bcadd((string)$account->quantity, (string)$expense_oil->oil_quantity, 3); // add back
            $account->save();

            $expense_oil->delete();
        });

        return response()->noContent();
    }
}
