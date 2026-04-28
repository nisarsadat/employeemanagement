<?php

namespace App\Http\Controllers\fifthyformatstow;

use App\Http\Controllers\Controller;
use App\Http\Requests\fifthyformatstow\StoreRahnomaMamlatRequest;
use App\Http\Requests\fifthyformatstow\UpdateRahnomaMamlatRequest;
use App\Http\Resources\fifthyformatstow\RahnomaMamlatResource;
use App\Models\fifthyformatstow\RahnomaMamlat;
use App\Services\NotificationService; // 🔔 notifications
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class RahnomaMamlatController extends Controller
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
                    'د همدې معلوماتو سره (نوم، د مسئول معلومات، او ادرس) ریکارډ مخکې له مخکې موجود دی.',
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

        'responsible_name',
        'responsible_f_name',
        'responsible_position',
        'responsible_phone',

        'province',
        'zone',
        'Province_addr',
        'district',
        'picture',
        'village',
        'specific_place',
        'coordinates',

        'note',
        'resource_id',
        ];

        $q = RahnomaMamlat::query()
            ->with(['resource:id,name,code_number,phone_number'])
            ->latest();

        if ($search !== '' && $searchField === '') {
            $q->where(function ($qq) use ($search) {
                $qq->where('name', 'like', "%{$search}%")
                   ->orWhere('responsible_name', 'like', "%{$search}%")
                   ->orWhere('responsible_phone', 'like', "%{$search}%")
                   ->orWhere('province', 'like', "%{$search}%")
                   ->orWhere('district', 'like', "%{$search}%");
            });
        }

        if ($searchField !== '' && in_array($searchField, $allowedSearchFields, true)) {
            $value = $searchValue !== '' ? $searchValue : $search;

            if ($value !== '') {
                $q->where($searchField, 'like', "%{$value}%");
            }
        }

        return RahnomaMamlatResource::collection($q->paginate($perPage));
    }

    public function filterOptions(Request $request)
    {
        $field = trim((string) $request->query('field', ''));
        $q = trim((string) $request->query('q', ''));

        $allowedFields = [
           
         'name',

        'responsible_name',
        'responsible_f_name',
        'responsible_position',
        'responsible_phone',

        'province',
        'zone',
        'Province_addr',
        'district',
        'picture',
        'village',
        'specific_place',
        'coordinates',

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

        $query = RahnomaMamlat::query()
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

    public function store(StoreRahnomaMamlatRequest $request)
    {
        $user = $request->user(); // 🔔 who did this
        $data = $request->validated();

        try {
            // ✅ picture: file OR string (same style as SaeenFasad)
            if ($request->hasFile('picture')) {
                $path = $request->file('picture')->store('uploads/rahnoma-mamlats', 'public');
                $data['picture'] = "storage/{$path}";
            } else {
                if (array_key_exists('picture', $data) && !is_string($data['picture'])) {
                    unset($data['picture']);
                }
            }

            $rahnoma = RahnomaMamlat::create($data)
                ->load(['resource:id,name,code_number,phone_number']);

            // 🔔 Notification: CREATED
            NotificationService::record(
                $user,
                module: 'رهنما مملات',
                action: 'created',
                title: 'نوی رهنما مملات ثبت شو',
                message: 'نوم: ' . ($rahnoma->name ?? ''),
                routeName: 'rahnoma-mamlats',       // ⚠️ set to your real route name
                routeParams: ['id' => $rahnoma->id],
                level: 'success',
                changes: null
            );

            return RahnomaMamlatResource::make($rahnoma);
        } catch (QueryException $e) {
            if ($this->isDuplicateKey($e)) {
                return $this->duplicateResponse();
            }
            throw $e;
        }
    }

    public function show(RahnomaMamlat $rahnomaMamlat)
    {
        $rahnomaMamlat->load(['resource:id,name,code_number,phone_number']);
        return RahnomaMamlatResource::make($rahnomaMamlat);
    }

    public function update(UpdateRahnomaMamlatRequest $request, RahnomaMamlat $rahnomaMamlat)
    {
        $user = $request->user(); // 🔔
        $data = $request->validated();

        try {
            // 🧮 build changes BEFORE update
            $original = $rahnomaMamlat->getOriginal();
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
                if ($rahnomaMamlat->picture && str_starts_with($rahnomaMamlat->picture, 'storage/')) {
                    $old = str_replace('storage/', '', $rahnomaMamlat->picture);
                    Storage::disk('public')->delete($old);
                }

                $path = $request->file('picture')->store('uploads/rahnoma-mamlats', 'public');
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

            $rahnomaMamlat->update($data);
            $rahnomaMamlat->load(['resource:id,name,code_number,phone_number']);

            // 🔔 Notification: UPDATED
            NotificationService::record(
                $user,
                module: 'رهنما مملات',
                action: 'updated',
                title: 'رهنما مملات اصلاح شو',
                message: 'نوم: ' . ($rahnomaMamlat->name ?? ''),
                routeName: 'rahnoma-mamlats',       // ⚠️ set to your real route name
                routeParams: ['id' => $rahnomaMamlat->id],
                level: 'info',
                changes: !empty($changes) ? $changes : null
            );

            return RahnomaMamlatResource::make($rahnomaMamlat);
        } catch (QueryException $e) {
            if ($this->isDuplicateKey($e)) {
                return $this->duplicateResponse();
            }
            throw $e;
        }
    }

    public function destroy(Request $request, RahnomaMamlat $rahnomaMamlat)
    {
        $user = $request->user(); // 🔔
        $name = $rahnomaMamlat->name;

        // ✅ delete picture file (same style as SaeenFasad)
        if ($rahnomaMamlat->picture && str_starts_with($rahnomaMamlat->picture, 'storage/')) {
            $old = str_replace('storage/', '', $rahnomaMamlat->picture);
            Storage::disk('public')->delete($old);
        }

        $rahnomaMamlat->delete();

        // 🔔 Notification: DELETED
        NotificationService::record(
            $user,
            module: 'رهنما مملات',
            action: 'deleted',
            title: 'رهنما مملات حذف شو',
            message: 'نوم: ' . ($name ?? ''),
            routeName: null,
            routeParams: ['rahnoma-mamlats'],
            level: 'warning',
            changes: null
        );

        return response()->noContent();
    }

    public function bulkDelete(Request $request)
    {
        $user = $request->user(); // 🔔
        $ids = $request->input('ids', []);

        // (kept your original logic — if you want file cleanup here too, tell me)
        RahnomaMamlat::destroy($ids);

        // 🔔 Notification: BULK DELETED
        NotificationService::record(
            $user,
            module: 'رهنما مملات',
            action: 'bulk_deleted',
            title: 'ډله‌ییز حذف ترسره شو',
            message: 'حذف شوي ریکارډونه: ' . count($ids),
            routeName: null,
            routeParams: ['rahnoma-mamlats'],
            level: 'warning',
            changes: null
        );

        return response()->noContent();
    }
    private function printSearchFields(): array
{
    return [
        'name',

        'responsible_name',
        'responsible_f_name',
        'responsible_position',
        'responsible_phone',

        'province',
        'zone',
        'Province_addr',
        'district',
        'picture',
        'village',
        'specific_place',
        'coordinates',

        'note',
        'resource_id',
        'resource_code',
        'resource_phone',
    ];
}

private function applyPrintFilters($query, Request $request)
{
    $search      = trim((string) $request->query('search', ''));
    $searchField = trim((string) $request->query('search_field', ''));
    $searchValue = trim((string) $request->query('search_value', ''));
    $resourceId  = $request->query('resource_id');
    $cardFilter  = trim((string) $request->query('card_filter', 'all'));

    $allowedFields = $this->printSearchFields();

    if ($search !== '' && $searchField === '') {
        $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
                ->orWhere('responsible_name', 'like', "%{$search}%")
                ->orWhere('responsible_f_name', 'like', "%{$search}%")
                ->orWhere('responsible_position', 'like', "%{$search}%")
                ->orWhere('responsible_phone', 'like', "%{$search}%")
                ->orWhere('province', 'like', "%{$search}%")
                ->orWhere('zone', 'like', "%{$search}%")
                ->orWhere('district', 'like', "%{$search}%")
                ->orWhere('village', 'like', "%{$search}%")
                ->orWhere('specific_place', 'like', "%{$search}%")
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
            } elseif ($searchField === 'resource_id') {
                $query->where('resource_id', $value);
            } else {
                $query->where($searchField, 'like', "%{$value}%");
            }
        }
    }

    if ($resourceId) {
        $query->where('resource_id', $resourceId);
    }

    if ($cardFilter === 'withCoordinates') {
        $query->whereNotNull('coordinates')
            ->whereRaw("TRIM(coordinates) != ''");
    } elseif ($cardFilter === 'withPhone') {
        $query->whereNotNull('responsible_phone')
            ->whereRaw("TRIM(responsible_phone) != ''");
    } elseif ($cardFilter === 'withResource') {
        $query->whereNotNull('resource_id')
            ->whereRaw("TRIM(resource_id) != ''");
    }

    return $query;
}

public function printView(Request $request)
{
    $query = RahnomaMamlat::query()
        ->with([
            'resource:id,name,code_number,phone_number',
        ]);

    $this->applyPrintFilters($query, $request);

    $rows = $query
        ->orderByDesc('id')
        ->get()
        ->map(function ($row) {
            $resourceCode = $row->resource?->code_number;
            $resourcePhone = $row->resource?->phone_number;

            $row->resource_code = $resourceCode ?? '-';
            $row->resource_phone = $resourcePhone ?? '-';

            // Because your header key is resource_id but title says "کود منبع"
            $row->resource_id = $resourceCode ?? $row->resource_id ?? '-';

            return $row;
        });

    $selectedColumns = $request->input('selected_columns', []);
    $columnLabels    = $request->input('column_labels', []);
    $printLabels     = $request->input('print_labels', []);
    $locale          = $request->input('locale', 'fa');
    $title           = $request->input('title', 'چاپ رهنما مملات');

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
    $rahnomaMamlat = RahnomaMamlat::findOrFail($id);

    return $this->removePictureFileAndClear($rahnomaMamlat);
} 
}