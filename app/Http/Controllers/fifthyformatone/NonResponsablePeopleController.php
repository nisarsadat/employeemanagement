<?php

namespace App\Http\Controllers\fifthyformatone;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\fifthyformatone\NonResponsablePeople;
use App\Http\Resources\fifthyformatone\NonResponsablePeopleResource;
use App\Http\Requests\fifthyformatone\StoreNonResponsablePeopleRequest;
use App\Http\Requests\fifthyformatone\UpdateNonResponsablePeopleRequest;

class NonResponsablePeopleController extends Controller
{
    /** GET /non-responsable-people */
    public function index(Request $request)
    {
        $perPage = (int) $request->input('perPage', 10);
        $search  = $request->input('search');

        $q = NonResponsablePeople::query()
            ->with(['resource:id,name,code_number,phone_number'])
            ->latest();

        if ($search) {
            $q->where(function ($w) use ($search) {
                $w->where('name', 'like', "%{$search}%")
                    ->orWhere('nick_name', 'like', "%{$search}%")
                    ->orWhere('father_name', 'like', "%{$search}%")
                    ->orWhere('phone_number', 'like', "%{$search}%");
            });
        }

        return NonResponsablePeopleResource::collection(
            $q->paginate($perPage)
        );
    }

    /** POST /non-responsable-people */
    public function store(StoreNonResponsablePeopleRequest $request)
    {
        $record = NonResponsablePeople::create($request->validated())
            ->load(['resource:id,name,code_number,phone_number']);

        return NonResponsablePeopleResource::make($record);
    }

    /** GET /non-responsable-people/{id} */
    public function show($id)
    {
        $record = NonResponsablePeople::with(['resource:id,name,code_number,phone_number'])
            ->findOrFail($id);

        return NonResponsablePeopleResource::make($record);
    }

    public function update(UpdateNonResponsablePeopleRequest $request, NonResponsablePeople $non_responsable_person)
    {
        $data = $request->validated();

        $non_responsable_person->update($data);
        $non_responsable_person->load('resource');

        return new NonResponsablePeopleResource($non_responsable_person);
    }

    /** DELETE /non-responsable-people/{id} */
    public function destroy($id)
    {
        $record = NonResponsablePeople::findOrFail($id);
        $record->delete(); // ✅ this will delete the record (or soft delete if the model uses SoftDeletes)

        return response()->noContent();
    }

    /** POST /non-responsable-people/bulk-delete { "ids": [1,2,3] } */
    public function bulkDelete(Request $request)
    {
        $ids = (array) $request->input('ids', []);

        if (! empty($ids)) {
            NonResponsablePeople::whereIn('id', $ids)->delete();
        }

        return response()->noContent();
    }
}
