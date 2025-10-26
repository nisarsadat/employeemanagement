<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreIncomeRequest;
use App\Http\Resources\IncomeResource;
use App\Models\Account;
use App\Models\Income;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class IncomeController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int)($request->integer('perPage') ?: 15);
        $search  = trim((string)$request->get('search',''));
        $from    = $request->date('from');
        $to      = $request->date('to');

        $q = Income::query()
            ->with(['account:id,name,price'])
            ->when($from, fn($w) => $w->whereDate('received_at', '>=', $from))
            ->when($to,   fn($w) => $w->whereDate('received_at', '<=', $to))
            ->orderBy('received_at','desc')->orderBy('id','desc');

        if ($search !== '') {
            $q->where(function($w) use ($search) {
                $w->where('note','like',"%{$search}%");
            });
        }

        return IncomeResource::collection($q->paginate($perPage));
    }

    public function store(StoreIncomeRequest $request)
    {
        $data = $request->validated();

        $income = DB::transaction(function () use ($data) {
            $account = Account::whereKey($data['account_id'])->lockForUpdate()->firstOrFail();

            $inc = Income::create($data);

            // add to account price
            $account->price = bcadd((string)$account->price, (string)$data['amount'], 2);
            $account->save();

            return $inc->load(['account:id,name,price']);
        });

        return new IncomeResource($income);
    }

    public function update(Request $request, Income $income)
    {
        // validate (simple inline rules; make UpdateIncomeRequest if you prefer)
        $data = $request->validate([
            'account_id'  => ['sometimes','exists:accounts,id'],
            'amount'      => ['sometimes','numeric','min:0.01'],
            'received_at' => ['sometimes','date'],
            'note'        => ['nullable','string'],
        ]);

        $income = DB::transaction(function () use ($income, $data) {
            // lock old account
            $oldAccount = Account::whereKey($income->account_id)->lockForUpdate()->firstOrFail();

            // reverse previous effect (subtract old amount)
            $oldAccount->price = bcsub((string)$oldAccount->price, (string)$income->amount, 2);
            $oldAccount->save();

            // apply incoming changes
            $income->fill($data);

            // lock (possibly new) account
            $newAccount = Account::whereKey($income->account_id)->lockForUpdate()->firstOrFail();

            // apply new effect (add new amount)
            $newAccount->price = bcadd((string)$newAccount->price, (string)$income->amount, 2);
            $newAccount->save();

            $income->save();

            return $income->load(['account:id,name,price']);
        });

        return new IncomeResource($income);
    }

    public function destroy(Income $income)
    {
        DB::transaction(function () use ($income) {
            // lock account and reverse effect
            $account = Account::whereKey($income->account_id)->lockForUpdate()->firstOrFail();
            $account->price = bcsub((string)$account->price, (string)$income->amount, 2);
            $account->save();

            $income->delete();
        });

        return response()->json(['deleted' => true]);
    }
}
