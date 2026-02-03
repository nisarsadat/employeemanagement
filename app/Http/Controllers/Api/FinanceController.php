<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\FinanceResource;
use App\Models\Account;
use App\Models\Event;
use App\Models\Finance;
use App\Models\Information;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class FinanceController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) ($request->get('perPage', 10));
        $page    = (int) ($request->get('page', 1));

        $search  = trim((string) $request->get('search', ''));

        $year    = $request->get('year');   // expected: 2026
        $month   = $request->get('month');  // expected: 1..12

        // ✅ NEW: extra filters (frontend already sends these)
        $accountId        = $request->get('account_id');
        $officeEmployeeId = $request->get('office_employee_id');

        $q = Finance::query()
            ->with([
                'resource:id,name,code_number,phone_number,nick_name',
                'officeEmployee:id,name,code_number,phone_number',
                'account:id,name,price',
                'event:id,finance_status,finance_paid_at',
                'information:id,finance_status,finance_paid_at',
            ])
            ->orderByDesc('id');

        // ✅ Search
        if ($search !== '') {
            $q->search($search);
        }

        // ✅ Account filter
        if (is_numeric($accountId) && (int) $accountId > 0) {
            $q->where('account_id', (int) $accountId);
        }

        // ✅ Office employee filter
        if (is_numeric($officeEmployeeId) && (int) $officeEmployeeId > 0) {
            $q->where('office_employee_id', (int) $officeEmployeeId);
        }

        // ✅ Year/month filter (on Finance.date)
        if (is_numeric($year) && (int) $year > 0) {
            $q->whereYear('date', (int) $year);
        }

        if (is_numeric($month) && (int) $month >= 1 && (int) $month <= 12) {
            $q->whereMonth('date', (int) $month);
        }

        // ✅ detect if ANY filters are active (for card logic)
        $hasAnyFilter =
            ($search !== '') ||
            (is_numeric($year) && (int)$year > 0) ||
            (is_numeric($month) && (int)$month >= 1 && (int)$month <= 12) ||
            (is_numeric($accountId) && (int)$accountId > 0) ||
            (is_numeric($officeEmployeeId) && (int)$officeEmployeeId > 0);

        // ✅ FILTERED outcome (based on $q)
        $filteredOutcome = (clone $q)->sum('money_amount_number');

        // ✅ GLOBAL totals
        $totalBalanceAllAccounts = (string) (Account::query()->sum('price') ?? 0);

        $totalIncomeAll = '0.00';
        if (Schema::hasTable('incomes') && Schema::hasColumn('incomes', 'amount')) {
            $totalIncomeAll = (string) (DB::table('incomes')->sum('amount') ?? 0);
        }

        $totalOutcomeAll = (string) (Finance::query()->sum('money_amount_number') ?? 0);

        // ✅ FILTERED balance (only meaningful if account_id exists)
        $filteredBalance = null;
        if (is_numeric($accountId) && (int) $accountId > 0) {
            $filteredBalance = (string) (Account::query()->where('id', (int) $accountId)->sum('price') ?? 0);
        }

        // ✅ FILTERED income (only if incomes table supports date/account_id columns)
        $filteredIncome = null;
        if (Schema::hasTable('incomes') && Schema::hasColumn('incomes', 'amount')) {
            $incomeQ = DB::table('incomes');

            if (Schema::hasColumn('incomes', 'account_id') && is_numeric($accountId) && (int)$accountId > 0) {
                $incomeQ->where('account_id', (int) $accountId);
            }

            // only apply year/month if incomes has date
            if (Schema::hasColumn('incomes', 'date')) {
                if (is_numeric($year) && (int)$year > 0) {
                    $incomeQ->whereYear('date', (int) $year);
                }
                if (is_numeric($month) && (int)$month >= 1 && (int)$month <= 12) {
                    $incomeQ->whereMonth('date', (int) $month);
                }
            }

            $filteredIncome = (string) ($incomeQ->sum('amount') ?? 0);
        }

        $summary = [
            // global
            'total_balance_all_accounts' => $totalBalanceAllAccounts,
            'total_income_all'           => $totalIncomeAll,
            'total_outcome_all'          => $totalOutcomeAll,

            // filtered
            'filtered_outcome'           => (string) ($filteredOutcome ?? 0),
            'filtered_income'            => $filteredIncome,
            'filtered_balance'           => $filteredBalance,

            // helper
            'has_filters'                => $hasAnyFilter,
        ];

        $collection = FinanceResource::collection(
            $q->paginate($perPage, ['*'], 'page', $page)
        );

        return $collection->additional([
            'summary' => $summary,
        ]);
    }

    public function show(Finance $finance)
    {
        $finance->load([
            'resource:id,name,code_number,phone_number,nick_name',
            'officeEmployee:id,name,code_number,phone_number',
            'account:id,name,price',
            'event:id,finance_status,finance_paid_at',
            'information:id,finance_status,finance_paid_at',
        ]);

        return new FinanceResource($finance);
    }

    public function store(Request $request)
    {
        $data = $this->validatePayload($request);

        $finance = DB::transaction(function () use ($data) {
            $finance = Finance::create($data);

            $this->applyAccountDelta(
                (int) $data['account_id'],
                -1 * (float) $data['money_amount_number']
            );

            $this->markLinkedPaid($data);

            return $finance;
        });

        $finance->load([
            'resource:id,name,code_number,phone_number,nick_name',
            'officeEmployee:id,name,code_number,phone_number',
            'account:id,name,price',
            'event:id,finance_status,finance_paid_at',
            'information:id,finance_status,finance_paid_at',
        ]);

        return new FinanceResource($finance);
    }

    public function update(Request $request, Finance $finance)
    {
        $data = $this->validatePayload($request);

        DB::transaction(function () use ($finance, $data) {
            $oldAccountId = (int) $finance->account_id;
            $oldAmount    = (float) $finance->money_amount_number;

            $finance->update($data);

            $newAccountId = (int) $finance->account_id;
            $newAmount    = (float) $finance->money_amount_number;

            if ($oldAccountId === $newAccountId) {
                $delta = $oldAmount - $newAmount;
                if ($delta != 0.0) {
                    $this->applyAccountDelta($newAccountId, $delta);
                }
            } else {
                $this->applyAccountDelta($oldAccountId, +1 * $oldAmount);
                $this->applyAccountDelta($newAccountId, -1 * $newAmount);
            }

            $this->markLinkedPaid($data);
        });

        $finance->refresh()->load([
            'resource:id,name,code_number,phone_number,nick_name',
            'officeEmployee:id,name,code_number,phone_number',
            'account:id,name,price',
            'event:id,finance_status,finance_paid_at',
            'information:id,finance_status,finance_paid_at',
        ]);

        return new FinanceResource($finance);
    }

    public function destroy(Finance $finance)
    {
        DB::transaction(function () use ($finance) {
            $this->applyAccountDelta(
                (int) $finance->account_id,
                +1 * (float) $finance->money_amount_number
            );

            $finance->delete();
        });

        return response()->json(['message' => 'Deleted'], 200);
    }

    // =========================
    // Helpers
    // =========================

    private function validatePayload(Request $request): array
    {
        return $request->validate([
            'office_employee_id' => ['nullable', 'integer', 'exists:office_employees,id'],
            'resource_id'        => ['nullable', 'integer', 'exists:resources,id'],
            'account_id'         => ['required', 'integer', 'exists:accounts,id'],

            'event_id'           => ['nullable', 'integer', 'exists:events,id'],
            'information_id'     => ['nullable', 'integer', 'exists:information,id'],

            'money_amount_number' => ['required', 'numeric', 'gt:0'],
            'money_amount_words'  => ['nullable', 'string', 'max:500'],

            'date'            => ['nullable', 'date'],
            'employee_phone'  => ['nullable', 'string', 'max:50'],
            'expense_result'  => ['nullable', 'string', 'max:255'],

            'money_distributer' => ['nullable', 'string', 'max:255'],
            'payer'            => ['nullable', 'string', 'max:255'],
            'receiver'         => ['nullable', 'string', 'max:255'],

            'account_type' => ['nullable', 'string', 'max:120'],
            'notes'        => ['nullable', 'string'],
        ]);
    }

    private function applyAccountDelta(int $accountId, float $delta): void
    {
        $acc = Account::lockForUpdate()->findOrFail($accountId);
        $acc->price = (float) $acc->price + $delta;
        $acc->save();
    }

    private function markLinkedPaid(array $data): void
    {
        $now = now();

        if (!empty($data['event_id'])) {
            Event::where('id', $data['event_id'])->update([
                'finance_status'  => 'paid',
                'finance_paid_at' => $now,
            ]);
        }

        if (!empty($data['information_id'])) {
            Information::where('id', $data['information_id'])->update([
                'finance_status'  => 'paid',
                'finance_paid_at' => $now,
            ]);
        }
    }

    public function years()
    {
        $years = Finance::query()
            ->whereNotNull('date')
            ->selectRaw('YEAR(date) as year')
            ->distinct()
            ->orderByDesc('year')
            ->pluck('year');

        return response()->json($years);
    }
}
