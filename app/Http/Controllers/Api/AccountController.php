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
        $perPage = (int) $request->get('perPage', 25);
        $search  = $request->get('search');

        $query = Account::query()
            ->with(['accountType:id,name'])
            ->search($search)
            ->latest('id');

        return AccountResource::collection($query->paginate($perPage));
    }

    public function show(Account $account)
    {
        $account->load(['accountType:id,name']);
        return new AccountResource($account);
    }

    public function store(Request $request)
    {
        $data = $this->validatePayload($request);

        $row = Account::create($data);
        $row->load(['accountType:id,name']);

        return new AccountResource($row);
    }

    public function update(Request $request, Account $account)
    {
        $data = $this->validatePayload($request);

        $account->update($data);
        $account->load(['accountType:id,name']);

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
            'name' => ['required', 'string', 'max:255'],
            'price' => ['nullable', 'numeric'],
            'account_type_id' => ['nullable', 'integer', 'exists:account_types,id'],
            'details' => ['nullable', 'string'],
        ]);
    }
}
