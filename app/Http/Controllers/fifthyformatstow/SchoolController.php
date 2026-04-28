<?php

namespace App\Http\Controllers\fifthyformatstow;

use App\Http\Controllers\Controller;
use App\Http\Requests\fifthyformatstow\StoreSchoolRequest;
use App\Http\Requests\fifthyformatstow\UpdateSchoolRequest;
use App\Http\Resources\fifthyformatstow\SchoolResource;
use App\Models\fifthyformatstow\School;
use App\Services\NotificationService; // 🔔 notifications
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class SchoolController extends Controller
{
    private function duplicateErrorPashto(): array
    {
        return [
            'message' => 'دا ښوونځی مخکې ثبت شوی دی. مهرباني وکړئ تکراري معلومات مه داخلوی.',
            'errors'  => [
                'duplicate' => [
                    'دا ښوونځی مخکې ثبت شوی دی (د ښوونځي نوم/ډول، د مدیر معلومات او پته یو شان دي).',
                ],
            ],
        ];
    }

    private function isDuplicateKeyException(QueryException $e): bool
    {
        return (int) ($e->errorInfo[1] ?? 0) === 1062;
    }

    // ✅ SaeenFasad-style picture delete helper (handles "storage/..." too)
    private function deletePictureIfStored(?string $picture): void
    {
        if (!$picture) return;

        $path = $picture;
        if (str_starts_with($path, 'storage/')) {
            $path = str_replace('storage/', '', $path);
        }

        Storage::disk('public')->delete($path);
    }

    public function index(Request $request)
    {
        $perPage = (int) $request->input('perPage', 10);
        $search  = trim((string) $request->input('search', ''));
        $rid     = $request->input('resource_id');
        $searchField = trim((string) $request->input('search_field', ''));
        $searchValue = trim((string) $request->input('search_value', ''));

        $sortBy  = $request->input('sort_by', 'created_at');
        $sortDir = in_array($request->input('sort_dir'), ['asc', 'desc'], true)
            ? $request->input('sort_dir')
            : 'desc';

        $allowedSearchFields = [
            'school_name',
        'school_type',

        'principle_name',
        'principle_f_name',
        'principle_phone',

        'male_teachers_quantity',
        'female_teachers_quantity',
        'male_students_quantity',
        'female_students_quantity',
        'picture',

        'province',
        'Province_addr',
        'zone',
        'district',
        'specific_place',
        'village',
        'street_number',
        'coordinates',

        'note',
        'resource_id',
        ];

        $q = School::query()->with([
            'resource' => fn ($qr) => $qr->select('id', 'name', 'code_number', 'phone_number'),
        ]);

        if ($search !== '' && $searchField === '') {
            $q->where(function ($qq) use ($search) {
                $qq->where('school_name', 'like', "%{$search}%")
                   ->orWhere('principle_name', 'like', "%{$search}%")
                   ->orWhere('principle_phone', 'like', "%{$search}%")
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

        if ($rid) {
            $q->where('resource_id', $rid);
        }

        $q->orderBy($sortBy, $sortDir);

        return SchoolResource::collection($q->paginate($perPage));
    }

    public function filterOptions(Request $request)
    {
        $field = trim((string) $request->query('field', ''));
        $q = trim((string) $request->query('q', ''));

        $allowedFields = [
            'school_name',
        'school_type',

        'principle_name',
        'principle_f_name',
        'principle_phone',

        'male_teachers_quantity',
        'female_teachers_quantity',
        'male_students_quantity',
        'female_students_quantity',
        'picture',

        'province',
        'Province_addr',
        'zone',
        'district',
        'specific_place',
        'village',
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

        $query = School::query()
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

    /** POST /schools
     * Accepts:
     * - picture as FILE or STRING
     */
    public function store(StoreSchoolRequest $request)
    {
        $user = $request->user(); // 🔔
        $data = $request->validated();

        try {
            return DB::transaction(function () use ($request, $data, $user) {

                // ✅ picture: file OR string (same style as SaeenFasad)
                if ($request->hasFile('picture')) {
                    $path = $request->file('picture')->store('uploads/schools', 'public');
                    $data['picture'] = "storage/{$path}";
                } else {
                    if (array_key_exists('picture', $data) && !is_string($data['picture'])) {
                        unset($data['picture']);
                    }
                }

                $school = School::create($data);

                $school->load([
                    'resource' => fn ($qr) => $qr->select('id', 'name', 'code_number', 'phone_number'),
                ]);

                NotificationService::record(
                    $user,
                    module: 'ښوونیژ او روزنیز مراکز',
                    action: 'created',
                    title: 'ښوونیز او روزنیز مرکز جدید ثبت شوو',
                    message: 'نام ښوونیز او روزنیز مرکز: ' . ($school->school_name ?? ''),
                    routeName: 'schools', // ⚠️ set your real route name
                    routeParams: ['id' => $school->id],
                    level: 'success',
                    changes: null
                );

                return (new SchoolResource($school))
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

    public function show(School $school)
    {
        $school->load([
            'resource' => fn ($qr) => $qr->select('id', 'name', 'code_number', 'phone_number'),
        ]);

        return SchoolResource::make($school);
    }

    /** PUT /schools/{school}
     * Accepts:
     * - picture as FILE or STRING (replaces old file if new uploaded)
     */
    public function update(UpdateSchoolRequest $request, School $school)
    {
        $user = $request->user(); // 🔔
        $validated = $request->validated();

        try {
            return DB::transaction(function () use ($request, $school, $validated, $user) {

                // 🧮 changes BEFORE update
                $original = $school->getOriginal();
                $changes  = [];

                foreach ($validated as $field => $newValue) {
                    if (array_key_exists($field, $original) && $original[$field] != $newValue) {
                        $changes[$field] = ['old' => $original[$field], 'new' => $newValue];
                    }
                }

                // ✅ picture replace (same style as SaeenFasad)
                if ($request->hasFile('picture')) {
                    $this->deletePictureIfStored($school->picture);

                    $path = $request->file('picture')->store('uploads/schools', 'public');
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

                $school->update($validated);

                $school->load([
                    'resource' => fn ($qr) => $qr->select('id', 'name', 'code_number', 'phone_number'),
                ]);

                NotificationService::record(
                    $user,
                    module: 'ښوونیژ او روزنیز مراکز',
                    action: 'updated',
                    title: 'ښوونیز او روزنیز مرکز اصلاح شوو',
                    message: 'نام ښوونیز او روزنیز مرکز: ' . ($school->school_name ?? ''),
                    routeName: 'schools',
                    routeParams: ['id' => $school->id],
                    level: 'info',
                    changes: !empty($changes) ? $changes : null
                );

                return SchoolResource::make($school);
            });
        } catch (QueryException $e) {
            if ($this->isDuplicateKeyException($e)) {
                return response()->json($this->duplicateErrorPashto(), 422);
            }
            throw $e;
        }
    }

    public function destroy(Request $request, School $school)
    {
        $user = $request->user(); // 🔔

        return DB::transaction(function () use ($school, $user) {
            $name = $school->school_name;

            // ✅ delete picture file
            $this->deletePictureIfStored($school->picture);

            $school->delete();

            NotificationService::record(
                $user,
                module: 'ښوونیژ او روزنیز مراکز',
                action: 'deleted',
                title: 'ښوونیز او روزنیز مرکز حذف شوو',
                message: 'نام ښوونیز او روزنیز مرکز: ' . ($name ?? ''),
                routeName: null,
                routeParams: ['schools'],
                level: 'warning',
                changes: null
            );

            return response()->noContent();
        });
    }

    public function bulkDelete(Request $request)
    {
        $user = $request->user(); // 🔔

        $ids = $request->input('ids', []);
        $ids = is_array($ids) ? array_values(array_filter($ids)) : [];

        return DB::transaction(function () use ($ids, $user) {
            if (empty($ids)) {
                return response()->noContent();
            }

            // ✅ delete pictures first (so files don't remain)
            $rows = School::whereIn('id', $ids)->get(['id', 'picture']);
            foreach ($rows as $row) {
                $this->deletePictureIfStored($row->picture);
            }

            School::whereIn('id', $ids)->delete();

            NotificationService::record(
                $user,
                module: 'ښوونیژ او روزنیز مراکز',
                action: 'deleted',
                title: 'ښوونیژ او روزنیز مراکز به‌صورت گروهی حذف شوو',
                message: 'تعداد: ' . count($ids),
                routeName: null,
                routeParams: ['schools'],
                level: 'warning',
                changes: null
            );

            return response()->noContent();
        });
    }
    private function printSearchFields(): array
{
    return [
        'school_name',
        'school_type',

        'principle_name',
        'principle_f_name',
        'principle_phone',

        'male_teachers_quantity',
        'female_teachers_quantity',
        'male_students_quantity',
        'female_students_quantity',
        'picture',

        'province',
        'Province_addr',
        'zone',
        'district',
        'specific_place',
        'village',
        'street_number',
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

    $sortBy = trim((string) $request->query('sort_by', 'created_at'));
    $sortDir = in_array($request->query('sort_dir'), ['asc', 'desc'], true)
        ? $request->query('sort_dir')
        : 'desc';

    $allowedFields = $this->printSearchFields();

    if ($search !== '' && $searchField === '') {
        $query->where(function ($qq) use ($search) {
            $qq->where('school_name', 'like', "%{$search}%")
                ->orWhere('school_type', 'like', "%{$search}%")
                ->orWhere('principle_name', 'like', "%{$search}%")
                ->orWhere('principle_f_name', 'like', "%{$search}%")
                ->orWhere('principle_phone', 'like', "%{$search}%")
                ->orWhere('male_teachers_quantity', 'like', "%{$search}%")
                ->orWhere('female_teachers_quantity', 'like', "%{$search}%")
                ->orWhere('male_students_quantity', 'like', "%{$search}%")
                ->orWhere('female_students_quantity', 'like', "%{$search}%")
                ->orWhere('province', 'like', "%{$search}%")
                ->orWhere('Province_addr', 'like', "%{$search}%")
                ->orWhere('zone', 'like', "%{$search}%")
                ->orWhere('district', 'like', "%{$search}%")
                ->orWhere('specific_place', 'like', "%{$search}%")
                ->orWhere('village', 'like', "%{$search}%")
                ->orWhere('street_number', 'like', "%{$search}%")
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
    } elseif ($cardFilter === 'withFemaleStudents') {
        $query->where('female_students_quantity', '>', 0);
    } elseif ($cardFilter === 'withFemaleTeachers') {
        $query->where('female_teachers_quantity', '>', 0);
    }

    return [
        'query' => $query,
        'sort_by' => $sortBy,
        'sort_dir' => $sortDir,
    ];
}

public function printView(Request $request)
{
    $query = School::query()->with([
        'resource' => fn ($qr) => $qr->select('id', 'name', 'code_number', 'phone_number'),
    ]);

    $filterResult = $this->applyPrintFilters($query, $request);

    $rows = $filterResult['query']
        ->orderBy($filterResult['sort_by'], $filterResult['sort_dir'])
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
    $title           = $request->input('title', 'چاپ مکتب ها');

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
    $school = School::findOrFail($id);

    return $this->removePictureFileAndClear($school);
}
}