<?php
namespace App\Http\Controllers\fifthyformatone;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\fifthyformatone\Mawad;
use App\Http\Resources\fifthyformatone\MawadResource;
use App\Http\Requests\fifthyformatone\StoreMawadRequest;
use App\Http\Requests\fifthyformatone\UpdateMawadRequest;

class MawadController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) $request->input('perPage', 10);
        $search  = $request->input('search');

        $q = Mawad::query()
            ->with(['resource:id,name,code_number,phone_number'])
            ->latest();

        if ($search) {
            $q->where('name', 'like', "%{$search}%");
        }

        return MawadResource::collection($q->paginate($perPage));
    }

    public function store(StoreMawadRequest $request)
    {
        $mawad = Mawad::create($request->validated())
            ->load(['resource:id,name,code_number,phone_number']);

        return MawadResource::make($mawad);
    }

    public function show(Mawad $mawad)
    {
        $mawad->load(['resource:id,name,code_number,phone_number']);
        return MawadResource::make($mawad);
    }

    public function update(UpdateMawadRequest $request, Mawad $mawad)
    {
        $mawad->update($request->validated());
        $mawad->load(['resource:id,name,code_number,phone_number']);

        return MawadResource::make($mawad);
    }

    public function destroy(Mawad $mawad)
    {
        $mawad->delete();
        return response()->noContent();
    }

    public function bulkDelete(Request $request)
    {
        $ids = $request->input('ids', []);
        Mawad::destroy($ids);
        return response()->noContent();
    }
}
