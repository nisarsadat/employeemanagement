<?php

namespace App\Http\Controllers\fifthyformatsthree;

use App\Http\Controllers\Controller;
use App\Models\fifthyformatsthree\Madrasa;
use App\Http\Resources\fifthyformatsthree\MadrasaResource;
use App\Http\Requests\fifthyformatsthree\StoreMadrasaRequest;
use App\Http\Requests\fifthyformatsthree\UpdateMadrasaRequest;
use Illuminate\Http\Request;

class MadrasaController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) $request->input('perPage', 10);
        $search = $request->input('search');

        $q = Madrasa::query()
            ->with(['resource:id,name,code_number,phone_number'])
            ->latest();

        if ($search) {
            $q->where(function ($qq) use ($search) {
                $qq->where('name', 'like', "%{$search}%")
                   ->orWhere('madrasa_type', 'like', "%{$search}%")
                   ->orWhere('province', 'like', "%{$search}%");
            });
        }

        return MadrasaResource::collection($q->paginate($perPage));
    }

    public function store(StoreMadrasaRequest $request)
    {
        $item = Madrasa::create($request->validated())
            ->load(['resource:id,name,code_number,phone_number']);

        return MadrasaResource::make($item);
    }

    public function show(Madrasa $madrasa)
    {
        $madrasa->load(['resource:id,name,code_number,phone_number']);
        return MadrasaResource::make($madrasa);
    }

    public function update(UpdateMadrasaRequest $request, Madrasa $madrasa)
    {
        $madrasa->update($request->validated());
        $madrasa->load(['resource:id,name,code_number,phone_number']);

        return MadrasaResource::make($madrasa);
    }

    public function destroy(Madrasa $madrasa)
    {
        $madrasa->delete();
        return response()->noContent();
    }

    public function bulkDelete(Request $request)
    {
        $ids = (array) $request->input('ids', []);
        Madrasa::destroy($ids);
        return response()->noContent();
    }
}
