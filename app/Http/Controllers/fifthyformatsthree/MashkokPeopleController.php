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
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\MashkokPeopleExport;

class MashkokPeopleController extends Controller
{
    private function allowedSearchFields(): array
    {
        $table = (new MashkokPeople())->getTable();

        $candidates = [
                   'information_id',

        'picture',
        'name',
        'father_name',

        'main_province',
        'main_district',
        'main_village',

        'now_province',
        'now_zone',
        'Province_addr',
        'now_districts',     // kept as written in your note
        'now_village',
        'now_specific_place',
        'now_street_number',
        'now_home_number',
        'coordinates',

        'old_job',
        'now_job',
        'phone_number',
        'mashkokyat_type',
        'mushkokyat_points', // kept spelling as requested

        'note',
        'tafseel',
        'resource_id',
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
    private function printSearchFields(): array
{
    return [
        'information_id',

        'name',
        'father_name',

        'main_province',
        'main_district',
        'main_village',

        'now_province',
        'now_zone',
        'Province_addr',
        'now_districts',
        'now_village',
        'now_specific_place',
        'now_street_number',
        'now_home_number',
        'coordinates',

        'old_job',
        'now_job',
        'phone_number',
        'mashkokyat_type',
        'mushkokyat_points',

        'note',
        'tafseel',
        'resource_id',
        'resource_code',
        'resource_phone',
    ];
}

private function applyPrintFilters($query, Request $request)
{
    $search           = trim((string) $request->query('search', ''));
    $searchField      = trim((string) $request->query('search_field', ''));
    $searchValue      = trim((string) $request->query('search_value', ''));
    $resourceId       = $request->query('resource_id');
    $fiftySixFormatId = $request->query('fifty_six_format_id');
    $cardFilter       = trim((string) $request->query('card_filter', 'all'));

    $allowedFields = $this->printSearchFields();

    if ($search !== '' && $searchField === '') {
        $query->where(function ($w) use ($search) {
            $w->where('name', 'like', "%{$search}%")
                ->orWhere('father_name', 'like', "%{$search}%")
                ->orWhere('phone_number', 'like', "%{$search}%")
                ->orWhere('main_province', 'like', "%{$search}%")
                ->orWhere('main_district', 'like', "%{$search}%")
                ->orWhere('main_village', 'like', "%{$search}%")
                ->orWhere('now_province', 'like', "%{$search}%")
                ->orWhere('now_zone', 'like', "%{$search}%")
                ->orWhere('Province_addr', 'like', "%{$search}%")
                ->orWhere('now_districts', 'like', "%{$search}%")
                ->orWhere('now_village', 'like', "%{$search}%")
                ->orWhere('now_specific_place', 'like', "%{$search}%")
                ->orWhere('now_street_number', 'like', "%{$search}%")
                ->orWhere('now_home_number', 'like', "%{$search}%")
                ->orWhere('coordinates', 'like', "%{$search}%")
                ->orWhere('old_job', 'like', "%{$search}%")
                ->orWhere('now_job', 'like', "%{$search}%")
                ->orWhere('mashkokyat_type', 'like', "%{$search}%")
                ->orWhere('mushkokyat_points', 'like', "%{$search}%")
                ->orWhere('note', 'like', "%{$search}%")
                ->orWhere('tafseel', 'like', "%{$search}%")
                ->orWhereHas('resource', function ($r) use ($search) {
                    $r->where('code_number', 'like', "%{$search}%")
                        ->orWhere('phone_number', 'like', "%{$search}%");
                });
        });
    }

    if ($searchField !== '' && in_array($searchField, $allowedFields, true)) {
        $value = $searchValue !== '' ? $searchValue : $search;

        if ($value !== '') {
            if ($searchField === 'resource_code') {
                $query->whereHas('resource', function ($r) use ($value) {
                    $r->where('code_number', 'like', "%{$value}%");
                });
            } elseif ($searchField === 'resource_phone') {
                $query->whereHas('resource', function ($r) use ($value) {
                    $r->where('phone_number', 'like', "%{$value}%");
                });
            } else {
                $query->where($searchField, 'like', "%{$value}%");
            }
        }
    }

    if (is_numeric($resourceId) && (int) $resourceId > 0) {
        $query->where('resource_id', (int) $resourceId);
    }

    if (is_numeric($fiftySixFormatId) && (int) $fiftySixFormatId > 0) {
        $query->where('id', (int) $fiftySixFormatId);
    }

    if ($cardFilter === 'withCoordinates') {
        $query->whereNotNull('coordinates')
            ->whereRaw("TRIM(coordinates) != ''");
    } elseif ($cardFilter === 'withPhone') {
        $query->whereNotNull('phone_number')
            ->whereRaw("TRIM(phone_number) != ''");
    } elseif ($cardFilter === 'withJob') {
        $query->whereNotNull('now_job')
            ->whereRaw("TRIM(now_job) != ''");
    }

    return $query;
}

public function printView(Request $request)
{
    $query = MashkokPeople::query()
        ->with(['resource:id,name,code_number,phone_number']);

    $this->applyPrintFilters($query, $request);

    $rows = $query
        ->latest('id')
        ->get()
        ->map(function ($row) {
            $row->resource_code =
                $row->resource?->code_number
                ?? $row->resource_id
                ?? '-';

            $row->resource_phone =
                $row->resource?->phone_number
                ?? '-';

            // Because your table header key is resource_id, print will show source code instead of raw ID
            $row->resource_id =
                $row->resource?->code_number
                ?? $row->resource_id
                ?? '-';

            return $row;
        });

    $selectedColumns = $request->input('selected_columns', []);
    $columnLabels    = $request->input('column_labels', []);
    $printLabels     = $request->input('print_labels', []);
    $locale          = $request->input('locale', 'fa');
    $title           = $request->input('title', 'چاپ افراد مشکوک');

    return response()->view('prints.table-print', [
        'title'           => $title,
        'rows'            => $rows,
        'selectedColumns' => $selectedColumns,
        'columnLabels'    => $columnLabels,
        'printLabels'     => $printLabels,
        'locale'          => $locale,
    ]);
}
private function removePictureFileAndClear($model)
{
    if (!$model->picture) {
        return response()->json([
            'success' => false,
            'message' => 'عکس نشته',
        ], 404);
    }

    if (Storage::disk('public')->exists($model->picture)) {
        Storage::disk('public')->delete($model->picture);
    }

    $model->update([
        'picture' => null,
    ]);

    return response()->json([
        'success' => true,
        'message' => 'عکس حذف شو',
        'data' => $model->fresh(),
    ]);
}
public function deletePicture($id)
{
    $mashkokPeople = MashkokPeople::findOrFail($id);

    return $this->removePictureFileAndClear($mashkokPeople);
}

public function exportExcel(Request $request)
{
    $search = trim((string) $request->get('search', ''));
    $searchField = $request->get('search_field');
    $searchValue = $request->get('search_value');
    $fileName = 'mashkok_people_' . now()->format('Y_m_d_H_i_s') . '.xlsx';
    return Excel::download(new MashkokPeopleExport(['search' => $search, 'search_field' => $searchField, 'search_value' => $searchValue, 'resource_id' => $request->get('resource_id')]), $fileName);
}
}