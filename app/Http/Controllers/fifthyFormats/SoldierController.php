<?php

namespace App\Http\Controllers\fifthyFormats;

use App\Http\Controllers\Controller;
use App\Http\Requests\fifthyFormats\StoreSoldierRequest;
use App\Http\Requests\fifthyFormats\UpdateSoldierRequest;
use App\Http\Resources\fifthyFormats\SoldierResource;
use App\Models\fifthyFormats\Soldier;
use App\Services\NotificationService; // 🔔 notifications
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class SoldierController extends Controller
{
    /**
     * Pashto duplicate error message (unique constraint)
     */
    private function duplicateErrorPashto(): array
    {
        return [
            'message' => 'دا ریکارډ مخکې ثبت شوی دی. مهرباني وکړئ تکراري معلومات مه داخلوی.',
            'errors'  => [
                'duplicate' => [
                    'دا ریکارډ مخکې ثبت شوی دی (همدا نوم، د پلار نوم او اصلي پته).',
                ],
            ],
        ];
    }

    private function isDuplicateKeyException(QueryException $e): bool
    {
        // MySQL duplicate entry = 1062
        return (int) ($e->errorInfo[1] ?? 0) === 1062;
    }

    public function index(Request $request)
    {
        $perPage = (int) $request->get('perPage', 15);
        $search  = trim((string) $request->get('search', ''));
        $rid     = $request->get('resource_id');
        $searchField = trim((string) $request->get('search_field', ''));
        $searchValue = trim((string) $request->get('search_value', ''));

        $sortBy  = $request->get('sort_by', 'created_at');
        $sortDir = in_array($request->get('sort_dir'), ['asc', 'desc'], true) ? $request->get('sort_dir') : 'desc';

        $allowedSearchFields = [
          
         'name',
        'father_name',
        'picture',
        'main_province','main_district','main_village',
        'now_province','now_zone','now_district','now_village',
        'specific_place','street_number','home_number',
        'Province_addr',
        'phone_number',
        'old_job','now_job','guard_amount','phone_number',
        'notes','resource_id', 'coordinates'
        ];

        $q = Soldier::query()->with([
            'resource' => fn ($qr) => $qr->select('id', 'name', 'code_number', 'phone_number'),
        ]);

        if ($search !== '' && $searchField === '') {
            $q->where(function ($qq) use ($search) {
                $qq->where('name', 'like', "%{$search}%")
                    ->orWhere('father_name', 'like', "%{$search}%")
                    ->orWhere('main_province', 'like', "%{$search}%")
                    ->orWhere('now_province', 'like', "%{$search}%")
                    ->orWhere('specific_place', 'like', "%{$search}%")
                    ->orWhere('old_job', 'like', "%{$search}%")
                    ->orWhere('now_job', 'like', "%{$search}%")
                    ->orWhere('phone_number', 'like', "%{$search}%");
            });
        }

        if ($searchField !== '' && in_array($searchField, $allowedSearchFields, true)) {
            $value = $searchValue !== '' ? $searchValue : $search;

            if ($value !== '') {
                $q->where($searchField, 'like', "%{$value}%");
            }
        }

        if ($rid) {
            $q->where('resource_id', $rid);
        }

        $q->orderBy($sortBy, $sortDir);

        return SoldierResource::collection($q->paginate($perPage));
    }

    public function filterOptions(Request $request)
    {
        $field = trim((string) $request->query('field', ''));
        $q = trim((string) $request->query('q', ''));

        $allowedFields = [
          
         'name',
        'father_name',
        'picture',
        'main_province','main_district','main_village',
        'now_province','now_zone','now_district','now_village',
        'specific_place','street_number','home_number',
        'Province_addr',
        'phone_number',
        'old_job','now_job','guard_amount','phone_number',
        'notes','resource_id', 'coordinates'
        ];

        if ($field === '' || !in_array($field, $allowedFields, true)) {
            return response()->json([
                'data' => [
                    'options' => [],
                ],
            ]);
        }

        $query = Soldier::query()
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

    public function show(Soldier $soldier)
    {
        $soldier->load([
            'resource' => fn ($qr) => $qr->select('id', 'name', 'code_number', 'phone_number'),
        ]);

        return new SoldierResource($soldier);
    }

    public function store(StoreSoldierRequest $request)
    {
        $user = $request->user(); // 🔔 who did this
        $storedPicturePath = null;

        try {
            return DB::transaction(function () use ($request, $user, &$storedPicturePath) {
                $data = $request->validated();

                // ✅ Picture upload (optional)
                if ($request->hasFile('picture')) {
                    $storedPicturePath = $request->file('picture')->store('soldiers/pictures', 'public');
                    $data['picture'] = $storedPicturePath;
                }

                $soldier = Soldier::create($data);

                $soldier->load([
                    'resource' => fn ($qr) => $qr->select('id', 'name', 'code_number', 'phone_number'),
                ]);

                // 🔔 Notification: CREATED
                NotificationService::record(
                    $user,
                    module: 'سرتیری',
                    action: 'created',
                    title: 'سرتیری  ثبت شوو',
                    message: 'نام سرتیری: ' . ($soldier->name ?? ''),
                    routeName: 'soldiers',              // ⚠️ set your real route name
                    routeParams: ['id' => $soldier->id],
                    level: 'success',
                    changes: null
                );

                return (new SoldierResource($soldier))
                    ->additional(['message' => 'Created'])
                    ->response()
                    ->setStatusCode(201);
            });
        } catch (QueryException $e) {
            // if DB fails and picture already stored, delete it
            if ($storedPicturePath) {
                Storage::disk('public')->delete($storedPicturePath);
            }

            if ($this->isDuplicateKeyException($e)) {
                return response()->json($this->duplicateErrorPashto(), 422);
            }
            throw $e;
        }
    }

    public function update(UpdateSoldierRequest $request, Soldier $soldier)
    {
        $user = $request->user(); // 🔔

        try {
            return DB::transaction(function () use ($request, $soldier, $user) {
                $validated = $request->validated();

                // ✅ Picture upload (optional) + remove old picture
                if ($request->hasFile('picture')) {
                    $oldPicture = $soldier->picture;

                    $newPath = $request->file('picture')->store('soldiers/pictures', 'public');
                    $validated['picture'] = $newPath;

                    // delete old after new stored
                    if ($oldPicture) {
                        Storage::disk('public')->delete($oldPicture);
                    }
                }

                // 🧮 build changes BEFORE update
                $original = $soldier->getOriginal();
                $changes  = [];

                foreach ($validated as $field => $newValue) {
                    if (array_key_exists($field, $original)) {
                        $oldValue = $original[$field];
                        if ($oldValue != $newValue) {
                            $changes[$field] = [
                                'old' => $oldValue,
                                'new' => $newValue,
                            ];
                        }
                    }
                }

                $soldier->update($validated);

                $soldier->load([
                    'resource' => fn ($qr) => $qr->select('id', 'name', 'code_number', 'phone_number'),
                ]);

                // 🔔 Notification: UPDATED + changes
                NotificationService::record(
                    $user,
                    module: 'سرتیری',
                    action: 'updated',
                    title: 'سرتیری اصلاح شوو',
                    message: 'نام سرتیری: ' . ($soldier->name ?? ''),
                    routeName: 'soldiers',              // ⚠️ set your real route name
                    routeParams: ['id' => $soldier->id],
                    level: 'info',
                    changes: !empty($changes) ? $changes : null
                );

                return new SoldierResource($soldier);
            });
        } catch (QueryException $e) {
            if ($this->isDuplicateKeyException($e)) {
                return response()->json($this->duplicateErrorPashto(), 422);
            }
            throw $e;
        }
    }

    public function destroy(Request $request, Soldier $soldier)
    {
        $user = $request->user(); // 🔔

        return DB::transaction(function () use ($soldier, $user) {
            $name = $soldier->name;

            // ✅ delete picture file if exists
            if ($soldier->picture) {
                Storage::disk('public')->delete($soldier->picture);
            }

            $soldier->delete();

            // 🔔 Notification: DELETED
            NotificationService::record(
                $user,
                module: 'سرتیری',
                action: 'deleted',
                title: 'سرتیری حذف شوو',
                message: 'نام سرتیری: ' . ($name ?? ''),
                routeName: null,
                routeParams: ['soldiers'],
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
        'specific_place',
        'street_number',
        'home_number',
        'Province_addr',
        'phone_number',
        'old_job',
        'now_job',
        'guard_amount',
        'notes',
        'resource_id',
        'coordinates',
        'resource_code',
        'resource_phone_number',
    ];
}

private function applyPrintFilters($query, Request $request)
{
    $search           = trim((string) $request->query('search', ''));
    $searchField      = trim((string) $request->query('search_field', ''));
    $searchValue      = trim((string) $request->query('search_value', ''));
    $resourceId       = $request->query('resource_id');
    $fiftySixFormatId = $request->query('fifty_six_format_id');

    $allowedFields = $this->printSearchFields();

    if ($search !== '' && $searchField === '') {
        $query->where(function ($x) use ($search) {
            $x->where('name', 'like', "%{$search}%")
                ->orWhere('father_name', 'like', "%{$search}%")
                ->orWhere('main_province', 'like', "%{$search}%")
                ->orWhere('main_district', 'like', "%{$search}%")
                ->orWhere('main_village', 'like', "%{$search}%")
                ->orWhere('now_province', 'like', "%{$search}%")
                ->orWhere('now_zone', 'like', "%{$search}%")
                ->orWhere('now_district', 'like', "%{$search}%")
                ->orWhere('now_village', 'like', "%{$search}%")
                ->orWhere('specific_place', 'like', "%{$search}%")
                ->orWhere('street_number', 'like', "%{$search}%")
                ->orWhere('home_number', 'like', "%{$search}%")
                ->orWhere('Province_addr', 'like', "%{$search}%")
                ->orWhere('phone_number', 'like', "%{$search}%")
                ->orWhere('old_job', 'like', "%{$search}%")
                ->orWhere('now_job', 'like', "%{$search}%")
                ->orWhere('guard_amount', 'like', "%{$search}%")
                ->orWhere('coordinates', 'like', "%{$search}%")
                ->orWhere('notes', 'like', "%{$search}%")
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
            } elseif ($searchField === 'resource_phone_number') {
                $query->whereHas('resource', function ($r) use ($value) {
                    $r->where('phone_number', 'like', "%{$value}%");
                });
            } else {
                $query->where($searchField, 'like', "%{$value}%");
            }
        }
    }

    if ($resourceId !== null && $resourceId !== '') {
        $query->where('resource_id', $resourceId);
    }

    if (is_numeric($fiftySixFormatId) && (int) $fiftySixFormatId > 0) {
        $query->where('id', (int) $fiftySixFormatId);
    }

    return $query;
}

public function printView(Request $request)
{
    $query = Soldier::query()
        ->with([
            'resource' => fn ($qr) => $qr->select('id', 'name', 'code_number', 'phone_number'),
        ]);

    $this->applyPrintFilters($query, $request);

    $rows = $query
        ->orderByDesc('id')
        ->get()
        ->map(function ($row) {
            $row->resource_code =
                $row->resource?->code_number
                ?? $row->resource_id
                ?? '-';

            $row->resource_phone_number =
                $row->resource?->phone_number
                ?? '-';

            $row->picture =
                $row->picture
                    ? asset('storage/' . ltrim($row->picture, '/'))
                    : null;

            return $row;
        });

    $selectedColumns = $request->input('selected_columns', []);
    $columnLabels    = $request->input('column_labels', []);
    $printLabels     = $request->input('print_labels', []);
    $locale          = $request->input('locale', 'fa');
    $title           = $request->input('title', 'چاپ سربازان');

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
    $soldier = Soldier::findOrFail($id);

    return $this->removePictureFileAndClear($soldier);
} 
}