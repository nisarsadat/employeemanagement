<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAccountTypeRequest;
use App\Http\Requests\UpdateAccountTypeRequest;
use App\Http\Resources\AccountTypeResource;
use App\Models\AccountType;
use Illuminate\Http\Request;

class AccountTypeController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int)($request->integer('perPage') ?: 15);
        $search  = trim((string)$request->get('search',''));

        $q = AccountType::query();

        if ($search !== '') {
            $q->where('name','like',"%{$search}%");
        }

        // include count if useful for UI badges
        $q->withCount('accounts');

        return AccountTypeResource::collection($q->paginate($perPage));
    }

    public function store(StoreAccountTypeRequest $request)
    {
        $type = AccountType::create($request->validated());
        return new AccountTypeResource($type);
    }

    public function show(AccountType $accountType)
    {
        return new AccountTypeResource($accountType->loadCount('accounts'));
    }

    public function update(UpdateAccountTypeRequest $request, AccountType $accountType)
    {
        $accountType->update($request->validated());
        return new AccountTypeResource($accountType->loadCount('accounts'));
    }
}

