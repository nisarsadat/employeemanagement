<?php

namespace App\Http\Controllers\fifthyFormats;

use App\Http\Controllers\Controller;
use App\Http\Requests\fifthyFormats\StoreMulkanoRequest;
use App\Http\Requests\fifthyFormats\UpdateMulkanoRequest;
use App\Http\Resources\fifthyFormats\MulkanoResource;
use App\Models\fifthyFormats\Mulkano;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\QueryException;
use App\Services\NotificationService; // 🔔 notifications

class MulkanoController extends Controller
{
    public function index(Request $request)
    {
        $page    = $request->query('page');
        $perPage = $request->query('perPage', $request->query('per_page'));

        $isValidPage    = is_numeric($page) && (int) $page > 0;
        $isValidPerPage = is_numeric($perPage) && (int) $perPage > 0;

        $search = trim((string) $request->query('search', ''));
        $rid    = $request->query('resource_id');
        $searchField = trim((string) $request->query('search_field', ''));
        $searchValue = trim((string) $request->query('search_value', ''));

        $sortable = ['id', 'name', 'created_at', 'updated_at'];
        $sortBy   = in_array($request->query('sort_by', 'created_at'), $sortable, true)
            ? $request->query('sort_by', 'created_at')
            : 'created_at';

        $sortDir = strtolower((string) $request->query('sort_dir', 'desc'));
        if (!in_array($sortDir, ['asc', 'desc'], true)) {
            $sortDir = 'desc';
        }

        $allowedSearchFields = [

        'name','picture','father_name','grand_father_name',
        'main_province','main_district','main_village',
        'now_province','now_zone','now_district','now_village',
        'specific_place','street_number','home_number','guzar',
        'tafseel_sahi','street_amount','old_job','new_job',
        'agent_quantity','home_quantity','phone_number',
        'Province_addr',
        'coordinates',
        'hawza',
        'nahya',
        'street',
        'jorm_history','tanzim','notes','resource_id',
        ];

        $q = Mulkano::query()
            ->with(['resource:id,name,code_number,phone_number'])
            ->when($search !== '' && $searchField === '', function ($sub) use ($search) {
                $sub->where(function ($x) use ($search) {
                    $x->where('name', 'like', "%{$search}%")
                      ->orWhere('father_name', 'like', "%{$search}%")
                      ->orWhere('grand_father_name', 'like', "%{$search}%")
                      ->orWhere('phone_number', 'like', "%{$search}%")
                      ->orWhere('specific_place', 'like', "%{$search}%");
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
            ->when($rid, fn ($sub) => $sub->where('resource_id', $rid))
            ->orderBy($sortBy, $sortDir);

        if ($isValidPage && $isValidPerPage) {
            return MulkanoResource::collection(
                $q->paginate((int) $perPage, ['*'], 'page', (int) $page)
            );
        }

        return MulkanoResource::collection($q->get());
    }

    public function filterOptions(Request $request)
    {
        $field = trim((string) $request->query('field', ''));
        $q = trim((string) $request->query('q', ''));

        $allowedFields = [

        'name','picture','father_name','grand_father_name',
        'main_province','main_district','main_village',
        'now_province','now_zone','now_district','now_village',
        'specific_place','street_number','home_number','guzar',
        'tafseel_sahi','street_amount','old_job','new_job',
        'agent_quantity','home_quantity','phone_number',
        'Province_addr',
        'coordinates',
        'hawza',
        'nahya',
        'street',
        'jorm_history','tanzim','notes','resource_id',
        ];

        if ($field === '' || !in_array($field, $allowedFields, true)) {
            return response()->json([
                'data' => [
                    'options' => [],
                ],
            ]);
        }

        $query = Mulkano::query()
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

    public function show(Mulkano $mulkano)
    {
        $mulkano->load(['resource:id,name,code_number,phone_number']);
        return new MulkanoResource($mulkano);
    }

    public function store(StoreMulkanoRequest $request)
    {
        $user = $request->user(); // 🔔
        $data = $request->validated();

        try {
            return DB::transaction(function () use ($request, $data, $user) {

                // ✅ picture
                if ($request->hasFile('picture')) {
                    $path = $request->file('picture')->store('uploads/mulkanos', 'public');
                    $data['picture'] = "storage/{$path}";
                } else {
                    if (array_key_exists('picture', $data) && !is_string($data['picture'])) {
                        unset($data['picture']);
                    }
                }

                $mulkano = Mulkano::create($data);
                $mulkano->load(['resource:id,name,code_number,phone_number']);

                // 🔔 Notification: CREATED
                NotificationService::record(
                    $user,
                    module: 'د کوڅو نماینده ګان',
                    action: 'created',
                    title: 'نوی ریکارډ ثبت شو',
                    message: 'نوم: ' . ($mulkano->name ?? ''),
                    routeName: 'mulkanos',
                    routeParams: ['id' => $mulkano->id],
                    level: 'success',
                    changes: null
                );

                return (new MulkanoResource($mulkano))
                    ->additional(['message' => 'Created'])
                    ->response()
                    ->setStatusCode(201);
            });
        } catch (QueryException $e) {
            if ((int) ($e->errorInfo[1] ?? 0) === 1062) {
                return response()->json([
                    'message' => 'دا ریکارډ له مخکې نه موجود دی (تکراري ثبت اجازه نشته).',
                    'errors'  => [
                        'duplicate' => ['دا ریکارډ له مخکې نه موجود دی (تکراري ثبت اجازه نشته).'],
                    ],
                ], 422);
            }
            throw $e;
        }
    }

    public function update(UpdateMulkanoRequest $request, Mulkano $mulkano)
    {
        $user = $request->user(); // 🔔
        $data = $request->validated();

        try {
            return DB::transaction(function () use ($request, $data, $mulkano, $user) {

                // 🧮 changes BEFORE update
                $original = $mulkano->getOriginal();
                $changes  = [];

                foreach ($data as $field => $newValue) {
                    if (array_key_exists($field, $original) && $original[$field] != $newValue) {
                        $changes[$field] = [
                            'old' => $original[$field],
                            'new' => $newValue,
                        ];
                    }
                }

                // ✅ picture replace
                if ($request->hasFile('picture')) {
                    if ($mulkano->picture && str_starts_with($mulkano->picture, 'storage/')) {
                        Storage::disk('public')->delete(str_replace('storage/', '', $mulkano->picture));
                    }

                    $path = $request->file('picture')->store('uploads/mulkanos', 'public');
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

                $mulkano->update($data);
                $mulkano->load(['resource:id,name,code_number,phone_number']);

                // 🔔 Notification: UPDATED
                NotificationService::record(
                    $user,
                    module: 'د کوڅو نماینده ګان',
                    action: 'updated',
                    title: 'ریکارډ اصلاح شو',
                    message: 'نوم: ' . ($mulkano->name ?? ''),
                    routeName: 'mulkanos',
                    routeParams: ['id' => $mulkano->id],
                    level: 'info',
                    changes: !empty($changes) ? $changes : null
                );

                return new MulkanoResource($mulkano);
            });
        } catch (QueryException $e) {
            if ((int) ($e->errorInfo[1] ?? 0) === 1062) {
                return response()->json([
                    'message' => 'دا ریکارډ له مخکې نه موجود دی (تکراري ثبت اجازه نشته).',
                    'errors'  => [
                        'duplicate' => ['دا ریکارډ له مخکې نه موجود دی (تکراري ثبت اجازه نشته).'],
                    ],
                ], 422);
            }
            throw $e;
        }
    }

    public function destroy(Request $request, Mulkano $mulkano)
    {
        $user = $request->user(); // 🔔

        return DB::transaction(function () use ($mulkano, $user) {

            if ($mulkano->picture && str_starts_with($mulkano->picture, 'storage/')) {
                Storage::disk('public')->delete(str_replace('storage/', '', $mulkano->picture));
            }

            $name = $mulkano->name;
            $mulkano->delete();

            // 🔔 Notification: DELETED
            NotificationService::record(
                $user,
                module: 'د کوڅو نماینده ګان',
                action: 'deleted',
                title: 'ریکارډ حذف شو',
                message: 'نوم: ' . ($name ?? ''),
                routeName: null,
                routeParams: ['mulkanos'],
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
        'picture',
        'father_name',
        'grand_father_name',
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
        'guzar',
        'tafseel_sahi',
        'street_amount',
        'old_job',
        'new_job',
        'agent_quantity',
        'home_quantity',
        'phone_number',
        'Province_addr',
        'coordinates',
        'hawza',
        'nahya',
        'street',
        'jorm_history',
        'tanzim',
        'notes',
        'resource_id',
        'resource_code',
        'resource_phone_number',
    ];
}

private function applyPrintFilters($query, Request $request)
{
    $search      = trim((string) $request->query('search', ''));
    $searchField = trim((string) $request->query('search_field', ''));
    $searchValue = trim((string) $request->query('search_value', ''));

    $resourceId = $request->query('resource_id');
    $allowedFields = $this->printSearchFields();

    if ($search !== '' && $searchField === '') {
        $query->where(function ($x) use ($search) {
            $x->where('mulkanos.name', 'like', "%{$search}%")
                ->orWhere('mulkanos.father_name', 'like', "%{$search}%")
                ->orWhere('mulkanos.grand_father_name', 'like', "%{$search}%")
                ->orWhere('mulkanos.main_province', 'like', "%{$search}%")
                ->orWhere('mulkanos.main_district', 'like', "%{$search}%")
                ->orWhere('mulkanos.main_village', 'like', "%{$search}%")
                ->orWhere('mulkanos.now_province', 'like', "%{$search}%")
                ->orWhere('mulkanos.now_zone', 'like', "%{$search}%")
                ->orWhere('mulkanos.now_district', 'like', "%{$search}%")
                ->orWhere('mulkanos.now_village', 'like', "%{$search}%")
                ->orWhere('mulkanos.specific_place', 'like', "%{$search}%")
                ->orWhere('mulkanos.street_number', 'like', "%{$search}%")
                ->orWhere('mulkanos.home_number', 'like', "%{$search}%")
                ->orWhere('mulkanos.guzar', 'like', "%{$search}%")
                ->orWhere('mulkanos.tafseel_sahi', 'like', "%{$search}%")
                ->orWhere('mulkanos.old_job', 'like', "%{$search}%")
                ->orWhere('mulkanos.new_job', 'like', "%{$search}%")
                ->orWhere('mulkanos.phone_number', 'like', "%{$search}%")
                ->orWhere('mulkanos.jorm_history', 'like', "%{$search}%")
                ->orWhere('mulkanos.tanzim', 'like', "%{$search}%")
                ->orWhere('mulkanos.notes', 'like', "%{$search}%")
                ->orWhere('mulkanos.coordinates', 'like', "%{$search}%")
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
            } elseif ($searchField === 'resource_id') {
                $query->where('mulkanos.resource_id', 'like', "%{$value}%");
            } else {
                $query->where("mulkanos.{$searchField}", 'like', "%{$value}%");
            }
        }
    }

    if (is_numeric($resourceId) && (int) $resourceId > 0) {
        $query->where('mulkanos.resource_id', (int) $resourceId);
    }

    return $query;
}

private function cleanPrintValue($value): string
{
    if ($value === null || $value === '') {
        return '';
    }

    $value = trim((string) $value);

    return preg_replace('/\s+00:00:00$/', '', $value);
}

public function printView(Request $request)
{
    $query = Mulkano::query()
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

            $row->resource_phone_number =
                $row->resource?->phone_number
                ?? '-';

            foreach ($row->getAttributes() as $key => $value) {
                if (is_string($value)) {
                    $row->{$key} = $this->cleanPrintValue($value);
                }
            }

            return $row;
        });

    $selectedColumns = $request->input('selected_columns', []);
    $columnLabels    = $request->input('column_labels', []);
    $printLabels     = $request->input('print_labels', []);
    $locale          = $request->input('locale', 'fa');
    $title           = $request->input('title', 'چاپ مُلکانو');

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