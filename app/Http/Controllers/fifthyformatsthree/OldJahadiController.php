<?php

namespace App\Http\Controllers\fifthyformatsthree;

use App\Http\Controllers\Controller;
use App\Models\fifthyformatsthree\OldJahadi;
use App\Http\Resources\fifthyformatsthree\OldJahadiResource;
use App\Http\Requests\fifthyformatsthree\StoreOldJahadiRequest;
use App\Http\Requests\fifthyformatsthree\UpdateOldJahadiRequest;
use Illuminate\Http\Request;

class OldJahadiController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) $request->input('perPage', 10);
        $search  = $request->input('search');

        $q = OldJahadi::query()
            ->with(['resource:id,name,code_number,phone_number'])
            ->latest();

        if ($search) {
            $q->where(function ($qq) use ($search) {
                $qq->where('name', 'like', "%{$search}%")
                   ->orWhere('father_name', 'like', "%{$search}%")
                   ->orWhere('now_province', 'like', "%{$search}%")
                   ->orWhere('tanzim', 'like', "%{$search}%");
            });
        }

        return OldJahadiResource::collection($q->paginate($perPage));
    }

    public function store(StoreOldJahadiRequest $request)
    {
        $item = OldJahadi::create($request->validated())
            ->load(['resource:id,name,code_number,phone_number']);

        return OldJahadiResource::make($item);
    }

    public function show(OldJahadi $oldJahadi)
    {
        $oldJahadi->load(['resource:id,name,code_number,phone_number']);
        return OldJahadiResource::make($oldJahadi);
    }

    public function update(UpdateOldJahadiRequest $request, OldJahadi $oldJahadi)
    {
        $oldJahadi->update($request->validated());
        $oldJahadi->load(['resource:id,name,code_number,phone_number']);

        return OldJahadiResource::make($oldJahadi);
    }

    public function destroy(OldJahadi $oldJahadi)
    {
        $oldJahadi->delete();
        return response()->noContent();
    }

    public function bulkDelete(Request $request)
    {
        $ids = (array) $request->input('ids', []);
        OldJahadi::destroy($ids);
        return response()->noContent();
    }
}
