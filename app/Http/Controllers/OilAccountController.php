<?php

namespace App\Http\Controllers;

use App\Http\Requests\OilAccountRequest;
use App\Http\Resources\OilAccountResource;
use App\Models\OilAccount;
use Illuminate\Http\Request;

class OilAccountController extends Controller
{
    public function index(Request $request) {
        $perPage = (int)($request->integer('perPage') ?: 15);
        $search  = trim((string)$request->get('search', ''));

        $query = OilAccount::query();

        if ($search !== '') {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('details', 'like', "%{$search}%");
            });
        }

        $query->orderByDesc('id');

        return OilAccountResource::collection($query->paginate($perPage));
    }

    public function store(OilAccountRequest $request) {
        $data = $request->validated();
        $account = OilAccount::create($data);
        return new OilAccountResource($account);
    }

    public function show(OilAccount $oil_account) {
        return new OilAccountResource($oil_account);
    }

    public function update(OilAccountRequest $request, OilAccount $oil_account) {
        $oil_account->update($request->validated());
        return new OilAccountResource($oil_account);
    }

    public function destroy(OilAccount $oil_account) {
        // prevent deletion if you want; otherwise:
        $oil_account->delete();
        return response()->noContent();
    }
}
