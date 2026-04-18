<?php

namespace App\Http\Controllers\fifthyformatsthree;

use App\Http\Controllers\Controller;
use App\Models\fifthyformatsthree\MashkokPeople;
use App\Http\Resources\fifthyformatsthree\MashkokPeopleResource;
use App\Http\Requests\fifthyformatsthree\StoreMashkokPeopleRequest;
use App\Http\Requests\fifthyformatsthree\UpdateMashkokPeopleRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class MashkokPeopleController extends Controller
{
    private function allowedSearchFields(): array
    {
        $table = (new MashkokPeople())->getTable();

        $candidates = [
            'name',
            'father_name',
            'phone_number',
            'now_province',
            'now_districts',
            'now_village',
            'main_province',
            'main_district',
            'main_village',
            'note',
            'tafseel',
            'address',
            'current_address',
            'original_address',
        ];

        return array_values(array_filter($candidates, fn ($field) => Schema::hasColumn($table, $field)));
    }

    public function index(Request $request)
    {
        $perPage = (int) $request->get('perPage', 10);
        $search  = trim((string) $request->get('search', ''));
        $searchField = trim((string) $request->get('search_field', ''));
        $searchValue = trim((string) $request->get('search_value', ''));

        $allowedSearchFields = $this->allowedSearchFields();

        $q = MashkokPeople::query()
            ->with(['resource:id,name,code_number,phone_number'])
            ->latest('id');

        if ($search !== '' && $searchField === '') {
            $q->where(function ($w) use ($search) {
                $w->where('name', 'like', "%{$search}%")
                    ->orWhere('father_name', 'like', "%{$search}%")
                    ->orWhere('phone_number', 'like', "%{$search}%")
                    ->orWhere('now_province', 'like', "%{$search}%")
                    ->orWhere('now_districts', 'like', "%{$search}%")
                    ->orWhere('now_village', 'like', "%{$search}%");
            });
        }

        if ($searchField !== '' && in_array($searchField, $allowedSearchFields, true)) {
            $value = $searchValue !== '' ? $searchValue : $search;

            if ($value !== '') {
                $q->where($searchField, 'like', "%{$value}%");
            }
        }

        return MashkokPeopleResource::collection($q->paginate($perPage));
    }

    /**
     * GET /mashkok-people/filter-options
     */
    public function filterOptions(Request $request)
    {
        $field = trim((string) $request->query('field', ''));
        $q     = trim((string) $request->query('q', ''));

        $allowedFields = $this->allowedSearchFields();

        if ($field === '' || !in_array($field, $allowedFields, true)) {
            return response()->json([
                'data' => [
                    'options' => [],
                ],
            ]);
        }

        $query = MashkokPeople::query()
            ->select($field)
            ->whereNotNull($field)
            ->where($field, '!=', '');

        if ($q !== '') {
            $query->where($field, 'like', "%{$q}%");
        }

        $options = $query
            ->distinct()
            ->limit(50)
            ->pluck($field)
            ->filter(fn ($v) => $v !== null && trim((string) $v) !== '')
            ->values()
            ->map(fn ($v) => [
                'title' => (string) $v,
                'value' => (string) $v,
            ]);

        return response()->json([
            'data' => [
                'options' => $options,
            ],
        ]);
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
            ->response()
            ->setStatusCode(201);
    }

    // ✅ load by ID instead of implicit binding
    public function show($id)
    {
        $mashkokPeople = MashkokPeople::with(['resource:id,name,code_number,phone_number'])
            ->findOrFail($id);

        return MashkokPeopleResource::make($mashkokPeople);
    }

    // ✅ also load by ID here (so we are consistent and avoid binding issues)
    public function update(UpdateMashkokPeopleRequest $request, $id)
    {
        $mashkokPeople = MashkokPeople::findOrFail($id);
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

        return MashkokPeopleResource::make($mashkokPeople)
            ->additional(['message' => 'Updated']);
    }

    // ✅ also use $id + findOrFail for destroy
    public function destroy($id)
    {
        $mashkokPeople = MashkokPeople::findOrFail($id);

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

        return response()->json(['message' => 'Deleted']);
    }
}