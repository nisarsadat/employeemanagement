<?php

namespace App\Http\Controllers\fifthyformatsthree;

use App\Http\Controllers\Controller;
use App\Models\fifthyformatsthree\Mujahedino;
use App\Http\Resources\fifthyformatsthree\MujahedinoResource;
use App\Http\Requests\fifthyformatsthree\StoreMujahedinoRequest;
use App\Http\Requests\fifthyformatsthree\UpdateMujahedinoRequest;
use Illuminate\Http\Request;

class MujahedinoController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) $request->input('perPage', 10);
        $search  = $request->input('search');

        $q = Mujahedino::query()
            ->with(['resource:id,name,code_number,phone_number'])
            ->latest();

        if ($search) {
            $q->where(function ($qq) use ($search) {
                $qq->where('name', 'like', "%{$search}%")
                   ->orWhere('father_name', 'like', "%{$search}%")
                   ->orWhere('organ', 'like', "%{$search}%")
                   ->orWhere('now_province', 'like', "%{$search}%");
            });
        }

        return MujahedinoResource::collection($q->paginate($perPage));
    }

    public function store(StoreMujahedinoRequest $request)
    {
        $item = Mujahedino::create($request->validated())
            ->load(['resource:id,name,code_number,phone_number']);

        return MujahedinoResource::make($item);
    }

    public function show(Mujahedino $mujahedino)
    {
        $mujahedino->load(['resource:id,name,code_number,phone_number']);
        return MujahedinoResource::make($mujahedino);
    }

    public function update(UpdateMujahedinoRequest $request, Mujahedino $mujahedino)
    {
        $mujahedino->update($request->validated());
        $mujahedino->load(['resource:id,name,code_number,phone_number']);

        return MujahedinoResource::make($mujahedino);
    }

    public function destroy(Mujahedino $mujahedino)
    {
        $mujahedino->delete();
        return response()->noContent();
    }

    public function bulkDelete(Request $request)
    {
        $ids = (array) $request->input('ids', []);
        Mujahedino::destroy($ids);
        return response()->noContent();
    }
}
