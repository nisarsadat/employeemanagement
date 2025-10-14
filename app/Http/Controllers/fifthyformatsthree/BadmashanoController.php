<?php

namespace App\Http\Controllers\fifthyformatsthree;

use App\Http\Controllers\Controller;
use App\Models\fifthyformatsthree\Badmashano;
use App\Http\Resources\fifthyformatsthree\BadmashanoResource;
use App\Http\Requests\fifthyformatsthree\StoreBadmashanoRequest;
use App\Http\Requests\fifthyformatsthree\UpdateBadmashanoRequest;
use Illuminate\Http\Request;

class BadmashanoController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) $request->input('perPage', 10);
        $search  = $request->input('search');

        $q = Badmashano::query()
            ->with(['resource:id,name,code_number,phone_number'])
            ->latest();

        if ($search) {
            $q->where(function ($qq) use ($search) {
                $qq->where('name', 'like', "%{$search}%")
                   ->orWhere('nickname', 'like', "%{$search}%")
                   ->orWhere('father_name', 'like', "%{$search}%");
            });
        }

        return BadmashanoResource::collection($q->paginate($perPage));
    }

    public function store(StoreBadmashanoRequest $request)
    {
        $item = Badmashano::create($request->validated())
            ->load(['resource:id,name,code_number,phone_number']);

        return BadmashanoResource::make($item);
    }

    public function show(Badmashano $badmashano)
    {
        $badmashano->load(['resource:id,name,code_number,phone_number']);
        return BadmashanoResource::make($badmashano);
    }

    public function update(UpdateBadmashanoRequest $request, Badmashano $badmashano)
    {
        $badmashano->update($request->validated());
        $badmashano->load(['resource:id,name,code_number,phone_number']);

        return BadmashanoResource::make($badmashano);
    }

    public function destroy(Badmashano $badmashano)
    {
        $badmashano->delete();
        return response()->noContent();
    }

    public function bulkDelete(Request $request)
    {
        $ids = (array) $request->input('ids', []);
        Badmashano::destroy($ids);
        return response()->noContent();
    }
}
