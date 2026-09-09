<?php

namespace App\Http\Controllers\fifthyformatsthree;

use App\Http\Controllers\Controller;
use App\Models\fifthyformatsthree\Tujaran;
use App\Http\Resources\fifthyformatsthree\TujaranResource;
use App\Http\Requests\fifthyformatsthree\StoreTujaranRequest;
use App\Http\Requests\fifthyformatsthree\UpdateTujaranRequest;
use App\Services\NotificationService; // 🔔 notifications
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Exports\TujaranExport;
use Maatwebsite\Excel\Facades\Excel;

class TujaranController extends Controller
{
    private function isDuplicateKey(QueryException $e): bool
    {
        return (int) ($e->errorInfo[1] ?? 0) === 1062;
    }

    private function duplicateResponse()
    {
        return response()->json([
            'message' => 'دا ریکارډ مخکې ثبت شوی دی.',
            'errors'  => [
                'duplicate' => [
                    'د همدې معلوماتو سره (نوم، د پلار نوم او اصلي ادرس) ریکارډ مخکې له مخکې موجود دی.',
                ],
            ],
        ], 422);
    }

    public function index(Request $request)
    {
        $perPage = (int) $request->input('perPage', 10);
        $search  = trim((string) $request->input('search', ''));
        $searchField = trim((string) $request->input('search_field', ''));
        $searchValue = trim((string) $request->input('search_value', ''));

        $allowedSearchFields = [
            
         'name',
        'father_name',
        'picture',

        // Main address
        'main_province',
        'main_district',
        'main_village',

        // Current address
        'now_province',
        'now_zone',
        'now_district',
        'now_village',
        'now_specific_place',
        'now_street_number',
        'Province_addr',
        'now_home_number',

        // Work & org
        'old_job',
        'now_job',
        'organ',

        // Other
        'guard_guntity',   // kept spelling as provided in the note
        'phone_number',
        'tahdid_mutawaji',
        'people_type',

        'note',
        'coordinates',
        'resource_id',
        ];

        $q = Tujaran::query()
            ->with(['resource:id,name,code_number,phone_number'])
            ->latest();

        if ($search !== '' && $searchField === '') {
            $q->where(function ($qq) use ($search) {
                $qq->where('name', 'like', "%{$search}%")
                   ->orWhere('father_name', 'like', "%{$search}%")
                   ->orWhere('organ', 'like', "%{$search}%")
                   ->orWhere('now_province', 'like', "%{$search}%");
            });
        }

        if ($searchField !== '' && in_array($searchField, $allowedSearchFields, true)) {
            $value = $searchValue !== '' ? $searchValue : $search;

            if ($value !== '') {
                $q->where($searchField, 'like', "%{$value}%");
            }
        }

        return TujaranResource::collection($q->paginate($perPage));
    }

    public function filterOptions(Request $request)
    {
        $field = trim((string) $request->query('field', ''));
        $q = trim((string) $request->query('q', ''));

        $allowedFields = [
            
         'name',
        'father_name',
        'picture',

        // Main address
        'main_province',
        'main_district',
        'main_village',

        // Current address
        'now_province',
        'now_zone',
        'now_district',
        'now_village',
        'now_specific_place',
        'now_street_number',
        'Province_addr',
        'now_home_number',

        // Work & org
        'old_job',
        'now_job',
        'organ',

        // Other
        'guard_guntity',   // kept spelling as provided in the note
        'phone_number',
        'tahdid_mutawaji',
        'people_type',

        'note',
        'coordinates',
        'resource_id',
        ];

        if ($field === '' || !in_array($field, $allowedFields, true)) {
            return response()->json([
                'data' => [
                    'options' => [],
                ],
            ]);
        }

        $query = Tujaran::query()
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

    public function store(StoreTujaranRequest $request)
    {
        $user = $request->user(); // 🔔

        try {
            return DB::transaction(function () use ($request, $user) {
                $data = $request->validated();

                // ✅ picture upload like others
                if ($request->hasFile('picture')) {
                    $data['picture'] = $request->file('picture')->store('tujarans', 'public');
                }

                $item = Tujaran::create($data)->load(['resource:id,name,code_number,phone_number']);

                NotificationService::record(
                    $user,
                    module: 'تاجران ، علما ، متنفذین او عالي رتبه اشخاص',
                    action: 'created',
                    title: 'نوی ریکارډ ثبت شو',
                    message: 'نوم: ' . ($item->name ?? ''),
                    routeName: 'tujarans',
                    routeParams: ['id' => $item->id],
                    level: 'success',
                    changes: null
                );

                return (new TujaranResource($item))
                    ->additional(['message' => 'Created'])
                    ->response()
                    ->setStatusCode(201);
            });
        } catch (QueryException $e) {
            if ($this->isDuplicateKey($e)) {
                return $this->duplicateResponse();
            }
            throw $e;
        }
    }

    public function show(Tujaran $tujaran)
    {
        $tujaran->load(['resource:id,name,code_number,phone_number']);
        return TujaranResource::make($tujaran);
    }

    public function update(UpdateTujaranRequest $request, Tujaran $tujaran)
    {
        $user = $request->user(); // 🔔

        try {
            return DB::transaction(function () use ($request, $tujaran, $user) {
                $original  = $tujaran->getOriginal();
                $validated = $request->validated();
                $changes   = [];

                foreach ($validated as $field => $newValue) {
                    if (array_key_exists($field, $original)) {
                        $oldValue = $original[$field];
                        if ($oldValue != $newValue) {
                            $changes[$field] = ['old' => $oldValue, 'new' => $newValue];
                        }
                    }
                }

                // ✅ update picture like others + delete old
                if ($request->hasFile('picture')) {
                    if ($tujaran->picture) {
                        Storage::disk('public')->delete($tujaran->picture);
                    }
                    $validated['picture'] = $request->file('picture')->store('tujarans', 'public');
                    $changes['picture'] = ['old' => $original['picture'] ?? null, 'new' => $validated['picture']];
                }

                $tujaran->update($validated);
                $tujaran->load(['resource:id,name,code_number,phone_number']);

                NotificationService::record(
                    $user,
                    module: 'تاجران ، علما ، متنفذین او عالي رتبه اشخاص',
                    action: 'updated',
                    title: 'ریکارډ اصلاح شو',
                    message: 'نوم: ' . ($tujaran->name ?? ''),
                    routeName: 'tujarans',
                    routeParams: ['id' => $tujaran->id],
                    level: 'info',
                    changes: !empty($changes) ? $changes : null
                );

                return TujaranResource::make($tujaran);
            });
        } catch (QueryException $e) {
            if ($this->isDuplicateKey($e)) {
                return $this->duplicateResponse();
            }
            throw $e;
        }
    }

    public function destroy(Request $request, Tujaran $tujaran)
    {
        $user = $request->user(); // 🔔

        return DB::transaction(function () use ($tujaran, $user) {
            $name = $tujaran->name;

            // ✅ delete picture file too
            if ($tujaran->picture) {
                Storage::disk('public')->delete($tujaran->picture);
            }

            $tujaran->delete();

            NotificationService::record(
                $user,
                module: 'تاجران ، علما ، متنفذین او عالي رتبه اشخاص',
                action: 'deleted',
                title: 'ریکارډ حذف شو',
                message: 'نوم: ' . ($name ?? ''),
                routeName: null,
                routeParams: ['tujarans'],
                level: 'warning',
                changes: null
            );

            return response()->noContent();
        });
    }

    public function bulkDelete(Request $request)
    {
        $user = $request->user(); // 🔔

        $ids = $request->input('ids', []);
        $ids = is_array($ids) ? array_values(array_filter($ids)) : [];

        return DB::transaction(function () use ($ids, $user) {
            if (empty($ids)) {
                return response()->noContent();
            }

            // ✅ delete pictures for all selected
            $pics = Tujaran::whereIn('id', $ids)->pluck('picture')->filter()->values()->all();
            foreach ($pics as $p) {
                Storage::disk('public')->delete($p);
            }

            Tujaran::whereIn('id', $ids)->delete();

            NotificationService::record(
                $user,
                module: 'تاجران ، علما ، متنفذین او عالي رتبه اشخاص',
                action: 'bulk_deleted',
                title: 'ډله‌ییز حذف ترسره شو',
                message: 'حذف شوي ریکارډونه: ' . count($ids),
                routeName: null,
                routeParams: ['tujarans'],
                level: 'warning',
                changes: null
            );

            return response()->noContent();
        });
    }
    private function printSearchFields(): array
{
    return [
        'name',
        'father_name',
        'picture',

        'main_province',
        'main_district',
        'main_village',

        'now_province',
        'now_zone',
        'now_district',
        'now_village',
        'now_specific_place',
        'now_street_number',
        'Province_addr',
        'now_home_number',

        'old_job',
        'now_job',
        'organ',

        'guard_guntity',
        'phone_number',
        'tahdid_mutawaji',
        'people_type',

        'note',
        'coordinates',
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
        $query->where(function ($qq) use ($search) {
            $qq->where('name', 'like', "%{$search}%")
                ->orWhere('father_name', 'like', "%{$search}%")
                ->orWhere('main_province', 'like', "%{$search}%")
                ->orWhere('main_district', 'like', "%{$search}%")
                ->orWhere('main_village', 'like', "%{$search}%")
                ->orWhere('now_province', 'like', "%{$search}%")
                ->orWhere('now_zone', 'like', "%{$search}%")
                ->orWhere('now_district', 'like', "%{$search}%")
                ->orWhere('now_village', 'like', "%{$search}%")
                ->orWhere('now_specific_place', 'like', "%{$search}%")
                ->orWhere('now_street_number', 'like', "%{$search}%")
                ->orWhere('Province_addr', 'like', "%{$search}%")
                ->orWhere('now_home_number', 'like', "%{$search}%")
                ->orWhere('old_job', 'like', "%{$search}%")
                ->orWhere('now_job', 'like', "%{$search}%")
                ->orWhere('organ', 'like', "%{$search}%")
                ->orWhere('guard_guntity', 'like', "%{$search}%")
                ->orWhere('phone_number', 'like', "%{$search}%")
                ->orWhere('tahdid_mutawaji', 'like', "%{$search}%")
                ->orWhere('people_type', 'like', "%{$search}%")
                ->orWhere('note', 'like', "%{$search}%")
                ->orWhere('coordinates', 'like', "%{$search}%")
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
    } elseif ($cardFilter === 'withGuards') {
        $query->whereNotNull('guard_guntity')
            ->whereRaw("TRIM(guard_guntity) != ''");
    }

    return $query;
}

public function printView(Request $request)
{
    $query = Tujaran::query()
        ->with(['resource:id,name,code_number,phone_number']);

    $this->applyPrintFilters($query, $request);

    $rows = $query
        ->latest()
        ->get()
        ->map(function ($row) {
            $row->resource_code =
                $row->resource?->code_number
                ?? $row->resource_id
                ?? '-';

            $row->resource_phone =
                $row->resource?->phone_number
                ?? '-';

            // Your table header uses resource_id, so print shows resource code there
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
    $title           = $request->input('title', 'چاپ تجاران');

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
    $tujaran = Tujaran::findOrFail($id);

    return $this->removePictureFileAndClear($tujaran);
}  

    public function exportExcel(Request $request)
    {
        $filters = $request->only(['search', 'search_field', 'search_value', 'resource_id']);
        return Excel::download(new TujaranExport($filters), 'tujaran_' . now()->format('Y_m_d_His') . '.xlsx');
    }
}