<?php

namespace App\Http\Controllers\fifthyformatone;

use App\Http\Controllers\Controller;
use App\Http\Requests\fifthyformatone\StoreRobbersRequest;
use App\Http\Requests\fifthyformatone\UpdateRobbersRequest;
use App\Http\Resources\fifthyformatone\RobbersResource;
use App\Models\fifthyformatone\Robbers;
use App\Services\NotificationService; // 🔔 notifications
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage; // ✅ picture handling

class RobbersController extends Controller
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
                    'دا کس مخکې ثبت شوی دی (همدا نوم، د پلار نوم او اصلي پته).',
                ],
            ],
        ];
    }

    private function isDuplicateKeyException(QueryException $e): bool
    {
        // MySQL duplicate entry error code
        return (int) ($e->errorInfo[1] ?? 0) === 1062;
    }

    /**
     * Searchable columns for selected-column filter
     */
    private function allowedSearchFields(): array
    {
        $table = (new Robbers())->getTable();

        $candidates = [
           
        'information_id',

        'nickname',
        'name',
        'father_name',

        'main_province',
        'main_district',
        'main_village',

        'now_province',
        'picture',
        'now_zone',
        'now_district',
        'now_village',
        'now_specific_place',
        'now_street_number',
        'Province_addr',
        'now_home_number',

        'old_job',
        'now_job',
        'supplier',
        'phone_number',
        'freinds_quantity',
        'history_form',
        'coordinates',

        'note',
        'resource_id',
        ];

        return array_values(array_filter($candidates, fn ($field) => Schema::hasColumn($table, $field)));
    }

    public function index(Request $request)
    {
        $perPage = (int) $request->get('perPage', 15);
        $search  = trim((string) $request->get('search', ''));
        $rid     = $request->get('resource_id');

        $searchField = trim((string) $request->get('search_field', ''));
        $searchValue = trim((string) $request->get('search_value', ''));

        $sortBy  = $request->get('sort_by', 'created_at');
        $sortDir = in_array($request->get('sort_dir'), ['asc', 'desc'], true)
            ? $request->get('sort_dir')
            : 'desc';

        $allowedSearchFields = $this->allowedSearchFields();

        $query = Robbers::query()->with([
            'resource' => fn ($qr) => $qr->select('id', 'name', 'code_number', 'phone_number'),
        ]);

        if ($search !== '' && $searchField === '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('nickname', 'like', "%{$search}%")
                    ->orWhere('father_name', 'like', "%{$search}%")
                    ->orWhere('phone_number', 'like', "%{$search}%");
            });
        }

        if ($searchField !== '' && in_array($searchField, $allowedSearchFields, true)) {
            $value = $searchValue !== '' ? $searchValue : $search;

            if ($value !== '') {
                $query->where($searchField, 'like', "%{$value}%");
            }
        }

        if ($rid) {
            $query->where('resource_id', $rid);
        }

        $query->orderBy($sortBy, $sortDir);

        return RobbersResource::collection($query->paginate($perPage));
    }

    /**
     * GET /robbers/filter-options
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

        $query = Robbers::query()
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

    public function store(StoreRobbersRequest $request)
    {
        $user = $request->user(); // 🔔

        try {
            return DB::transaction(function () use ($request, $user) {
                $data = $request->validated();

                // ✅ picture: file OR string (same style)
                if ($request->hasFile('picture')) {
                    $path = $request->file('picture')->store('uploads/robbers', 'public');
                    $data['picture'] = "storage/{$path}";
                } else {
                    if (array_key_exists('picture', $data) && !is_string($data['picture'])) {
                        unset($data['picture']);
                    }
                }

                $robber = Robbers::create($data);

                $robber->load([
                    'resource' => fn ($qr) => $qr->select('id', 'name', 'code_number', 'phone_number'),
                ]);

                // 🔔 Notification: CREATED
                NotificationService::record(
                    $user,
                    module: 'غله',
                    action: 'created',
                    title: 'ریکارډ ثبت شوو',
                    message: 'نوم: ' . ($robber->name ?? ''),
                    routeName: 'robbers', // ⚠️ set your real route name
                    routeParams: ['id' => $robber->id],
                    level: 'success',
                    changes: null
                );

                return (new RobbersResource($robber))
                    ->additional(['message' => 'Created'])
                    ->response()
                    ->setStatusCode(201);
            });
        } catch (QueryException $e) {
            if ($this->isDuplicateKeyException($e)) {
                return response()->json($this->duplicateErrorPashto(), 422);
            }
            throw $e;
        }
    }

    /**
     * IMPORTANT:
     * This endpoint must ONLY be called with a VALID ID
     */
    public function show($id)
    {
        $robber = Robbers::with([
            'resource' => fn ($qr) => $qr->select('id', 'name', 'code_number', 'phone_number'),
        ])->findOrFail($id);

        return RobbersResource::make($robber);
    }

    public function update(UpdateRobbersRequest $request, $id)
    {
        $user = $request->user(); // 🔔

        try {
            return DB::transaction(function () use ($request, $id, $user) {
                $robber = Robbers::findOrFail($id);

                $validated = $request->validated();

                // 🧮 changes BEFORE update
                $original = $robber->getOriginal();
                $changes  = [];

                foreach ($validated as $field => $newValue) {
                    if (array_key_exists($field, $original) && $original[$field] != $newValue) {
                        $changes[$field] = [
                            'old' => $original[$field],
                            'new' => $newValue,
                        ];
                    }
                }

                // ✅ picture: replace file if uploaded
                if ($request->hasFile('picture')) {
                    if ($robber->picture && str_starts_with($robber->picture, 'storage/')) {
                        $old = str_replace('storage/', '', $robber->picture);
                        Storage::disk('public')->delete($old);
                    }

                    $path = $request->file('picture')->store('uploads/robbers', 'public');
                    $validated['picture'] = "storage/{$path}";

                    $changes['picture'] = [
                        'old' => $original['picture'] ?? null,
                        'new' => $validated['picture'],
                    ];
                } else {
                    if (array_key_exists('picture', $validated) && !is_string($validated['picture'])) {
                        unset($validated['picture']);
                    }
                }

                $robber->update($validated);

                $robber->load([
                    'resource' => fn ($qr) => $qr->select('id', 'name', 'code_number', 'phone_number'),
                ]);

                // 🔔 Notification: UPDATED
                NotificationService::record(
                    $user,
                    module: 'غله',
                    action: 'updated',
                    title: 'ریکارډ اصلاح شوو',
                    message: 'نوم: ' . ($robber->name ?? ''),
                    routeName: 'robbers', // ⚠️ set your real route name
                    routeParams: ['id' => $robber->id],
                    level: 'info',
                    changes: !empty($changes) ? $changes : null
                );

                return RobbersResource::make($robber);
            });
        } catch (QueryException $e) {
            if ($this->isDuplicateKeyException($e)) {
                return response()->json($this->duplicateErrorPashto(), 422);
            }
            throw $e;
        }
    }

    public function destroy(Request $request, $id)
    {
        $user = $request->user(); // 🔔

        return DB::transaction(function () use ($id, $user) {
            $robber = Robbers::findOrFail($id);
            $name   = $robber->name;

            // ✅ delete picture file
            if ($robber->picture && str_starts_with($robber->picture, 'storage/')) {
                $old = str_replace('storage/', '', $robber->picture);
                Storage::disk('public')->delete($old);
            }

            $robber->delete();

            // 🔔 Notification: DELETED
            NotificationService::record(
                $user,
                module: 'غله',
                action: 'deleted',
                title: 'ریکارډ حذف شوو',
                message: 'نوم: ' . ($name ?? ''),
                routeName: null,
                routeParams: ['robbers'],
                level: 'warning',
                changes: null
            );

            return response()->noContent();
        });
    }
    private function printSearchFields(): array
{
    return [
        'information_id',

        'nickname',
        'name',
        'father_name',

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
        'supplier',
        'phone_number',

        // ✅ keep both, because your frontend uses friends_quantity,
        // but your controller/db candidate has freinds_quantity
        'friends_quantity',
        'freinds_quantity',

        'history_form',
        'coordinates',
        'note',

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
    $table = (new Robbers())->getTable();

    if ($search !== '' && $searchField === '') {
        $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
                ->orWhere('nickname', 'like', "%{$search}%")
                ->orWhere('father_name', 'like', "%{$search}%")
                ->orWhere('phone_number', 'like', "%{$search}%")
                ->orWhere('main_province', 'like', "%{$search}%")
                ->orWhere('main_district', 'like', "%{$search}%")
                ->orWhere('main_village', 'like', "%{$search}%")
                ->orWhere('now_province', 'like', "%{$search}%")
                ->orWhere('now_zone', 'like', "%{$search}%")
                ->orWhere('now_district', 'like', "%{$search}%")
                ->orWhere('now_village', 'like', "%{$search}%")
                ->orWhere('now_specific_place', 'like', "%{$search}%")
                ->orWhere('now_street_number', 'like', "%{$search}%")
                ->orWhere('now_home_number', 'like', "%{$search}%")
                ->orWhere('old_job', 'like', "%{$search}%")
                ->orWhere('now_job', 'like', "%{$search}%")
                ->orWhere('supplier', 'like', "%{$search}%")
                ->orWhere('history_form', 'like', "%{$search}%")
                ->orWhere('coordinates', 'like', "%{$search}%")
                ->orWhere('note', 'like', "%{$search}%")
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
            } elseif ($searchField === 'friends_quantity' && Schema::hasColumn($table, 'freinds_quantity')) {
                // ✅ typo-safe fallback
                $query->where('freinds_quantity', 'like', "%{$value}%");
            } elseif (Schema::hasColumn($table, $searchField)) {
                $query->where($searchField, 'like', "%{$value}%");
            }
        }
    }

    if ($resourceId) {
        $query->where('resource_id', $resourceId);
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
    $query = Robbers::query()->with([
        'resource' => fn ($qr) => $qr->select('id', 'name', 'code_number', 'phone_number'),
    ]);

    $this->applyPrintFilters($query, $request);

    $rows = $query
        ->orderByDesc('id')
        ->get()
        ->map(function ($row) {
            // ✅ print display fields
            $row->resource_code =
                $row->resource?->code_number
                ?? $row->resource_id
                ?? '-';

            $row->resource_phone =
                $row->resource?->phone_number
                ?? '-';

            // ✅ because your header key is resource_id but label says کود منبع
            $row->resource_id =
                $row->resource?->code_number
                ?? $row->resource_id
                ?? '-';

            // ✅ typo-safe print value
            if (!isset($row->friends_quantity)) {
                $row->friends_quantity = $row->freinds_quantity ?? null;
            }

            return $row;
        });

    $selectedColumns = $request->input('selected_columns', []);
    $columnLabels    = $request->input('column_labels', []);
    $printLabels     = $request->input('print_labels', []);
    $locale          = $request->input('locale', 'fa');
    $title           = $request->input('title', 'چاپ رهزنان');

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
    $robber = Robbers::findOrFail($id);

    return $this->removePictureFileAndClear($robber);
}

public function exportExcel(Request $request)
{
    $search = trim((string) $request->get('search', ''));
    $searchField = $request->get('search_field');
    $searchValue = $request->get('search_value');
    $fileName = 'robbers_' . now()->format('Y_m_d_H_i_s') . '.xlsx';
    return Excel::download(new \App\Exports\RobbersExport(['search' => $search, 'search_field' => $searchField, 'search_value' => $searchValue, 'resource_id' => $request->get('resource_id')]), $fileName);
}
}
