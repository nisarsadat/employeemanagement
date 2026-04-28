<?php

namespace App\Http\Controllers\fifthyformatsthree;

use App\Http\Controllers\Controller;
use App\Models\fifthyformatsthree\Sarayano;
use App\Http\Resources\fifthyformatsthree\SarayanoResource;
use App\Http\Requests\fifthyformatsthree\StoreSarayanoRequest;
use App\Http\Requests\fifthyformatsthree\UpdateSarayanoRequest;
use App\Services\NotificationService; // 🔔 notifications
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SarayanoController extends Controller
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
                    'د همدې معلوماتو سره (سراي نوم، ډول، او ادرس) ریکارډ مخکې له مخکې موجود دی.',
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
            
         'saray_name',
        'saray_type',

        'responsible_name',
        'responsible_f_name',
        'responsible_position',
        'responsible_phone',

        'province',
        'zone',
        'district',
        'village',
        'Province_addr',
        'specific_place',
        'cordenates',

        'employee_quantity',
        'note',

        'resource_id',
        ];

        $q = Sarayano::query()
            ->with(['resource:id,name,code_number,phone_number'])
            ->latest();

        if ($search !== '' && $searchField === '') {
            $q->where(function ($qq) use ($search) {
                $qq->where('saray_name', 'like', "%{$search}%")
                   ->orWhere('responsible_name', 'like', "%{$search}%")
                   ->orWhere('province', 'like', "%{$search}%");
            });
        }

        if ($searchField !== '' && in_array($searchField, $allowedSearchFields, true)) {
            $value = $searchValue !== '' ? $searchValue : $search;

            if ($value !== '') {
                $q->where($searchField, 'like', "%{$value}%");
            }
        }

        return SarayanoResource::collection($q->paginate($perPage));
    }

    public function filterOptions(Request $request)
    {
        $field = trim((string) $request->query('field', ''));
        $q = trim((string) $request->query('q', ''));

        $allowedFields = [
            
         'saray_name',
        'saray_type',

        'responsible_name',
        'responsible_f_name',
        'responsible_position',
        'responsible_phone',

        'province',
        'zone',
        'district',
        'village',
        'Province_addr',
        'specific_place',
        'cordenates',

        'employee_quantity',
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

        $query = Sarayano::query()
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

    public function store(StoreSarayanoRequest $request)
    {
        $user = $request->user(); // 🔔 who did this
        $data = $request->validated();

        try {
            // ✅ picture: file OR string (same style as SaeenFasad)
            if ($request->hasFile('picture')) {
                $path = $request->file('picture')->store('uploads/sarayano', 'public');
                $data['picture'] = "storage/{$path}";
            } else {
                if (array_key_exists('picture', $data) && !is_string($data['picture'])) {
                    unset($data['picture']);
                }
            }

            $saray = Sarayano::create($data)
                ->load(['resource:id,name,code_number,phone_number']);

            // 🔔 Notification: CREATED
            NotificationService::record(
                $user,
                module: 'سرايان',
                action: 'created',
                title: 'نوی سراي ثبت شو',
                message: 'نوم: ' . ($saray->saray_name ?? ''),
                routeName: 'sarayano',               // ⚠️ set to your real route name
                routeParams: ['id' => $saray->id],
                level: 'success',
                changes: null
            );

            return SarayanoResource::make($saray);
        } catch (QueryException $e) {
            if ($this->isDuplicateKey($e)) {
                return $this->duplicateResponse();
            }
            throw $e;
        }
    }

    public function show(Sarayano $sarayano)
    {
        $sarayano->load(['resource:id,name,code_number,phone_number']);
        return SarayanoResource::make($sarayano);
    }

    public function update(UpdateSarayanoRequest $request, Sarayano $sarayano)
    {
        $user = $request->user(); // 🔔
        $data = $request->validated();

        try {
            // 🧮 build changes BEFORE update
            $original = $sarayano->getOriginal();
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
                if ($sarayano->picture && str_starts_with($sarayano->picture, 'storage/')) {
                    $old = str_replace('storage/', '', $sarayano->picture);
                    Storage::disk('public')->delete($old);
                }

                $path = $request->file('picture')->store('uploads/sarayano', 'public');
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

            $sarayano->update($data);
            $sarayano->load(['resource:id,name,code_number,phone_number']);

            // 🔔 Notification: UPDATED
            NotificationService::record(
                $user,
                module: 'سرايان',
                action: 'updated',
                title: 'سراي اصلاح شو',
                message: 'نوم: ' . ($sarayano->saray_name ?? ''),
                routeName: 'sarayano',               // ⚠️ set to your real route name
                routeParams: ['id' => $sarayano->id],
                level: 'info',
                changes: !empty($changes) ? $changes : null
            );

            return SarayanoResource::make($sarayano);
        } catch (QueryException $e) {
            if ($this->isDuplicateKey($e)) {
                return $this->duplicateResponse();
            }
            throw $e;
        }
    }

    public function destroy(Request $request, Sarayano $sarayano)
    {
        $user = $request->user(); // 🔔
        $name = $sarayano->saray_name;

        // ✅ delete picture file (same style as SaeenFasad)
        if ($sarayano->picture && str_starts_with($sarayano->picture, 'storage/')) {
            $old = str_replace('storage/', '', $sarayano->picture);
            Storage::disk('public')->delete($old);
        }

        $sarayano->delete();

        // 🔔 Notification: DELETED
        NotificationService::record(
            $user,
            module: 'سرايان',
            action: 'deleted',
            title: 'سراي حذف شو',
            message: 'نوم: ' . ($name ?? ''),
            routeName: null,
            routeParams: ['sarayano'],
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
        Sarayano::destroy($ids);

        // 🔔 Notification: BULK DELETED
        NotificationService::record(
            $user,
            module: 'سرايان',
            action: 'bulk_deleted',
            title: 'ډله‌ییز حذف ترسره شو',
            message: 'حذف شوي ریکارډونه: ' . count($ids),
            routeName: null,
            routeParams: null,
            level: 'warning',
            changes: [
                'ids' => [
                    'old' => null,
                    'new' => $ids,
                ],
            ]
        );

        return response()->json([
            'message' => 'Bulk deleted',
            'deleted' => count($ids),
            'ids'     => $ids,
        ]);
    }
    private function printSearchFields(): array
{
    return [
        'saray_name',
        'saray_type',

        'responsible_name',
        'responsible_f_name',
        'responsible_position',
        'responsible_phone',

        'province',
        'zone',
        'district',
        'village',
        'Province_addr',
        'specific_place',
        'cordenates',

        'employee_quantity',
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
    $cardFilter  = trim((string) $request->query('card_filter', 'all'));

    $allowedFields = $this->printSearchFields();

    if ($search !== '' && $searchField === '') {
        $query->where(function ($qq) use ($search) {
            $qq->where('saray_name', 'like', "%{$search}%")
                ->orWhere('saray_type', 'like', "%{$search}%")
                ->orWhere('responsible_name', 'like', "%{$search}%")
                ->orWhere('responsible_f_name', 'like', "%{$search}%")
                ->orWhere('responsible_position', 'like', "%{$search}%")
                ->orWhere('responsible_phone', 'like', "%{$search}%")
                ->orWhere('province', 'like', "%{$search}%")
                ->orWhere('zone', 'like', "%{$search}%")
                ->orWhere('district', 'like', "%{$search}%")
                ->orWhere('village', 'like', "%{$search}%")
                ->orWhere('Province_addr', 'like', "%{$search}%")
                ->orWhere('specific_place', 'like', "%{$search}%")
                ->orWhere('cordenates', 'like', "%{$search}%")
                ->orWhere('employee_quantity', 'like', "%{$search}%")
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

    if ($cardFilter === 'withCoordinates') {
        $query->whereNotNull('cordenates')
            ->whereRaw("TRIM(cordenates) != ''");
    } elseif ($cardFilter === 'withPhone') {
        $query->whereNotNull('responsible_phone')
            ->whereRaw("TRIM(responsible_phone) != ''");
    } elseif ($cardFilter === 'withEmployees') {
        $query->whereNotNull('employee_quantity')
            ->whereRaw("TRIM(employee_quantity) != ''");
    }

    return $query;
}

public function printView(Request $request)
{
    $query = Sarayano::query()
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
    $title           = $request->input('title', 'چاپ سرایانو');

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
    $sarayano = Sarayano::findOrFail($id);

    return $this->removePictureFileAndClear($sarayano);
}
}