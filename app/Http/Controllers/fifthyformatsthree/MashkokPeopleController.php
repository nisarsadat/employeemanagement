<?php

namespace App\Http\Controllers\fifthyformatsthree;

use App\Http\Controllers\Controller;
use App\Models\fifthyformatsthree\MashkokPeople;
use App\Http\Resources\fifthyformatsthree\MashkokPeopleResource;
use App\Http\Requests\fifthyformatsthree\StoreMashkokPeopleRequest;
use App\Http\Requests\fifthyformatsthree\UpdateMashkokPeopleRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MashkokPeopleController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) $request->get('perPage', 10);
        $search  = trim((string) $request->get('search', ''));

        $q = MashkokPeople::query()
            ->with(['resource:id,name,code_number,phone_number'])
            ->latest('id');

        if ($search !== '') {
            $q->where(function ($w) use ($search) {
                $w->where('name', 'like', "%{$search}%")
                  ->orWhere('father_name', 'like', "%{$search}%")
                  ->orWhere('phone_number', 'like', "%{$search}%")
                  ->orWhere('now_province', 'like', "%{$search}%")
                  ->orWhere('now_districts', 'like', "%{$search}%")
                  ->orWhere('now_village', 'like', "%{$search}%");
            });
        }

        return MashkokPeopleResource::collection($q->paginate($perPage));
    }

    public function store(StoreMashkokPeopleRequest $request)
    {
        $data = $request->validated();

        // Picture upload (optional)
        if ($request->hasFile('picture')) {
            $path = $request->file('picture')->store('uploads/mashkok_people', 'public');
            $data['picture'] = "storage/{$path}";
        }

        $item = MashkokPeople::create($data)
            ->load(['resource:id,name,code_number,phone_number']);

        return MashkokPeopleResource::make($item)
            ->additional(['message' => 'Created'])
            ->response()->setStatusCode(201);
    }

    public function show(MashkokPeople $mashkokPeople)
    {
        $mashkokPeople->load(['resource:id,name,code_number,phone_number']);
        return MashkokPeopleResource::make($mashkokPeople);
    }

    public function update(UpdateMashkokPeopleRequest $request, MashkokPeople $mashkokPeople)
    {
        $data = $request->validated();

        // Handle picture replacement safely
        if ($request->hasFile('picture')) {
            // delete old if it was on 'public' disk under storage/
            $old = $mashkokPeople->picture;
            if ($old && str_starts_with($old, 'storage/')) {
                Storage::disk('public')->delete(str_replace('storage/', '', $old));
            }
            $path = $request->file('picture')->store('uploads/mashkok_people', 'public');
            $data['picture'] = "storage/{$path}";
        } else {
            // prevent nulling picture when no new file is sent
            unset($data['picture']);
        }

        $mashkokPeople->fill($data)->save();

        $mashkokPeople->load(['resource:id,name,code_number,phone_number']);
        return MashkokPeopleResource::make($mashkokPeople);
    }

    public function destroy(MashkokPeople $mashkokPeople)
    {
        // delete stored picture if applicable
        if ($mashkokPeople->picture && str_starts_with($mashkokPeople->picture, 'storage/')) {
            Storage::disk('public')->delete(str_replace('storage/', '', $mashkokPeople->picture));
        }

        $mashkokPeople->delete();
        return response()->json(['message' => 'Deleted']);
    }

    public function bulkDelete(Request $request)
    {
        $ids = array_filter((array) $request->input('ids', []));
        if (empty($ids)) {
            return response()->json(['message' => 'No IDs provided'], 422);
        }

        // Optionally clean up pictures for those rows
        $rows = MashkokPeople::whereIn('id', $ids)->get(['id', 'picture']);
        foreach ($rows as $row) {
            if ($row->picture && str_starts_with($row->picture, 'storage/')) {
                Storage::disk('public')->delete(str_replace('storage/', '', $row->picture));
            }
        }

        MashkokPeople::destroy($ids);

        return response()->noContent();
    }
}
