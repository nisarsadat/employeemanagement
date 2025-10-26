<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreExpenseRequest;
use App\Http\Resources\ExpenseResource;
use App\Models\Account;
use App\Models\Expense;
use App\Models\OfficeEmployee;
use App\Models\Resource as ResourceModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int)($request->integer('perPage') ?: 15);
        $search  = trim((string)$request->get('search',''));
        $from    = $request->date('from');
        $to      = $request->date('to');

        $q = Expense::query()
            ->with(['account:id,name,price','payee'])
            ->when($from, fn($w) => $w->whereDate('paid_at', '>=', $from))
            ->when($to,   fn($w) => $w->whereDate('paid_at', '<=', $to))
            ->orderBy('paid_at','desc')->orderBy('id','desc');

        if ($search !== '') {
            $q->where(function($w) use ($search) {
                $w->where('reason_expense','like',"%{$search}%")
                  ->orWhere('note','like',"%{$search}%")
                  ->orWhere('phone_number','like',"%{$search}%");
            });
        }

        return ExpenseResource::collection($q->paginate($perPage));
    }

    public function store(StoreExpenseRequest $request)
    {
        $data = $request->validated();

        // map payee_type
        if (!empty($data['payee_type']) && !empty($data['payee_id'])) {
            $map = [
                'office_employee' => OfficeEmployee::class,
                'resource'        => ResourceModel::class,
            ];
            $data['payee_type'] = $map[$data['payee_type']] ?? null;
        } else {
            $data['payee_type'] = null;
            $data['payee_id']   = null;
        }

        $expense = DB::transaction(function () use ($data) {
            $account = Account::whereKey($data['account_id'])->lockForUpdate()->firstOrFail();

            $exp = Expense::create($data);

            // subtract from account price
            $account->price = bcsub((string)$account->price, (string)$data['amount_price'], 2);
            $account->save();

            return $exp->load(['account:id,name,price','payee']);
        });

        return new ExpenseResource($expense);
    }

    public function update(Request $request, Expense $expense)
    {
        // validate (inline; make UpdateExpenseRequest if you want)
        $data = $request->validate([
            'account_id'     => ['sometimes','exists:accounts,id'],
            'payee_type'     => ['nullable','string', Rule::in(['office_employee','resource'])],
            'payee_id'       => ['nullable','integer'],
            'amount_price'   => ['sometimes','numeric','min:0.01'],
            'paid_at'        => ['sometimes','date'],
            'phone_number'   => ['nullable','string','max:30'],
            'reason_expense' => ['nullable','string','max:255'],
            'note'           => ['nullable','string'],
        ]);

        // normalize payee mapping if present
        if (array_key_exists('payee_type', $data)) {
            if (!empty($data['payee_type']) && !empty($data['payee_id'])) {
                $map = [
                    'office_employee' => OfficeEmployee::class,
                    'resource'        => ResourceModel::class,
                ];
                $data['payee_type'] = $map[$data['payee_type']] ?? null;
            } else {
                $data['payee_type'] = null;
                $data['payee_id']   = null;
            }
        }

        $expense = DB::transaction(function () use ($expense, $data) {
            // lock old account
            $oldAccount = Account::whereKey($expense->account_id)->lockForUpdate()->firstOrFail();

            // reverse previous effect (add back old)
            $oldAccount->price = bcadd((string)$oldAccount->price, (string)$expense->amount_price, 2);
            $oldAccount->save();

            // apply changes
            $expense->fill($data);

            // lock (possibly new) account
            $newAccount = Account::whereKey($expense->account_id)->lockForUpdate()->firstOrFail();

            // apply new effect (subtract new)
            $newAccount->price = bcsub((string)$newAccount->price, (string)$expense->amount_price, 2);
            $newAccount->save();

            $expense->save();

            return $expense->load(['account:id,name,price','payee']);
        });

        return new ExpenseResource($expense);
    }

    public function destroy(Expense $expense)
    {
        DB::transaction(function () use ($expense) {
            // lock account and reverse effect
            $account = Account::whereKey($expense->account_id)->lockForUpdate()->firstOrFail();
            $account->price = bcadd((string)$account->price, (string)$expense->amount_price, 2);
            $account->save();

            $expense->delete();
        });

        return response()->json(['deleted' => true]);
    }
}
