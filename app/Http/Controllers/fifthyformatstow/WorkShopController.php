<?php

namespace App\Http\Controllers\fifthyformatstow;

use App\Http\Controllers\Controller;
use App\Http\Requests\fifthyformatstow\StoreWorkShopRequest;
use App\Http\Requests\fifthyformatstow\UpdateWorkShopRequest;
use App\Http\Resources\fifthyformatstow\WorkShopResource;
use App\Models\fifthyformatstow\WorkShop;
use App\Services\NotificationService; // 🔔 notifications
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class WorkShopController extends Controller
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
                    'د همدې معلوماتو سره (ورکشاپ نوم، ډول، ولایت او زون) ریکارډ مخکې له مخکې موجود دی.',
                ],
            ],
        ], 422);
    }

    public function index(Request $request)
    {
        $page    = $request->query('page');
        $perPage = $request->query('perPage', $request->query('per_page'));

        $isValidPage    = is_numeric($page) && (int) $page > 0;
        $isValidPerPage = is_numeric($perPage) && (int) $perPage > 0;

        $search      = trim((string) $request->input('search', ''));
        $searchField = trim((string) $request->input('search_field', ''));
        $searchValue = trim((string) $request->input('search_value', ''));

        $allowedSearchFields = [
            'workshop_name',
        'workshop_type',

        'responsible_name',
        'responsible_f_name',
        'responsible_position',
        'responsible_phone',

        'province',
        'Province_addr',
        'zone',
        'picture',
        'district',
        'village',
        'specific_place',
        'coordinates',

        'workers_quantity',
        'note',
        'resource_id',
        ];

        $q = WorkShop::query()
            ->with(['resource:id,name,code_number,phone_number'])
            ->when($search !== '' && $searchField === '', function ($sub) use ($search) {
                $sub->where(function ($qq) use ($search) {
                    $qq->where('workshop_name', 'like', "%{$search}%")
                       ->orWhere('workshop_type', 'like', "%{$search}%")
                       ->orWhere('responsible_name', 'like', "%{$search}%")
                       ->orWhere('responsible_phone', 'like', "%{$search}%")
                       ->orWhere('province', 'like', "%{$search}%")
                       ->orWhere('district', 'like', "%{$search}%");
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
            )
            ->latest();

        if ($isValidPage && $isValidPerPage) {
            return WorkShopResource::collection(
                $q->paginate((int) $perPage, ['*'], 'page', (int) $page)
            );
        }

        return WorkShopResource::collection($q->get());
    }

    /**
     * GET /work-shops/filter-options
     */
    public function filterOptions(Request $request)
    {
        $field = trim((string) $request->query('field', ''));
        $q     = trim((string) $request->query('q', ''));

        $allowedFields = [
            'workshop_name',
        'workshop_type',

        'responsible_name',
        'responsible_f_name',
        'responsible_position',
        'responsible_phone',

        'province',
        'Province_addr',
        'zone',
        'picture',
        'district',
        'village',
        'specific_place',
        'coordinates',

        'workers_quantity',
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

        $query = WorkShop::query()
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

    public function store(StoreWorkShopRequest $request)
    {
        $user = $request->user(); // 🔔 who did this
        $data = $request->validated();

        try {
            // ✅ picture: file OR string (same style as SaeenFasad)
            if ($request->hasFile('picture')) {
                $path = $request->file('picture')->store('uploads/work-shops', 'public');
                $data['picture'] = "storage/{$path}";
            } else {
                if (array_key_exists('picture', $data) && !is_string($data['picture'])) {
                    unset($data['picture']);
                }
            }

            $workshop = WorkShop::create($data)
                ->load(['resource:id,name,code_number,phone_number']);

            // 🔔 Notification: CREATED
            NotificationService::record(
                $user,
                module: 'ورکشاپ',
                action: 'created',
                title: 'نوی ورکشاپ ثبت شو',
                message: 'نوم: ' . ($workshop->workshop_name ?? ''),
                routeName: 'work-shops',           // ⚠️ set to your real route name
                routeParams: ['id' => $workshop->id],
                level: 'success',
                changes: null
            );

            return WorkShopResource::make($workshop);
        } catch (QueryException $e) {
            if ($this->isDuplicateKey($e)) {
                return $this->duplicateResponse();
            }
            throw $e;
        }
    }

    public function show(WorkShop $workShop)
    {
        $workShop->load(['resource:id,name,code_number,phone_number']);
        return WorkShopResource::make($workShop);
    }

    public function update(UpdateWorkShopRequest $request, WorkShop $workShop)
    {
        $user = $request->user(); // 🔔
        $data = $request->validated();

        try {
            // 🧮 build changes BEFORE update
            $original = $workShop->getOriginal();
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
                if ($workShop->picture && str_starts_with($workShop->picture, 'storage/')) {
                    $old = str_replace('storage/', '', $workShop->picture);
                    Storage::disk('public')->delete($old);
                }

                $path = $request->file('picture')->store('uploads/work-shops', 'public');
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

            $workShop->update($data);
            $workShop->load(['resource:id,name,code_number,phone_number']);

            // 🔔 Notification: UPDATED
            NotificationService::record(
                $user,
                module: 'ورکشاپ',
                action: 'updated',
                title: 'ورکشاپ اصلاح شو',
                message: 'نوم: ' . ($workShop->workshop_name ?? ''),
                routeName: 'work-shops',           // ⚠️ set to your real route name
                routeParams: ['id' => $workShop->id],
                level: 'info',
                changes: !empty($changes) ? $changes : null
            );

            return WorkShopResource::make($workShop);
        } catch (QueryException $e) {
            if ($this->isDuplicateKey($e)) {
                return $this->duplicateResponse();
            }
            throw $e;
        }
    }

    public function destroy(Request $request, WorkShop $workShop)
    {
        $user = $request->user(); // 🔔
        $name = $workShop->workshop_name;

        // ✅ delete picture file (same style as SaeenFasad)
        if ($workShop->picture && str_starts_with($workShop->picture, 'storage/')) {
            $old = str_replace('storage/', '', $workShop->picture);
            Storage::disk('public')->delete($old);
        }

        $workShop->delete();

        // 🔔 Notification: DELETED
        NotificationService::record(
            $user,
            module: 'ورکشاپ',
            action: 'deleted',
            title: 'ورکشاپ حذف شو',
            message: 'نوم: ' . ($name ?? ''),
            routeName: null,
            routeParams: ['work-shops'],
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
        WorkShop::destroy($ids);

        // 🔔 Notification: BULK DELETED
        NotificationService::record(
            $user,
            module: 'ورکشاپ',
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
        'workshop_name',
        'workshop_type',

        'responsible_name',
        'responsible_f_name',
        'responsible_position',
        'responsible_phone',

        'province',
        'Province_addr',
        'zone',
        'picture',
        'district',
        'village',
        'specific_place',
        'coordinates',

        'workers_quantity',
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
            $q->where('workshop_name', 'like', "%{$search}%")
                ->orWhere('workshop_type', 'like', "%{$search}%")
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
                ->orWhere('workers_quantity', 'like', "%{$search}%")
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
    } elseif ($cardFilter === 'withWorkers') {
        $query->whereNotNull('workers_quantity')
            ->whereRaw("TRIM(workers_quantity) != ''")
            ->where('workers_quantity', '>', 0);
    } elseif ($cardFilter === 'withResource') {
        $query->whereNotNull('resource_id')
            ->whereRaw("TRIM(resource_id) != ''");
    }

    return $query;
}

public function printView(Request $request)
{
    $query = WorkShop::query()
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
    $title           = $request->input('title', 'چاپ ورکشاپ‌ها');

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