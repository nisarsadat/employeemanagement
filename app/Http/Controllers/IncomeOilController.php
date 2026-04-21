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
    private function searchableColumns(): array
    {
        return [
            'account_name',
            'oil_quantity',
            'source',
            'date',
            'note',
        ];
    }

    private function resolveSearchField(?string $field): ?string
    {
        $field = trim((string) $field);

        return in_array($field, $this->searchableColumns(), true) ? $field : null;
    }

    private function applyIncomeOilSearch($query, string $search = '', ?string $searchField = null, string $searchValue = '')
    {
        $searchField = $this->resolveSearchField($searchField);
        $value = trim($searchValue) !== '' ? trim($searchValue) : trim($search);

        if ($searchField) {
            if ($value === '') {
                return $query;
            }

            switch ($searchField) {
                case 'account_name':
                    $query->whereHas('account', fn ($q) => $q->where('name', 'like', "%{$value}%"));
                    break;

                default:
                    $query->where($searchField, 'like', "%{$value}%");
                    break;
            }

            return $query;
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('source', 'like', "%{$search}%")
                  ->orWhere('note', 'like', "%{$search}%");
            });
        }

        return $query;
    }

    public function filterOptions(Request $request)
    {
        $field = $this->resolveSearchField($request->query('field'));
        $q = trim((string) $request->query('q', ''));

        if (!$field) {
            return response()->json([
                'data' => [
                    'options' => [],
                ],
            ]);
        }

        $options = match ($field) {
            'account_name' => DB::table('income_oils')
                ->join('oil_accounts', 'income_oils.oil_account_id', '=', 'oil_accounts.id')
                ->select('oil_accounts.name as value')
                ->whereNotNull('oil_accounts.name')
                ->where('oil_accounts.name', '!=', '')
                ->when($q !== '', fn ($query) => $query->where('oil_accounts.name', 'like', "%{$q}%"))
                ->distinct()
                ->orderBy('oil_accounts.name')
                ->pluck('value'),

            default => IncomeOil::query()
                ->select($field . ' as value')
                ->whereNotNull($field)
                ->where($field, '!=', '')
                ->when($q !== '', fn ($query) => $query->where($field, 'like', "%{$q}%"))
                ->distinct()
                ->orderBy($field)
                ->pluck('value'),
        };

        return response()->json([
            'data' => [
                'options' => collect($options)
                    ->filter(fn ($value) => $value !== null && trim((string) $value) !== '')
                    ->map(fn ($value) => [
                        'title' => (string) $value,
                        'value' => (string) $value,
                    ])
                    ->values(),
            ],
        ]);
    }

    public function index(Request $request)
    {
        $request->validate([
            'perPage'        => ['nullable', 'integer', 'min:1', 'max:1000'],
            'search'         => ['nullable', 'string', 'max:255'],
            'search_field'   => ['nullable', 'string'],
            'search_value'   => ['nullable', 'string'],
            'from'           => ['nullable', 'date'],
            'to'             => ['nullable', 'date'],
            'year'           => ['nullable', 'integer', 'min:2000', 'max:2100'],
            'month'          => ['nullable', 'integer', 'min:1', 'max:12'],
            'oil_account_id' => ['nullable', 'integer', 'exists:oil_accounts,id'],
        ]);

        $perPage = (int) ($request->integer('perPage') ?: 15);
        $search = trim((string) $request->get('search', ''));
        $searchField = $this->resolveSearchField($request->get('search_field'));
        $searchValue = trim((string) $request->get('search_value', ''));
        $from = $request->date('from');
        $to = $request->date('to');

        $year = $request->integer('year');
        $month = $request->integer('month');
        $accountId = $request->integer('oil_account_id');

        $query = IncomeOil::query()->with(['account:id,name,quantity']);

        $this->applyIncomeOilSearch($query, $search, $searchField, $searchValue);

        if ($accountId) {
            $query->where('oil_account_id', $accountId);
        }

        if ($year) {
            $query->whereYear('date', $year);
        }

        if ($month) {
            $query->whereMonth('date', $month);
        }

        if ($from && $to) {
            $query->whereBetween('date', [$from, $to]);
        } elseif ($from) {
            $query->whereDate('date', '>=', $from);
        } elseif ($to) {
            $query->whereDate('date', '<=', $to);
        }

        $query->orderByDesc('date')->orderByDesc('id');

        $totalQuantityAllAccounts = (string) OilAccount::query()->sum('quantity');

        $incomeTotalQuery = IncomeOil::query();
        if ($year) {
            $incomeTotalQuery->whereYear('date', $year);
        }
        if ($month) {
            $incomeTotalQuery->whereMonth('date', $month);
        }
        if ($from) {
            $incomeTotalQuery->whereDate('date', '>=', $from);
        }
        if ($to) {
            $incomeTotalQuery->whereDate('date', '<=', $to);
        }
        $totalIncomeQuantity = (string) $incomeTotalQuery->sum('oil_quantity');

        $filteredIncomeQuery = IncomeOil::query();
        $this->applyIncomeOilSearch($filteredIncomeQuery, $search, $searchField, $searchValue);

        if ($accountId) {
            $filteredIncomeQuery->where('oil_account_id', $accountId);
        }
        if ($year) {
            $filteredIncomeQuery->whereYear('date', $year);
        }
        if ($month) {
            $filteredIncomeQuery->whereMonth('date', $month);
        }
        if ($from) {
            $filteredIncomeQuery->whereDate('date', '>=', $from);
        }
        if ($to) {
            $filteredIncomeQuery->whereDate('date', '<=', $to);
        }

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
            $account = OilAccount::lockForUpdate()->findOrFail($payload['oil_account_id']);

            $income = IncomeOil::create($payload);

            $account->quantity = bcadd((string) $account->quantity, (string) $payload['oil_quantity'], 3);
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
            $oldAccountId = $income_oil->oil_account_id;
            $oldQuantity = (string) $income_oil->oil_quantity;

            $newAccountId = $payload['oil_account_id'];
            $newQuantity = (string) $payload['oil_quantity'];

            $oldAccount = OilAccount::lockForUpdate()->find($oldAccountId);
            $newAccount = $oldAccountId == $newAccountId
                ? $oldAccount
                : OilAccount::lockForUpdate()->find($newAccountId);

            if ($oldAccount) {
                $oldAccount->quantity = bcsub((string) $oldAccount->quantity, $oldQuantity, 3);
                $oldAccount->save();
            }

            $income_oil->update($payload);

            if ($newAccount) {
                $newAccount->quantity = bcadd((string) $newAccount->quantity, $newQuantity, 3);
                $newAccount->save();
            }

            return $income_oil->load(['account:id,name,quantity']);
        });

        return new IncomeOilResource($updated);
    }
public function destroy(IncomeOil $income_oil)
{
    DB::transaction(function () use ($income_oil) {
        $account = OilAccount::lockForUpdate()->find($income_oil->oil_account_id);

        if ($account) {
            $account->quantity = bcsub(
                (string) $account->quantity,
                (string) $income_oil->oil_quantity,
                3
            );
            $account->save();
        }

        IncomeOil::whereKey($income_oil->id)->delete();
    });

    return response()->json([
        'message' => 'Deleted successfully',
    ]);
}

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