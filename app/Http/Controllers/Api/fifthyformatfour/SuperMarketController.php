<?php

namespace App\Http\Controllers\Api\fifthyformatfour;

use App\Http\Controllers\Controller;
use App\Http\Requests\fifthyformatfour\StoreSuperMarketRequest;
use App\Http\Requests\fifthyformatfour\UpdateSuperMarketRequest;
use App\Http\Resources\fifthyformatfour\SuperMarketResource;
use App\Models\fifthyformatfour\SuperMarket;
use App\Services\NotificationService; // 🔔 notifications
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Exports\SuperMarketExport;
use Maatwebsite\Excel\Facades\Excel;

class SuperMarketController extends Controller
{
    private function isDuplicateKey(QueryException $e): bool
    {
        return (int) ($e->errorInfo[1] ?? 0) === 1062; // MySQL duplicate key
    }

    private function duplicateResponse()
    {
        return response()->json([
            'message' => 'دا تجارتي مرکزونه او سوپرمارکیټونه مخکې ثبت شوي دي.',
            'errors' => [
                'duplicate' => [
                    'تجارتي مرکزونه او سوپرمارکیټونه په همدې مشخصاتو (نوم، د فعالیت ډول، د مراجعینو/دوکانونو شمېر او ولایت/آمریت/ولسوالي/قریه) سره مخکې په سیستم کې موجود دي.',
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

        $search = trim((string) $request->query('search', ''));
        $searchField = trim((string) $request->query('search_field', ''));
        $searchValue = trim((string) $request->query('search_value', ''));

        $allowedSearchFields = [

                'supermarket_name','market_activity_type','daily_clientele','shops_quantity',
        'responsible_name','responsible_f_name',
        'responsible_phone',
        'picture',
        'responsible_job',
        'province','zone','district','village','specefic_place','coordinates',
        'Province_addr',
        'employee_quantity','guard_quantity','resource_id','note',
        ];

        $query = SuperMarket::query()
            ->with(['resource:id,name,code_number,phone_number'])
            ->when($search !== '' && $searchField === '', function (Builder $sub) use ($search) {
                $sub->where(function (Builder $q) use ($search) {
                    $q->where('supermarket_name', 'like', "%{$search}%")
                        ->orWhere('province', 'like', "%{$search}%")
                        ->orWhere('village', 'like', "%{$search}%");
                });
            })
            ->when(
                $searchField !== '' && in_array($searchField, $allowedSearchFields, true),
                function (Builder $sub) use ($searchField, $searchValue, $search) {
                    $value = $searchValue !== '' ? $searchValue : $search;

                    if ($value !== '') {
                        $sub->where($searchField, 'like', "%{$value}%");
                    }
                }
            )
            ->orderByDesc('id');

        if ($isValidPage && $isValidPerPage) {
            $rows = $query->paginate((int) $perPage, ['*'], 'page', (int) $page);
            return SuperMarketResource::collection($rows);
        }

        return SuperMarketResource::collection($query->get());
    }

    /**
     * GET /super-markets/filter-options
     */
    public function filterOptions(Request $request)
    {
        $field = trim((string) $request->query('field', ''));
        $q = trim((string) $request->query('q', ''));

        $allowedFields = [

                'supermarket_name','market_activity_type','daily_clientele','shops_quantity',
        'responsible_name','responsible_f_name',
        'responsible_phone',
        'picture',
        'responsible_job',
        'province','zone','district','village','specefic_place','coordinates',
        'Province_addr',
        'employee_quantity','guard_quantity','resource_id','note',
        ];

        if ($field === '' || !in_array($field, $allowedFields, true)) {
            return response()->json([
                'data' => [
                    'options' => [],
                ],
            ]);
        }

        $query = SuperMarket::query()
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

    /**
     * POST /super-markets
     * Accepts:
     * - picture as FILE or STRING
     */
    public function store(StoreSuperMarketRequest $request)
    {
        $user = $request->user(); // 🔔
        $data = $request->validated();

        try {
            return DB::transaction(function () use ($request, $data, $user) {

                // ✅ picture: FILE OR STRING (same style as SaeenFasadController)
                if ($request->hasFile('picture')) {
                    $path = $request->file('picture')->store('uploads/super-markets', 'public');
                    $data['picture'] = "storage/{$path}";
                } else {
                    if (array_key_exists('picture', $data) && !is_string($data['picture'])) {
                        unset($data['picture']);
                    }
                }

                $row = SuperMarket::create($data);
                $row->load(['resource:id,name,code_number,phone_number']);

                // 🔔 Notification: CREATED
                NotificationService::record(
                    $user,
                    module: 'تجارتي مرکزونه او سوپرمارکیټونه',
                    action: 'created',
                    title: 'تجارتي مرکزونه او سوپرمارکیټونه  ثبت شوو',
                    message: 'نوم: ' . ($row->supermarket_name ?? ''),
                    routeName: 'super-markets',
                    routeParams: ['id' => $row->id],
                    level: 'success',
                    changes: null
                );

                return (new SuperMarketResource($row))
                    ->additional(['message' => 'Created'])
                    ->response()
                    ->setStatusCode(201);
            });
        } catch (QueryException $e) {
            if ($this->isDuplicateKey($e)) return $this->duplicateResponse();
            throw $e;
        }
    }

    public function show(SuperMarket $supermarket)
    {
        $supermarket->load(['resource:id,name,code_number,phone_number']);
        return new SuperMarketResource($supermarket);
    }

    /**
     * PUT /super-markets/{supermarket}
     * Accepts:
     * - picture as FILE or STRING (replaces old file if new uploaded)
     */
    public function update(UpdateSuperMarketRequest $request, SuperMarket $supermarket)
    {
        $user = $request->user(); // 🔔
        $data = $request->validated();

        try {
            return DB::transaction(function () use ($request, $supermarket, $data, $user) {
                // 🧮 changes BEFORE update
                $original = $supermarket->getOriginal();
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

                // ✅ picture: replace file if uploaded
                if ($request->hasFile('picture')) {
                    if ($supermarket->picture && str_starts_with($supermarket->picture, 'storage/')) {
                        $old = str_replace('storage/', '', $supermarket->picture);
                        Storage::disk('public')->delete($old);
                    }

                    $path = $request->file('picture')->store('uploads/super-markets', 'public');
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

                $supermarket->update($data);
                $supermarket->load(['resource:id,name,code_number,phone_number']);

                // 🔔 Notification: UPDATED
                NotificationService::record(
                    $user,
                    module: 'تجارتي مرکزونه او سوپرمارکیټونه',
                    action: 'updated',
                    title: 'تجارتي مرکزونه او سوپرمارکیټونه اصلاح شوو',
                    message: 'نوم: ' . ($supermarket->supermarket_name ?? ''),
                    routeName: 'super-markets',
                    routeParams: ['id' => $supermarket->id],
                    level: 'info',
                    changes: !empty($changes) ? $changes : null
                );

                return new SuperMarketResource($supermarket);
            });
        } catch (QueryException $e) {
            if ($this->isDuplicateKey($e)) return $this->duplicateResponse();
            throw $e;
        }
    }

    public function destroy(Request $request, SuperMarket $supermarket)
    {
        $user = $request->user(); // 🔔

        return DB::transaction(function () use ($supermarket, $user) {
            $name = $supermarket->supermarket_name;

            // ✅ delete picture file
            if ($supermarket->picture && str_starts_with($supermarket->picture, 'storage/')) {
                $old = str_replace('storage/', '', $supermarket->picture);
                Storage::disk('public')->delete($old);
            }

            $supermarket->delete();

            // 🔔 Notification: DELETED
            NotificationService::record(
                $user,
                module: 'تجارتي مرکزونه او سوپرمارکیټونه',
                action: 'deleted',
                title: 'تجارتي مرکزونه او سوپرمارکیټونه حذف شوو',
                message: 'نوم: ' . ($name ?? ''),
                routeName: null,
                routeParams: ['super-markets'],
                level: 'warning',
                changes: null
            );

            return response()->json(['message' => 'Deleted']);
        });
    }
    private function printSearchFields(): array
{
    return [
        'picture',

        'supermarket_name',
        'market_activity_type',
        'daily_clientele',
        'shops_quantity',

        'responsible_name',
        'responsible_f_name',
        'responsible_job',
        'responsible_phone',

        'province',
        'zone',
        'district',
        'village',
        'specefic_place',
        'Province_addr',

        'coordinates',

        'employee_quantity',
        'guard_quantity',

        'resource_id',
        'resource_code',
        'resource_phone',

        'note',
    ];
}

private function applyPrintFilters($query, Request $request)
{
    $search           = trim((string) $request->query('search', ''));
    $searchField      = trim((string) $request->query('search_field', ''));
    $searchValue      = trim((string) $request->query('search_value', ''));
    $cardFilter       = trim((string) $request->query('card_filter', 'all'));
    $fiftySixFormatId = $request->query('fifty_six_format_id');

    $allowedFields = $this->printSearchFields();

    if ($search !== '' && $searchField === '') {
        $query->where(function (Builder $q) use ($search) {
            $q->where('supermarket_name', 'like', "%{$search}%")
                ->orWhere('market_activity_type', 'like', "%{$search}%")
                ->orWhere('daily_clientele', 'like', "%{$search}%")
                ->orWhere('shops_quantity', 'like', "%{$search}%")
                ->orWhere('responsible_name', 'like', "%{$search}%")
                ->orWhere('responsible_f_name', 'like', "%{$search}%")
                ->orWhere('responsible_job', 'like', "%{$search}%")
                ->orWhere('responsible_phone', 'like', "%{$search}%")
                ->orWhere('province', 'like', "%{$search}%")
                ->orWhere('zone', 'like', "%{$search}%")
                ->orWhere('district', 'like', "%{$search}%")
                ->orWhere('village', 'like', "%{$search}%")
                ->orWhere('specefic_place', 'like', "%{$search}%")
                ->orWhere('Province_addr', 'like', "%{$search}%")
                ->orWhere('coordinates', 'like', "%{$search}%")
                ->orWhere('employee_quantity', 'like', "%{$search}%")
                ->orWhere('guard_quantity', 'like', "%{$search}%")
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

    /**
     * If your super_markets table has a real fifty_six_format_id column,
     * uncomment this block.
     */
    // if (is_numeric($fiftySixFormatId) && (int) $fiftySixFormatId > 0) {
    //     $query->where('fifty_six_format_id', (int) $fiftySixFormatId);
    // }

    if ($cardFilter === 'withCoordinates') {
        $query->whereNotNull('coordinates')
            ->whereRaw("TRIM(coordinates) != ''");
    } elseif ($cardFilter === 'withClients') {
        $query->whereNotNull('daily_clientele')
            ->where('daily_clientele', '>', 0);
    } elseif ($cardFilter === 'withEmployees') {
        $query->whereNotNull('employee_quantity')
            ->where('employee_quantity', '>', 0);
    }

    return $query;
}

public function printView(Request $request)
{
    $query = SuperMarket::query()
        ->with(['resource:id,name,code_number,phone_number']);

    $this->applyPrintFilters($query, $request);

    $rows = $query
        ->orderByDesc('id')
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
    $title           = $request->input('title', 'چاپ سوپرمارکیت‌ها');

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
    $superMarket = SuperMarket::findOrFail($id);

    return $this->removePictureFileAndClear($superMarket);
}

    public function exportExcel(Request $request)
    {
        $filters = $request->only(['search', 'search_field', 'search_value', 'resource_id']);
        return Excel::download(new SuperMarketExport($filters), 'supermarket_' . now()->format('Y_m_d_His') . '.xlsx');
    }
}