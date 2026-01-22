<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AccountResource;
use App\Models\Account;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    public function index(Request $request)
    {
        // ✅ validate filters
        $request->validate([
            'perPage' => ['nullable', 'integer', 'min:1', 'max:2000'],
            'search'  => ['nullable', 'string', 'max:255'],
            'year'    => ['nullable', 'integer', 'min:2000', 'max:2100'],
            'month'   => ['nullable', 'integer', 'min:1', 'max:12'],
        ]);

        $perPage = (int) $request->get('perPage', 25);
        $search  = $request->get('search');

        $year  = $request->integer('year');
        $month = $request->integer('month');

        $query = Account::query()
            ->search($search)
            ->latest('id');

        // ✅ year/month filter on created_at (simple & safe)
        if ($year) {
            $query->whereYear('created_at', $year);
        }
        if ($month) {
            $query->whereMonth('created_at', $month);
        }

        return AccountResource::collection($query->paginate($perPage));
    }

    public function show(Account $account)
    {
        return new AccountResource($account);
    }

    public function store(Request $request)
    {
        $data = $this->validatePayload($request);

        $row = Account::create($data);

        return new AccountResource($row);
    }

    public function update(Request $request, Account $account)
    {
        $data = $this->validatePayload($request);

        $account->update($data);

        return new AccountResource($account);
    }

    public function destroy(Account $account)
    {
        $account->delete();

        return response()->json(['message' => 'Deleted successfully']);
    }

    private function validatePayload(Request $request): array
    {
        return $request->validate([
            'name'    => ['required', 'string', 'max:255'],
            'price'   => ['nullable', 'numeric'],
            'details' => ['nullable', 'string'],
        ]);
    }
    public function years()
    {
        return Account::query()
            ->selectRaw('YEAR(created_at) as year')
            ->whereNotNull('created_at')
            ->distinct()
            ->orderByDesc('year')
            ->pluck('year');
    }
}
