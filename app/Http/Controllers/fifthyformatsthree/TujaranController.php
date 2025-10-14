<?php

namespace App\Http\Controllers\fifthyformatsthree;

use App\Http\Controllers\Controller;
use App\Models\fifthyformatsthree\Tujaran;
use App\Http\Resources\fifthyformatsthree\TujaranResource;
use App\Http\Requests\fifthyformatsthree\StoreTujaranRequest;
use App\Http\Requests\fifthyformatsthree\UpdateTujaranRequest;
use Illuminate\Http\Request;

class TujaranController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) $request->input('perPage', 10);
        $search  = $request->input('search');

        $q = Tujaran::query()
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

        return TujaranResource::collection($q->paginate($perPage));
    }

    public function store(StoreTujaranRequest $request)
    {
        $item = Tujaran::create($request->validated())
            ->load(['resource:id,name,code_number,phone_number']);

        return TujaranResource::make($item);
    }

    public function show(Tujaran $tujaran)
    {
        $tujaran->load(['resource:id,name,code_number,phone_number']);
        return TujaranResource::make($tujaran);
    }

    public function update(UpdateTujaranRequest $request, Tujaran $tujaran)
    {
        $tujaran->update($request->validated());
        $tujaran->load(['resource:id,name,code_number,phone_number']);

        return TujaranResource::make($tujaran);
    }

    public function destroy(Tujaran $tujaran)
    {
        $tujaran->delete();
        return response()->noContent();
    }

    public function bulkDelete(Request $request)
    {
        $ids = (array) $request->input('ids', []);
        Tujaran::destroy($ids);
        return response()->noContent();
    }
}
