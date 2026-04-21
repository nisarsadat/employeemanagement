<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\IncomeResource;
use App\Models\Account;
use App\Models\Income;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class IncomeController extends Controller
{
    private function searchableColumns(): array
    {
        return [
            'account_name',
            'amount',
            'received_at',
            'note',
        ];
    }

    private function resolveSearchField(?string $field): ?string
    {
        $field = trim((string) $field);

        return in_array($field, $this->searchableColumns(), true) ? $field : null;
    }

    private function applyIncomeSearch($query, string $search = '', ?string $searchField = null, string $searchValue = '')
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

        if (trim($search) === '') {
            return $query;
        }

        return $query->where(function ($q) use ($search) {
            $q->where('amount', 'like', "%{$search}%")
                ->orWhere('received_at', 'like', "%{$search}%")
                ->orWhere('note', 'like', "%{$search}%")
                ->orWhereHas('account', function ($qq) use ($search) {
                    $qq->where('name', 'like', "%{$search}%");
                });
        });
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
           

            'account_name' => DB::table('incomes')
                ->join('accounts', 'incomes.account_id', '=', 'accounts.id')
                ->select('accounts.name as value')
                ->whereNotNull('accounts.name')
                ->where('accounts.name', '!=', '')
                ->when($q !== '', fn ($query) => $query->where('accounts.name', 'like', "%{$q}%"))
                ->distinct()
                ->orderBy('accounts.name')
                ->limit(50)
                ->pluck('value'),

            default => Income::query()
                ->select($field . ' as value')
                ->whereNotNull($field)
                ->where($field, '!=', '')
                ->when($q !== '', fn ($query) => $query->where($field, 'like', "%{$q}%"))
                ->distinct()
                ->orderBy($field)
                ->limit(50)
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
            'perPage'      => ['nullable', 'integer', 'min:1', 'max:2000'],
            'search'       => ['nullable', 'string', 'max:255'],
            'search_field' => ['nullable', 'string'],
            'search_value' => ['nullable', 'string'],
            'account_id'   => ['nullable', 'integer', 'exists:accounts,id'],
            'year'         => ['nullable', 'integer', 'min:2000', 'max:2100'],
            'month'        => ['nullable', 'integer', 'min:1', 'max:12'],
        ]);

        $perPage = (int) $request->get('perPage', 25);
        $search = trim((string) $request->get('search', ''));
        $searchField = $this->resolveSearchField($request->get('search_field'));
        $searchValue = trim((string) $request->get('search_value', ''));

        $accountId = $request->integer('account_id');
        $year = $request->integer('year');
        $month = $request->integer('month');

        $query = Income::query()
            ->with(['account:id,name,price'])
            ->latest('id');

        $this->applyIncomeSearch($query, $search, $searchField, $searchValue);

        if ($accountId) {
            $query->where('account_id', $accountId);
        }

        if ($year) {
            $query->whereYear('received_at', $year);
        }

        if ($month) {
            $query->whereMonth('received_at', $month);
        }

        return IncomeResource::collection($query->paginate($perPage));
    }

    public function show(Income $income)
    {
        $income->load(['account:id,name,price']);
        return new IncomeResource($income);
    }

    public function store(Request $request)
    {
        $data = $this->validatePayload($request);

        $row = DB::transaction(function () use ($data) {
            $account = Account::lockForUpdate()->findOrFail($data['account_id']);

            $amount = (float) $data['amount'];
            $account->update(['price' => (float) $account->price + $amount]);

            return Income::create($data);
        });

        $row->load(['account:id,name,price']);
        return new IncomeResource($row);
    }

    public function update(Request $request, Income $income)
    {
        $data = $this->validatePayload($request);

        $updated = DB::transaction(function () use ($data, $income) {
            $oldAccount = Account::lockForUpdate()->findOrFail($income->account_id);
            $newAccount = ($income->account_id == $data['account_id'])
                ? $oldAccount
                : Account::lockForUpdate()->findOrFail($data['account_id']);

            $oldAmount = (float) $income->amount;
            $newAmount = (float) $data['amount'];

            $oldAccount->update(['price' => (float) $oldAccount->price - $oldAmount]);
            $newAccount->update(['price' => (float) $newAccount->price + $newAmount]);

            $income->update($data);

            return $income;
        });

        $updated->load(['account:id,name,price']);
        return new IncomeResource($updated);
    }

    public function destroy(Income $income)
    {
        DB::transaction(function () use ($income) {
            $account = Account::lockForUpdate()->findOrFail($income->account_id);

            $amount = (float) $income->amount;
            $account->update(['price' => (float) $account->price - $amount]);

            $income->delete();
        });

        return response()->json(['message' => 'Deleted successfully']);
    }

   
    private function validatePayload(Request $request): array
{
    return $request->validate([
        'account_id'     => ['required', 'integer', 'exists:accounts,id'],
        'amount'         => ['required', 'numeric', 'min:0'],
        'amount_by_word' => ['required', 'string'],
        'received_at'    => ['nullable', 'date'],
        'note'           => ['nullable', 'string'],
    ]);
}

    public function years()
    {
        return Income::query()
            ->selectRaw('YEAR(received_at) as year')
            ->whereNotNull('received_at')
            ->distinct()
            ->orderByDesc('year')
            ->pluck('year');
    }
}