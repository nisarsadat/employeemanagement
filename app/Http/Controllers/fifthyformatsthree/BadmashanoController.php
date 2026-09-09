<?php

namespace App\Http\Controllers\fifthyformatsthree;

use App\Http\Controllers\Controller;
use App\Models\fifthyformatsthree\Badmashano;
use App\Http\Resources\fifthyformatsthree\BadmashanoResource;
use App\Http\Requests\fifthyformatsthree\StoreBadmashanoRequest;
use App\Http\Requests\fifthyformatsthree\UpdateBadmashanoRequest;
use App\Services\NotificationService; // 🔔 notifications
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use App\Exports\BadmashanoExport;
use Maatwebsite\Excel\Facades\Excel;

class BadmashanoController extends Controller
{
    private function isDuplicateKey(QueryException $e): bool
    {
        // MySQL duplicate key
        return (int) ($e->errorInfo[1] ?? 0) === 1062;
    }

    private function duplicateResponse()
    {
        return response()->json([
            'message' => 'دا ریکارډ مخکې ثبت شوی دی.',
            'errors'  => [
                'duplicate' => [
                    'د همدې معلوماتو سره (لقب/نوم/د پلار نوم او اصلي ادرس) ریکارډ مخکې له مخکې موجود دی.',
                ],
            ],
        ], 422);
    }

    private function allowedSearchFields(): array
    {
        $table = (new Badmashano())->getTable();

        $candidates = [
          
         'information_id',

        'picture',
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
        'Province_addr',
        'now_specific_place',
        'now_street_number',
        'now_home_number',

        'old_job',
        'job',
        'friends_quantity',
        'supplier',
        'phone_number',

        'history_jorm',
        'jorm_type',
        'note',
        'coordinates',

        'resource_id',
        ];

        return array_values(array_filter($candidates, fn ($field) => Schema::hasColumn($table, $field)));
    }

    public function index(Request $request)
    {
        $perPage = (int) $request->input('perPage', 10);
        $search  = trim((string) $request->input('search', ''));
        $searchField = trim((string) $request->input('search_field', ''));
        $searchValue = trim((string) $request->input('search_value', ''));

        $allowedSearchFields = $this->allowedSearchFields();

        $q = Badmashano::query()
            ->with(['resource:id,name,code_number,phone_number'])
            ->latest();

        if ($search !== '' && $searchField === '') {
            $q->where(function ($qq) use ($search) {
                $qq->where('name', 'like', "%{$search}%")
                   ->orWhere('nickname', 'like', "%{$search}%")
                   ->orWhere('father_name', 'like', "%{$search}%");
            });
        }

        if ($searchField !== '' && in_array($searchField, $allowedSearchFields, true)) {
            $value = $searchValue !== '' ? $searchValue : $search;

            if ($value !== '') {
                $q->where($searchField, 'like', "%{$value}%");
            }
        }

        return BadmashanoResource::collection($q->paginate($perPage));
    }

    /**
     * GET /badmashanos/filter-options
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

        $query = Badmashano::query()
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

    public function store(StoreBadmashanoRequest $request)
    {
        $user = $request->user(); // 🔔 who did this
        $data = $request->validated();

        try {
            // ✅ picture: file OR string (same style as SaeenFasad)
            if ($request->hasFile('picture')) {
                $path = $request->file('picture')->store('uploads/badmashanos', 'public');
                $data['picture'] = "storage/{$path}";
            } else {
                if (array_key_exists('picture', $data) && !is_string($data['picture'])) {
                    unset($data['picture']);
                }
            }

            $item = Badmashano::create($data)
                ->load(['resource:id,name,code_number,phone_number']);

            // 🔔 Notification: CREATED
            NotificationService::record(
                $user,
                module: 'بدماشانو',
                action: 'created',
                title: 'نوی بدماش ثبت شو',
                message: 'نوم: ' . ($item->name ?? ''),
                routeName: 'badmashanos',          // ⚠️ set to your real route name
                routeParams: ['id' => $item->id],
                level: 'success',
                changes: null
            );

            return BadmashanoResource::make($item);
        } catch (QueryException $e) {
            if ($this->isDuplicateKey($e)) {
                return $this->duplicateResponse();
            }
            throw $e;
        }
    }

    public function show(Badmashano $badmashano)
    {
        $badmashano->load(['resource:id,name,code_number,phone_number']);
        return BadmashanoResource::make($badmashano);
    }

    public function update(UpdateBadmashanoRequest $request, Badmashano $badmashano)
    {
        $user = $request->user(); // 🔔
        $data = $request->validated();

        try {
            // 🧮 build changes BEFORE update
            $original = $badmashano->getOriginal();
            $changes  = [];

            foreach ($data as $field => $newValue) {
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

            // ✅ picture: replace file if uploaded (same style as SaeenFasad)
            if ($request->hasFile('picture')) {
                if ($badmashano->picture && str_starts_with($badmashano->picture, 'storage/')) {
                    $old = str_replace('storage/', '', $badmashano->picture);
                    Storage::disk('public')->delete($old);
                }

                $path = $request->file('picture')->store('uploads/badmashanos', 'public');
                $data['picture'] = "storage/{$path}";

                $changes['picture'] = [
                    'old' => $original['picture'] ?? null,
                    'new' => $data['picture'],
                ];
            } else {
                if (array_key_exists('picture', $data) && !is_string($data['picture'])) {
                    unset($data['picture']);
                }
            }

            $badmashano->update($data);
            $badmashano->load(['resource:id,name,code_number,phone_number']);

            // 🔔 Notification: UPDATED
            NotificationService::record(
                $user,
                module: 'بدماشانو',
                action: 'updated',
                title: 'بدماش اصلاح شو',
                message: 'نوم: ' . ($badmashano->name ?? ''),
                routeName: 'badmashanos',          // ⚠️ set to your real route name
                routeParams: ['id' => $badmashano->id],
                level: 'info',
                changes: !empty($changes) ? $changes : null
            );

            return BadmashanoResource::make($badmashano);
        } catch (QueryException $e) {
            if ($this->isDuplicateKey($e)) {
                return $this->duplicateResponse();
            }
            throw $e;
        }
    }

    public function destroy(Request $request, Badmashano $badmashano)
    {
        $user = $request->user(); // 🔔
        $name = $badmashano->name;

        // ✅ delete picture file (same style as SaeenFasad)
        if ($badmashano->picture && str_starts_with($badmashano->picture, 'storage/')) {
            $old = str_replace('storage/', '', $badmashano->picture);
            Storage::disk('public')->delete($old);
        }

        $badmashano->delete();

        // 🔔 Notification: DELETED
        NotificationService::record(
            $user,
            module: 'بدماشانو',
            action: 'deleted',
            title: 'بدماش حذف شو',
            message: 'نوم: ' . ($name ?? ''),
            routeName: null,
            routeParams: ['badmashanos'],
            level: 'warning',
            changes: null
        );

        return response()->noContent();
    }

    public function bulkDelete(Request $request)
    {
        $user = $request->user(); // 🔔
        $ids = (array) $request->input('ids', []);

        // (kept your original logic — if you want file cleanup here too, tell me)
        Badmashano::destroy($ids);

        // 🔔 Notification: BULK DELETED
        NotificationService::record(
            $user,
            module: 'بدماشانو',
            action: 'bulk_deleted',
            title: 'ډله‌ییز حذف ترسره شو',
            message: 'حذف شوي ریکارډونه: ' . count($ids),
            routeName: null,
            routeParams: ['badmashanos'],
            level: 'warning',
            changes: null
        );

        return response()->noContent();
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
    $badmashano = Badmashano::findOrFail($id);

    return $this->removePictureFileAndClear($badmashano);
}

public function exportExcel(Request $request)
{
    $search = trim((string) $request->get('search', ''));
    $searchField = $request->get('search_field');
    $searchValue = $request->get('search_value');
    $cardFilter = trim((string) $request->get('card_filter', 'all'));

    $fileName = 'badmashanos_' . now()->format('Y_m_d_H_i_s') . '.xlsx';

    return Excel::download(
        new BadmashanoExport([
            'search'       => $search,
            'search_field' => $searchField,
            'search_value' => $searchValue,
            'card_filter'  => $cardFilter,
            'resource_id'         => $request->get('resource_id'),
            'fifty_six_format_id' => $request->get('fifty_six_format_id'),
        ]),
        $fileName
    );
}
private function printSearchFields(): array
{
    return $this->allowedSearchFields();
}

private function applyPrintFilters($query, Request $request)
{
    $search           = trim((string) $request->query('search', ''));
    $searchField      = trim((string) $request->query('search_field', ''));
    $searchValue      = trim((string) $request->query('search_value', ''));
    $cardFilter       = trim((string) $request->query('card_filter', 'all'));
    $fiftySixFormatId = $request->query('fifty_six_format_id');
    $resourceId       = $request->query('resource_id');

    $allowedFields = $this->printSearchFields();

    if ($search !== '' && $searchField === '') {
        $query->where(function ($qq) use ($search) {
            $qq->where('name', 'like', "%{$search}%")
                ->orWhere('nickname', 'like', "%{$search}%")
                ->orWhere('father_name', 'like', "%{$search}%")
                ->orWhere('main_province', 'like', "%{$search}%")
                ->orWhere('main_district', 'like', "%{$search}%")
                ->orWhere('main_village', 'like', "%{$search}%")
                ->orWhere('now_province', 'like', "%{$search}%")
                ->orWhere('now_zone', 'like', "%{$search}%")
                ->orWhere('now_district', 'like', "%{$search}%")
                ->orWhere('now_village', 'like', "%{$search}%")
                ->orWhere('Province_addr', 'like', "%{$search}%")
                ->orWhere('now_specific_place', 'like', "%{$search}%")
                ->orWhere('now_street_number', 'like', "%{$search}%")
                ->orWhere('now_home_number', 'like', "%{$search}%")
                ->orWhere('old_job', 'like', "%{$search}%")
                ->orWhere('job', 'like', "%{$search}%")
                ->orWhere('friends_quantity', 'like', "%{$search}%")
                ->orWhere('supplier', 'like', "%{$search}%")
                ->orWhere('phone_number', 'like', "%{$search}%")
                ->orWhere('history_jorm', 'like', "%{$search}%")
                ->orWhere('jorm_type', 'like', "%{$search}%")
                ->orWhere('note', 'like', "%{$search}%")
                ->orWhere('coordinates', 'like', "%{$search}%")
                ->orWhereHas('resource', function ($r) use ($search) {
                    $r->where('code_number', 'like', "%{$search}%")
                        ->orWhere('phone_number', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%");
                });
        });
    }

    if ($searchField !== '') {
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
            } elseif (in_array($searchField, $allowedFields, true)) {
                $query->where($searchField, 'like', "%{$value}%");
            }
        }
    }

    if (is_numeric($fiftySixFormatId) && (int) $fiftySixFormatId > 0) {
        $query->where('id', (int) $fiftySixFormatId);
    }

    if (is_numeric($resourceId) && (int) $resourceId > 0) {
        $query->where('resource_id', (int) $resourceId);
    }

    if ($cardFilter === 'withCoordinates') {
        $query->whereNotNull('coordinates')
            ->whereRaw("TRIM(coordinates) != ''");
    } elseif ($cardFilter === 'withPhone') {
        $query->whereNotNull('phone_number')
            ->whereRaw("TRIM(phone_number) != ''");
    } elseif ($cardFilter === 'withJob') {
        $query->whereNotNull('job')
            ->whereRaw("TRIM(job) != ''");
    }

    return $query;
}

public function printView(Request $request)
{
    $query = Badmashano::query()
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

            return $row;
        });

    $selectedColumns = $request->input('selected_columns', []);
    $columnLabels    = $request->input('column_labels', []);
    $printLabels     = $request->input('print_labels', []);
    $locale          = $request->input('locale', 'fa');
    $title           = $request->input('title', 'چاپ بدماشانو');

    return response()->view('prints.table-print', [
        'title'           => $title,
        'rows'            => $rows,
        'selectedColumns' => $selectedColumns,
        'columnLabels'    => $columnLabels,
        'printLabels'     => $printLabels,
        'locale'          => $locale,
    ]);
}
}