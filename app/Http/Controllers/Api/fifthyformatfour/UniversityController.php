<?php

namespace App\Http\Controllers\Api\fifthyformatfour;

use App\Http\Controllers\Controller;
use App\Http\Requests\fifthyformatfour\StoreUniversityRequest;
use App\Http\Requests\fifthyformatfour\UpdateUniversityRequest;
use App\Http\Resources\fifthyformatfour\UniversityResource;
use App\Models\fifthyformatfour\University;
use Illuminate\Http\Request;

class UniversityController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) $request->input('perPage', 10);
        $search  = $request->input('search');

        $q = University::query()
            ->with('resource')
            ->latest();

        if ($search) {
            $q->where(function ($qq) use ($search) {
                $qq->where('uni_name', 'like', "%{$search}%")
                   ->orWhere('responsible_name', 'like', "%{$search}%")
                   ->orWhere('responsible_phone', 'like', "%{$search}%")
                   ->orWhere('province', 'like', "%{$search}%");
            });
        }

        return UniversityResource::collection(
            $q->paginate($perPage)
        );
    }

    public function store(StoreUniversityRequest $request)
    {
        $data = $request->validated();

        $university = University::create($data)->load('resource');

        return UniversityResource::make($university);
    }

    public function show(University $university)
    {
        $university->load('resource');

        return UniversityResource::make($university);
    }

    public function update(UpdateUniversityRequest $request, University $university)
    {
        $data = $request->validated();

        $university->update($data);
        $university->load('resource');

        return UniversityResource::make($university);
    }

    public function destroy(University $university)
    {
        $university->delete();

        return response()->noContent();
    }
}
