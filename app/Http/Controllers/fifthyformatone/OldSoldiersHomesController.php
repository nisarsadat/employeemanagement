<?php

namespace App\Http\Controllers\fifthyformatone;

use App\Http\Controllers\Controller;
use App\Http\Requests\fifthyformatone\StoreOldSoldiersHomesRequest;
use App\Http\Requests\fifthyformatone\UpdateOldSoldiersHomesRequest;
use App\Http\Resources\fifthyformatone\OldSoldiersHomesResource;
use App\Models\fifthyformatone\OldSoldiersHomes;
use App\Services\NotificationService; // 🔔 notifications
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage; // ✅ ONLY for picture delete/store
use Illuminate\Support\Facades\Schema;
class OldSoldiersHomesController extends Controller
{
    /**
     * Pashto duplicate error message
     */
    private function duplicateErrorPashto(): array
    {
        return [
            'message' => 'دا کور مخکې ثبت شوی دی. مهرباني وکړئ تکراري معلومات مه داخلوی.',
            'errors'  => [
                'duplicate' => [
                    'دا موقعیت مخکې ثبت شوی دی (همدا ولایت، ناحیه، کوڅه او کور نمبر).',
                ],
            ],
        ];
    }

    private function isDuplicateKeyException(QueryException $e): bool
    {
        // MySQL duplicate entry error code
        return (int) ($e->errorInfo[1] ?? 0) === 1062;
    }

    /** GET /old-soldiers-homes */
    public function index(Request $request)
    {
        $page    = $request->query('page');
        $perPage = $request->query('perPage', $request->query('per_page', $request->query('itemsPerPage')));

        $isValidPage    = is_numeric($page) && (int) $page > 0;
        $isValidPerPage = is_numeric($perPage) && (int) $perPage > 0;

        $search      = trim((string) $request->query('search', ''));
        $searchField = trim((string) $request->query('search_field', ''));
        $searchValue = trim((string) $request->query('search_value', ''));

        $allowedSearchFields = [
           
        'province',
        'zone',
        'district',
        'village',
        'specific_place',
        'street_number',
        'home_number',
        'phone_number',
        'picture',
        'coordinates',
        'name',
        'father_name',
        'main_province',
        'old_job',
        'now_activity',
        'Province_addr',
        'note',
        'resource_id',
        ];

        $q = OldSoldiersHomes::query()
            ->with(['resource:id,name,code_number,phone_number'])
            ->latest()
            ->when($search !== '' && $searchField === '', function ($sub) use ($search) {
                $sub->where(function ($w) use ($search) {
                    $w->where('name', 'like', "%{$search}%")
                        ->orWhere('father_name', 'like', "%{$search}%")
                        ->orWhere('province', 'like', "%{$search}%")
                        ->orWhere('district', 'like', "%{$search}%")
                        ->orWhere('village', 'like', "%{$search}%")
                        ->orWhere('old_job', 'like', "%{$search}%")
                        ->orWhere('now_activity', 'like', "%{$search}%");
                });
            })
            ->when(
                $searchField !== '' && in_array($searchField, $allowedSearchFields, true),
                function ($sub) use ($searchField, $searchValue, $search) {
                    $value = $searchValue !== '' ? $searchValue : $search;

                    if ($value !== '') {
                        $sub->where($searchField, 'like', "%{$value}%");
                    }
                }
            );

        if ($isValidPage && $isValidPerPage) {
            return OldSoldiersHomesResource::collection(
                $q->paginate((int) $perPage, ['*'], 'page', (int) $page)
            );
        }

        return OldSoldiersHomesResource::collection($q->get());
    }

    /**
     * GET /old-soldiers-homes/filter-options
     */
    public function filterOptions(Request $request)
    {
        $field = trim((string) $request->query('field', ''));
        $q = trim((string) $request->query('q', ''));

        $allowedFields = [
           
        'province',
        'zone',
        'district',
        'village',
        'specific_place',
        'street_number',
        'home_number',
        'phone_number',
        'picture',
        'coordinates',
        'name',
        'father_name',
        'main_province',
        'old_job',
        'now_activity',
        'Province_addr',
        'note',
        'resource_id',
        ];

        if ($field === '' || !in_array($field, $allowedFields, true)) {
            return response()->json([
                'data' => [
                    'options' => [],
                ],
            ]);
        }

        $query = OldSoldiersHomes::query()
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

    /** POST /old-soldiers-homes */
    public function store(StoreOldSoldiersHomesRequest $request)
    {
        $user = $request->user(); // 🔔

        try {
            return DB::transaction(function () use ($request, $user) {
                $data = $request->validated();

                // ✅ picture: file OR string (same as SaeenFasad)
                if ($request->hasFile('picture')) {
                    $path = $request->file('picture')->store('uploads/old-soldiers-homes', 'public');
                    $data['picture'] = "storage/{$path}";
                } else {
                    if (array_key_exists('picture', $data) && !is_string($data['picture'])) {
                        unset($data['picture']);
                    }
                }

                $record = OldSoldiersHomes::create($data);

                $record->load(['resource:id,name,code_number,phone_number']);

                // 🔔 Notification: CREATED
                NotificationService::record(
                    $user,
                    module: 'پخواني چارواکو کورونه',
                    action: 'created',
                    title: 'کور ثبت شوو',
                    message: 'نام: ' . ($record->name ?? ''),
                    routeName: 'old-soldiers-homes',
                    routeParams: ['id' => $record->id],
                    level: 'success',
                    changes: null
                );

                return (new OldSoldiersHomesResource($record))
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

    /** GET /old-soldiers-homes/{oldSoldiersHome} */
    public function show(OldSoldiersHomes $oldSoldiersHome)
    {
        $oldSoldiersHome->load(['resource:id,name,code_number,phone_number']);
        return OldSoldiersHomesResource::make($oldSoldiersHome);
    }

    /** PUT /old-soldiers-homes/{oldSoldiersHome} */
    public function update(UpdateOldSoldiersHomesRequest $request, OldSoldiersHomes $oldSoldiersHome)
    {
        $user = $request->user(); // 🔔

        try {
            return DB::transaction(function () use ($request, $oldSoldiersHome, $user) {
                $validated = $request->validated();

                // 🧮 detect changes
                $original = $oldSoldiersHome->getOriginal();
                $changes  = [];

                foreach ($validated as $field => $newValue) {
                    if (array_key_exists($field, $original) && $original[$field] != $newValue) {
                        $changes[$field] = [
                            'old' => $original[$field],
                            'new' => $newValue,
                        ];
                    }
                }

                // ✅ picture: replace file if uploaded (same as SaeenFasad)
                if ($request->hasFile('picture')) {
                    if ($oldSoldiersHome->picture && str_starts_with($oldSoldiersHome->picture, 'storage/')) {
                        $old = str_replace('storage/', '', $oldSoldiersHome->picture);
                        Storage::disk('public')->delete($old);
                    }

                    $path = $request->file('picture')->store('uploads/old-soldiers-homes', 'public');
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

                $oldSoldiersHome->update($validated);
                $oldSoldiersHome->load(['resource:id,name,code_number,phone_number']);

                // 🔔 Notification: UPDATED
                NotificationService::record(
                    $user,
                    module: 'پخواني چارواکو کورونه',
                    action: 'updated',
                    title: 'کور اصلاح شوو',
                    message: 'نام: ' . ($oldSoldiersHome->name ?? ''),
                    routeName: 'old-soldiers-homes',
                    routeParams: ['id' => $oldSoldiersHome->id],
                    level: 'info',
                    changes: !empty($changes) ? $changes : null
                );

                return OldSoldiersHomesResource::make($oldSoldiersHome);
            });
        } catch (QueryException $e) {
            if ($this->isDuplicateKeyException($e)) {
                return response()->json($this->duplicateErrorPashto(), 422);
            }
            throw $e;
        }
    }

    /** DELETE /old-soldiers-homes/{oldSoldiersHome} */
    public function destroy(Request $request, OldSoldiersHomes $oldSoldiersHome)
    {
        $user = $request->user(); // 🔔
        $name = $oldSoldiersHome->name;

        // ✅ delete picture file (same as SaeenFasad)
        if ($oldSoldiersHome->picture && str_starts_with($oldSoldiersHome->picture, 'storage/')) {
            $old = str_replace('storage/', '', $oldSoldiersHome->picture);
            Storage::disk('public')->delete($old);
        }

        $oldSoldiersHome->delete();

        // 🔔 Notification: DELETED
        NotificationService::record(
            $user,
            module: 'پخواني چارواکو کورونه',
            action: 'deleted',
            title: 'کور حذف شوو',
            message: 'نام: ' . ($name ?? ''),
            routeName: null,
            routeParams: ['old-soldiers-homes'],
            level: 'warning',
            changes: null
        );

        return response()->noContent();
    }

    /** POST /old-soldiers-homes/bulk-delete */
    public function bulkDelete(Request $request)
    {
        $user = $request->user(); // 🔔
        $ids  = is_array($request->input('ids')) ? $request->input('ids') : [];

        if (empty($ids)) {
            return response()->noContent();
        }

        OldSoldiersHomes::whereIn('id', $ids)->delete();

        // 🔔 Notification: BULK DELETE
        NotificationService::record(
            $user,
            module: 'پخواني چارواکو کورونه',
            action: 'deleted',
            title: 'خانه‌ها به‌صورت گروهی حذف شوو',
            message: 'تعداد: ' . count($ids),
            routeName: null,
            routeParams: ['old-soldiers-homes'],
            level: 'warning',
            changes: null
        );

        return response()->noContent();
    }
    private function printSearchFields(): array
{
    return [
        'province',
        'zone',
        'district',
        'village',
        'specific_place',
        'street_number',
        'home_number',
        'phone_number',
        'picture',
        'coordinates',
        'name',
        'father_name',
        'main_province',
        'old_job',
        'now_activity',
        'Province_addr',
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
    $fiftySixFormatId = $request->query('fifty_six_format_id');
    $cardFilter       = trim((string) $request->query('card_filter', 'all'));

    $allowedFields = $this->printSearchFields();

    if ($search !== '' && $searchField === '') {
        $query->where(function ($w) use ($search) {
            $w->where('name', 'like', "%{$search}%")
                ->orWhere('father_name', 'like', "%{$search}%")
                ->orWhere('province', 'like', "%{$search}%")
                ->orWhere('zone', 'like', "%{$search}%")
                ->orWhere('district', 'like', "%{$search}%")
                ->orWhere('village', 'like', "%{$search}%")
                ->orWhere('specific_place', 'like', "%{$search}%")
                ->orWhere('street_number', 'like', "%{$search}%")
                ->orWhere('home_number', 'like', "%{$search}%")
                ->orWhere('phone_number', 'like', "%{$search}%")
                ->orWhere('coordinates', 'like', "%{$search}%")
                ->orWhere('main_province', 'like', "%{$search}%")
                ->orWhere('old_job', 'like', "%{$search}%")
                ->orWhere('now_activity', 'like', "%{$search}%")
                ->orWhere('Province_addr', 'like', "%{$search}%")
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
            } else {
                $query->where($searchField, 'like', "%{$value}%");
            }
        }
    }

    if (
        is_numeric($fiftySixFormatId)
        && (int) $fiftySixFormatId > 0
        && Schema::hasColumn((new OldSoldiersHomes())->getTable(), 'fifty_six_format_id')
    ) {
        $query->where('fifty_six_format_id', (int) $fiftySixFormatId);
    }

    if ($cardFilter === 'withCoordinates') {
        $query->whereNotNull('coordinates')
            ->whereRaw("TRIM(coordinates) != ''");
    } elseif ($cardFilter === 'withPhone') {
        $query->whereNotNull('phone_number')
            ->whereRaw("TRIM(phone_number) != ''");
    } elseif ($cardFilter === 'withResource') {
        $query->whereNotNull('resource_id');
    }

    return $query;
}

public function printView(Request $request)
{
    $query = OldSoldiersHomes::query()
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
                ?? $row->resource_phone
                ?? '-';

            return $row;
        });

    $selectedColumns = $request->input('selected_columns', []);
    $columnLabels    = $request->input('column_labels', []);
    $printLabels     = $request->input('print_labels', []);
    $locale          = $request->input('locale', 'fa');
    $title           = $request->input('title', 'چاپ منازل سربازان سابق');

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
    $oldSoldiersHome = OldSoldiersHomes::findOrFail($id);

    return $this->removePictureFileAndClear($oldSoldiersHome);
}  
}