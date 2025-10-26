<?php

namespace App\Http\Controllers;

use App\Http\Requests\IncomeOilRequest;
use App\Http\Resources\IncomeOilResource;
use App\Models\IncomeOil;
use App\Models\OilAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class IncomeOilController extends Controller
{
    public function index(Request $request) {
        $perPage = (int)($request->integer('perPage') ?: 15);
        $search  = trim((string)$request->get('search', ''));
        $from    = $request->date('from'); // expects YYYY-MM-DD
        $to      = $request->date('to');

        $query = IncomeOil::query()->with(['account:id,name,quantity']);

        if ($search !== '') {
            $query->where(function($q) use ($search) {
                $q->where('source', 'like', "%{$search}%")
                ->orWhere('note', 'like', "%{$search}%");
            });
        }

        if ($from && $to)      { $query->whereBetween('date', [$from, $to]); }
        elseif ($from)         { $query->whereDate('date', '>=', $from); }
        elseif ($to)           { $query->whereDate('date', '<=', $to); }

        $query->orderByDesc('date')->orderByDesc('id'); // <-- sort by date first

        return IncomeOilResource::collection($query->paginate($perPage));
    }


    public function store(IncomeOilRequest $request) {
        $payload = $request->validated();

        $result = DB::transaction(function () use ($payload) {
            /** @var OilAccount $account */
            $account = OilAccount::lockForUpdate()->findOrFail($payload['oil_account_id']);

            $income = IncomeOil::create($payload);

            // increase account quantity
            $account->quantity = bcadd((string)$account->quantity, (string)$payload['oil_quantity'], 3);
            $account->save();

            return $income->load(['account:id,name,quantity']);
        });

        return new IncomeOilResource($result);
    }

    public function show(IncomeOil $income_oil) {
        $income_oil->load(['account:id,name,quantity']);
        return new IncomeOilResource($income_oil);
    }

    public function update(IncomeOilRequest $request, IncomeOil $income_oil) {
        $payload = $request->validated();

        $updated = DB::transaction(function () use ($payload, $income_oil) {
            // revert previous account effect
            $oldAccount = OilAccount::lockForUpdate()->findOrFail($income_oil->oil_account_id);
            $oldAccount->quantity = bcsub((string)$oldAccount->quantity, (string)$income_oil->oil_quantity, 3);
            $oldAccount->save();

            // apply new payload
            $income_oil->update($payload);

            // apply new effect
            $newAccount = OilAccount::lockForUpdate()->findOrFail($income_oil->oil_account_id);
            $newAccount->quantity = bcadd((string)$newAccount->quantity, (string)$income_oil->oil_quantity, 3);
            $newAccount->save();

            return $income_oil->load(['account:id,name,quantity']);
        });

        return new IncomeOilResource($updated);
    }

    public function destroy(IncomeOil $income_oil) {
        DB::transaction(function () use ($income_oil) {
            $account = OilAccount::lockForUpdate()->findOrFail($income_oil->oil_account_id);
            $account->quantity = bcsub((string)$account->quantity, (string)$income_oil->oil_quantity, 3);
            $account->save();

            $income_oil->delete();
        });

        return response()->noContent();
    }
}
