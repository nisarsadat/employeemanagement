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
    public function index(Request $request)
    {
        $request->validate([
            'perPage' => ['nullable', 'integer', 'min:1', 'max:1000'],
            'search'  => ['nullable', 'string', 'max:255'],

            // existing range filters
            'from'    => ['nullable', 'date'],
            'to'      => ['nullable', 'date'],

            // ✅ NEW: year / month
            'year'    => ['nullable', 'integer', 'min:2000', 'max:2100'],
            'month'   => ['nullable', 'integer', 'min:1', 'max:12'],

            // optional
            'oil_account_id' => ['nullable', 'integer', 'exists:oil_accounts,id'],
        ]);

        $perPage = (int)($request->integer('perPage') ?: 15);
        $search  = trim((string)$request->get('search', ''));
        $from    = $request->date('from'); // YYYY-MM-DD
        $to      = $request->date('to');

        $year    = $request->integer('year');
        $month   = $request->integer('month');
        $accountId = $request->integer('oil_account_id');

        $query = IncomeOil::query()->with(['account:id,name,quantity']);

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('source', 'like', "%{$search}%")
                  ->orWhere('note', 'like', "%{$search}%");
            });
        }

        if ($accountId) {
            $query->where('oil_account_id', $accountId);
        }

        // ✅ NEW: year/month filter on income_oils.date
        if ($year)  $query->whereYear('date', $year);
        if ($month) $query->whereMonth('date', $month);

        // keep your from/to too (intersection is fine)
        if ($from && $to)      { $query->whereBetween('date', [$from, $to]); }
        elseif ($from)         { $query->whereDate('date', '>=', $from); }
        elseif ($to)           { $query->whereDate('date', '<=', $to); }

        $query->orderByDesc('date')->orderByDesc('id');

        // =========================
        // ✅ SUMMARY (cards)
        // =========================
        $totalQuantityAllAccounts = (string) OilAccount::query()->sum('quantity');

        $incomeTotalQuery = IncomeOil::query();
        if ($year)  $incomeTotalQuery->whereYear('date', $year);
        if ($month) $incomeTotalQuery->whereMonth('date', $month);
        if ($from)  $incomeTotalQuery->whereDate('date', '>=', $from);
        if ($to)    $incomeTotalQuery->whereDate('date', '<=', $to);
        $totalIncomeQuantity = (string) $incomeTotalQuery->sum('oil_quantity');

        $filteredIncomeQuery = IncomeOil::query();
        if ($search !== '') {
            $filteredIncomeQuery->where(function ($q) use ($search) {
                $q->where('source', 'like', "%{$search}%")
                  ->orWhere('note', 'like', "%{$search}%");
            });
        }
        if ($accountId) $filteredIncomeQuery->where('oil_account_id', $accountId);
        if ($year)  $filteredIncomeQuery->whereYear('date', $year);
        if ($month) $filteredIncomeQuery->whereMonth('date', $month);
        if ($from)  $filteredIncomeQuery->whereDate('date', '>=', $from);
        if ($to)    $filteredIncomeQuery->whereDate('date', '<=', $to);

        $filteredIncomeQuantity = (string) $filteredIncomeQuery->sum('oil_quantity');

        return IncomeOilResource::collection($query->paginate($perPage))->additional([
            'summary' => [
                'total_quantity_all_accounts' => $totalQuantityAllAccounts,
                'total_income_quantity'       => $totalIncomeQuantity,
                'filtered_income_quantity'    => $filteredIncomeQuantity,
            ],
        ]);
    }

    public function store(IncomeOilRequest $request)
    {
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

    public function show(IncomeOil $income_oil)
    {
        $income_oil->load(['account:id,name,quantity']);
        return new IncomeOilResource($income_oil);
    }

    public function update(IncomeOilRequest $request, IncomeOil $income_oil)
    {
        $payload = $request->validated();

        $updated = DB::transaction(function () use ($payload, $income_oil) {
            // revert previous account effect
            $oldAccount = OilAccount::lockForUpdate()->findOrFail($income_oil->oil_account_id);
            $oldAccount->quantity = bcsub((string)$oldAccount->quantity, (string)$income_oil->oil_quantity, 3);
            $oldAccount->save();

            // apply new payload
            $income_oil->update($payload);

            // apply new effect (same record's oil_account_id/oil_quantity after update)
            $newAccount = OilAccount::lockForUpdate()->findOrFail($income_oil->oil_account_id);
            $newAccount->quantity = bcadd((string)$newAccount->quantity, (string)$income_oil->oil_quantity, 3);
            $newAccount->save();

            return $income_oil->load(['account:id,name,quantity']);
        });

        return new IncomeOilResource($updated);
    }

    public function destroy(IncomeOil $income_oil)
    {
        DB::transaction(function () use ($income_oil) {
            $account = OilAccount::lockForUpdate()->findOrFail($income_oil->oil_account_id);
            $account->quantity = bcsub((string)$account->quantity, (string)$income_oil->oil_quantity, 3);
            $account->save();

            $income_oil->delete();
        });

        return response()->noContent();
    }

    // ✅ years endpoint for dropdown
    public function years()
    {
        return IncomeOil::query()
            ->selectRaw('YEAR(date) as year')
            ->whereNotNull('date')
            ->distinct()
            ->orderByDesc('year')
            ->pluck('year');
    }
}
