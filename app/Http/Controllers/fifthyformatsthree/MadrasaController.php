<?php

namespace App\Http\Controllers\fifthyformatsthree;

use App\Http\Controllers\Controller;
use App\Models\fifthyformatsthree\Madrasa;
use App\Http\Resources\fifthyformatsthree\MadrasaResource;
use App\Http\Requests\fifthyformatsthree\StoreMadrasaRequest;
use App\Http\Requests\fifthyformatsthree\UpdateMadrasaRequest;
use App\Services\NotificationService; // 🔔 notifications
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MadrasaController extends Controller
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
                    'د همدې معلوماتو سره (نوم، د مسئول نوم او ادرس) ریکارډ مخکې له مخکې موجود دی.',
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

        $search      = trim((string) $request->query('search', ''));
        $searchField = trim((string) $request->query('search_field', ''));
        $searchValue = trim((string) $request->query('search_value', ''));

        $allowedSearchFields = [
           
         'name',
        'madrasa_type',

        'responsible_name',
        'responsible_f_name',
        'responsible_position',
        'responsible_phone',
        'responsible_main_province',
        'tarafdari_type_responsible',

        'mazhab',
        'male_teacher_quantity',
        'female_teacher_quantity',
        'male_students_quantity',
        'Province_addr',
        'female_students_quantity',

        'province',
        'zone',
        'district',
        'village',
        'specific_place',
        'street_number',
        'coordinates',
        'note',
        'resource_id',
        ];

        $q = Madrasa::query()
            ->with(['resource:id,name,code_number,phone_number'])
            ->when($search !== '' && $searchField === '', function ($sub) use ($search) {
                $sub->where(function ($qq) use ($search) {
                    $qq->where('name', 'like', "%{$search}%")
                        ->orWhere('manager_name', 'like', "%{$search}%")
                        ->orWhere('main_province', 'like', "%{$search}%")
                        ->orWhere('main_district', 'like', "%{$search}%");
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
            return MadrasaResource::collection(
                $q->paginate((int) $perPage, ['*'], 'page', (int) $page)
            );
        }

        return MadrasaResource::collection($q->get());
    }

    /**
     * GET /madrasas/filter-options
     */
    public function filterOptions(Request $request)
    {
        $field = trim((string) $request->query('field', ''));
        $q     = trim((string) $request->query('q', ''));

        $allowedFields = [
           
         'name',
        'madrasa_type',

        'responsible_name',
        'responsible_f_name',
        'responsible_position',
        'responsible_phone',
        'responsible_main_province',
        'tarafdari_type_responsible',

        'mazhab',
        'male_teacher_quantity',
        'female_teacher_quantity',
        'male_students_quantity',
        'Province_addr',
        'female_students_quantity',

        'province',
        'zone',
        'district',
        'village',
        'specific_place',
        'street_number',
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

        $query = Madrasa::query()
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

    public function store(StoreMadrasaRequest $request)
    {
        $user = $request->user(); // 🔔
        $data = $request->validated();

        try {
            // ✅ picture: file OR string (same style as SaeenFasad)
            if ($request->hasFile('picture')) {
                $path = $request->file('picture')->store('uploads/madrasas', 'public');
                $data['picture'] = "storage/{$path}";
            } else {
                if (array_key_exists('picture', $data) && !is_string($data['picture'])) {
                    unset($data['picture']);
                }
            }

            $item = Madrasa::create($data)
                ->load(['resource:id,name,code_number,phone_number']);

            // 🔔 Notification: CREATED
            NotificationService::record(
                $user,
                module: 'مدرسې',
                action: 'created',
                title: 'نوې مدرسه ثبت شوه',
                message: 'نوم: ' . ($item->name ?? ''),
                routeName: 'madrasas',        // ⚠️ set to your real route name
                routeParams: ['id' => $item->id],
                level: 'success',
                changes: null
            );

            return MadrasaResource::make($item);
        } catch (QueryException $e) {
            if ($this->isDuplicateKey($e)) {
                return $this->duplicateResponse();
            }
            throw $e;
        }
    }

    public function show(Madrasa $madrasa)
    {
        $madrasa->load(['resource:id,name,code_number,phone_number']);
        return MadrasaResource::make($madrasa);
    }

    public function update(UpdateMadrasaRequest $request, Madrasa $madrasa)
    {
        $user = $request->user(); // 🔔
        $data = $request->validated();

        try {
            $original = $madrasa->getOriginal();
            $changes  = [];

            foreach ($data as $field => $newValue) {
                if (array_key_exists($field, $original)) {
                    $oldValue = $original[$field];
                    if ($oldValue != $newValue) {
                        $changes[$field] = ['old' => $oldValue, 'new' => $newValue];
                    }
                }
            }

            // ✅ picture: replace file if uploaded (same style as SaeenFasad)
            if ($request->hasFile('picture')) {
                if ($madrasa->picture && str_starts_with($madrasa->picture, 'storage/')) {
                    $old = str_replace('storage/', '', $madrasa->picture);
                    Storage::disk('public')->delete($old);
                }

                $path = $request->file('picture')->store('uploads/madrasas', 'public');
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

            $madrasa->update($data);
            $madrasa->load(['resource:id,name,code_number,phone_number']);

            // 🔔 Notification: UPDATED
            NotificationService::record(
                $user,
                module: 'مدرسې',
                action: 'updated',
                title: 'مدرسه اصلاح شوه',
                message: 'نوم: ' . ($madrasa->name ?? ''),
                routeName: 'madrasas',        // ⚠️ set to your real route name
                routeParams: ['id' => $madrasa->id],
                level: 'info',
                changes: !empty($changes) ? $changes : null
            );

            return MadrasaResource::make($madrasa);
        } catch (QueryException $e) {
            if ($this->isDuplicateKey($e)) {
                return $this->duplicateResponse();
            }
            throw $e;
        }
    }

    public function destroy(Request $request, Madrasa $madrasa)
    {
        $user = $request->user(); // 🔔
        $name = $madrasa->name;

        // ✅ delete picture file (same style as SaeenFasad)
        if ($madrasa->picture && str_starts_with($madrasa->picture, 'storage/')) {
            $old = str_replace('storage/', '', $madrasa->picture);
            Storage::disk('public')->delete($old);
        }

        $madrasa->delete();

        // 🔔 Notification: DELETED
        NotificationService::record(
            $user,
            module: 'مدرسې',
            action: 'deleted',
            title: 'مدرسه حذف شوه',
            message: 'نوم: ' . ($name ?? ''),
            routeName: null,
            routeParams: ['madrasas'],
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
        Madrasa::destroy($ids);

        // 🔔 Notification: BULK DELETED
        NotificationService::record(
            $user,
            module: 'مدرسې',
            action: 'bulk_deleted',
            title: 'ډله‌ییز حذف ترسره شو',
            message: 'حذف شوي ریکارډونه: ' . count($ids),
            routeName: null,
            routeParams: ['madrasas'],
            level: 'warning',
            changes: null
        );

        return response()->noContent();
    }
    private function printSearchFields(): array
{
    return [
        'name',
        'madrasa_type',

        'responsible_name',
        'responsible_f_name',
        'responsible_position',
        'responsible_phone',
        'responsible_main_province',
        'tarafdari_type_responsible',

        'mazhab',
        'male_teacher_quantity',
        'female_teacher_quantity',
        'male_students_quantity',
        'female_students_quantity',

        'province',
        'zone',
        'district',
        'village',
        'specific_place',
        'street_number',
        'coordinates',

        'Province_addr',
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
            $qq->where('name', 'like', "%{$search}%")
                ->orWhere('madrasa_type', 'like', "%{$search}%")
                ->orWhere('responsible_name', 'like', "%{$search}%")
                ->orWhere('responsible_f_name', 'like', "%{$search}%")
                ->orWhere('responsible_position', 'like', "%{$search}%")
                ->orWhere('responsible_phone', 'like', "%{$search}%")
                ->orWhere('responsible_main_province', 'like', "%{$search}%")
                ->orWhere('tarafdari_type_responsible', 'like', "%{$search}%")
                ->orWhere('mazhab', 'like', "%{$search}%")
                ->orWhere('male_teacher_quantity', 'like', "%{$search}%")
                ->orWhere('female_teacher_quantity', 'like', "%{$search}%")
                ->orWhere('male_students_quantity', 'like', "%{$search}%")
                ->orWhere('female_students_quantity', 'like', "%{$search}%")
                ->orWhere('province', 'like', "%{$search}%")
                ->orWhere('zone', 'like', "%{$search}%")
                ->orWhere('district', 'like', "%{$search}%")
                ->orWhere('village', 'like', "%{$search}%")
                ->orWhere('specific_place', 'like', "%{$search}%")
                ->orWhere('street_number', 'like', "%{$search}%")
                ->orWhere('coordinates', 'like', "%{$search}%")
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

    if ($cardFilter === 'withCoordinates') {
        $query->whereNotNull('coordinates')
            ->whereRaw("TRIM(coordinates) != ''");
    } elseif ($cardFilter === 'withPhone') {
        $query->whereNotNull('responsible_phone')
            ->whereRaw("TRIM(responsible_phone) != ''");
    } elseif ($cardFilter === 'withResponsible') {
        $query->whereNotNull('responsible_name')
            ->whereRaw("TRIM(responsible_name) != ''");
    }

    return $query;
}

public function printView(Request $request)
{
    $query = Madrasa::query()
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
    $title           = $request->input('title', 'چاپ مدرسه‌ها');

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