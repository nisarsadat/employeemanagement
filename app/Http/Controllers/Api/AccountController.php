<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAccountRequest;
use App\Http\Requests\UpdateAccountRequest;
use App\Http\Resources\AccountResource;
use App\Models\Account;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int)($request->integer('perPage') ?: 15);
        $search  = trim((string)$request->get('search',''));
        $typeId  = $request->integer('account_type_id');

        $q = Account::query()->with(['accountType:id,name']);

        if ($search !== '') {
            $q->where(function ($w) use ($search) {
                $w->where('name','like',"%{$search}%")
                  ->orWhere('details','like',"%{$search}%");
            });
        }

        if ($typeId) {
            $q->where('account_type_id', $typeId);
        }

        return AccountResource::collection($q->paginate($perPage));
    }

    public function store(StoreAccountRequest $request)
    {
        $account = Account::create($request->validated());
        return new AccountResource($account->load(['accountType:id,name']));
    }

    public function show(Account $account)
    {
        return new AccountResource($account->load(['accountType:id,name']));
    }

    public function update(UpdateAccountRequest $request, Account $account)
    {
        $account->update($request->validated());
        return new AccountResource($account->load(['accountType:id,name']));
    }
}

