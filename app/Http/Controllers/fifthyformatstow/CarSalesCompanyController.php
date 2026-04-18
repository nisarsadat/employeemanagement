<?php

namespace App\Http\Controllers\fifthyformatstow;

use App\Http\Controllers\Controller;
use App\Http\Requests\fifthyformatstow\StoreCarSalesCompanyRequest;
use App\Http\Requests\fifthyformatstow\UpdateCarSalesCompanyRequest;
use App\Http\Resources\fifthyformatstow\CarSalesCompanyResource;
use App\Models\fifthyformatstow\CarSalesCompany;
use App\Services\NotificationService; // 🔔 notifications
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CarSalesCompanyController extends Controller
{
    private function duplicateErrorPashto(): array
    {
        return [
            'message' => 'دا د موټر پلورلو شرکت مخکې ثبت شوی دی. مهرباني وکړئ تکراري معلومات مه داخلوی.',
            'errors'  => [
                'duplicate' => [
                    'دا شرکت مخکې ثبت شوی دی (د شرکت نوم، مسؤل معلومات او پته یو شان دي).',
                ],
            ],
        ];
    }

    private function isDuplicateKey(QueryException $e): bool
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
        $page    = $request->query('page');
        $perPage = $request->query('perPage', $request->query('per_page'));

        $isValidPage    = is_numeric($page) && (int) $page > 0;
        $isValidPerPage = is_numeric($perPage) && (int) $perPage > 0;

        $search      = trim((string) $request->input('search', ''));
        $searchField = trim((string) $request->input('search_field', ''));
        $searchValue = trim((string) $request->input('search_value', ''));
        $rid         = $request->input('resource_id');

        $sortBy  = $request->input('sort_by', 'created_at');
        $sortDir = in_array($request->input('sort_dir'), ['asc','desc'], true)
            ? $request->input('sort_dir')
            : 'desc';

        $allowedSearchFields = [
           
        'car_sales_company_name',

        'responsible_name',
        'responsible_f_name',
        'responsible_position',
        'responsible_phone',
        'Province_addr',

        'province',
        'zone',
        'picture',
        'district',
        'village',
        'specific_place',
        'coordinates',

        'note',
        'resource_id',
        ];

        $q = CarSalesCompany::query()->with([
            'resource' => fn ($qr) => $qr->select('id','name','code_number','phone_number'),
        ]);

        if ($search !== '' && $searchField === '') {
            $q->where(function ($qq) use ($search) {
                $qq->where('car_sales_company_name', 'like', "%{$search}%")
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

        if ($rid) {
            $q->where('resource_id', $rid);
        }

        $q->orderBy($sortBy, $sortDir);

        if ($isValidPage && $isValidPerPage) {
            return CarSalesCompanyResource::collection(
                $q->paginate((int) $perPage, ['*'], 'page', (int) $page)
            );
        }

        return CarSalesCompanyResource::collection($q->get());
    }

    /**
     * GET /car-sales-companies/filter-options
     */
    public function filterOptions(Request $request)
    {
        $field = trim((string) $request->query('field', ''));
        $q     = trim((string) $request->query('q', ''));

        $allowedFields = [
           
        'car_sales_company_name',

        'responsible_name',
        'responsible_f_name',
        'responsible_position',
        'responsible_phone',
        'Province_addr',

        'province',
        'zone',
        'picture',
        'district',
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

        $query = CarSalesCompany::query()
            ->select($field)
            ->whereNotNull($field)
            ->where($field, '!=', '');

        if ($q !== '') {
            $query->where($field, 'like', "%{$q}%");
        }

        $options = $query
            ->distinct()
            ->limit(50)
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
     * POST /car-sales-companies
     * Accepts:
     * - picture as FILE or STRING
     */
    public function store(StoreCarSalesCompanyRequest $request)
    {
        $user = $request->user(); // 🔔
        $data = $request->validated();

        try {
            return DB::transaction(function () use ($request, $data, $user) {

                // ✅ picture: file OR string (same style as SaeenFasad)
                if ($request->hasFile('picture')) {
                    $path = $request->file('picture')->store('uploads/car-sales-companies', 'public');
                    $data['picture'] = "storage/{$path}";
                } else {
                    if (array_key_exists('picture', $data) && !is_string($data['picture'])) {
                        unset($data['picture']);
                    }
                }

                $company = CarSalesCompany::create($data);

                $company->load([
                    'resource' => fn ($qr) => $qr->select('id','name','code_number','phone_number'),
                ]);

                NotificationService::record(
                    $user,
                    module: 'موټر پلورونکي',
                    action: 'created',
                    title: 'شرکت ثبت شوو',
                    message: 'نوم: ' . ($company->car_sales_company_name ?? ''),
                    routeName: 'car-sales-companies', // ⚠️ adjust to your real route name
                    routeParams: ['id' => $company->id],
                    level: 'success',
                    changes: null
                );

                return (new CarSalesCompanyResource($company))
                    ->additional(['message' => 'Created'])
                    ->response()
                    ->setStatusCode(201);
            });
        } catch (QueryException $e) {
            if ($this->isDuplicateKey($e)) {
                return response()->json($this->duplicateErrorPashto(), 422);
            }
            throw $e;
        }
    }

    public function show(CarSalesCompany $carSalesCompany)
    {
        $carSalesCompany->load([
            'resource' => fn ($qr) => $qr->select('id','name','code_number','phone_number'),
        ]);

        return CarSalesCompanyResource::make($carSalesCompany);
    }

    /**
     * PUT /car-sales-companies/{id}
     * Accepts:
     * - picture as FILE or STRING (replaces old file if new uploaded)
     */
    public function update(UpdateCarSalesCompanyRequest $request, CarSalesCompany $carSalesCompany)
    {
        $user = $request->user(); // 🔔
        $validated = $request->validated();

        try {
            return DB::transaction(function () use ($request, $validated, $carSalesCompany, $user) {

                // 🧮 changes BEFORE update
                $original = $carSalesCompany->getOriginal();
                $changes  = [];

                foreach ($validated as $field => $newValue) {
                    if (array_key_exists($field, $original) && $original[$field] != $newValue) {
                        $changes[$field] = ['old' => $original[$field], 'new' => $newValue];
                    }
                }

                // ✅ picture replace (same style as SaeenFasad)
                if ($request->hasFile('picture')) {
                    $this->deletePictureIfStored($carSalesCompany->picture);

                    $path = $request->file('picture')->store('uploads/car-sales-companies', 'public');
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

                $carSalesCompany->update($validated);

                $carSalesCompany->load([
                    'resource' => fn ($qr) => $qr->select('id','name','code_number','phone_number'),
                ]);

                NotificationService::record(
                    $user,
                    module: 'موټر پلورونکي',
                    action: 'updated',
                    title: 'شرکت اصلاح شوو',
                    message: 'نوم: ' . ($carSalesCompany->car_sales_company_name ?? ''),
                    routeName: 'car-sales-companies',
                    routeParams: ['id' => $carSalesCompany->id],
                    level: 'info',
                    changes: !empty($changes) ? $changes : null
                );

                return CarSalesCompanyResource::make($carSalesCompany);
            });
        } catch (QueryException $e) {
            if ($this->isDuplicateKey($e)) {
                return response()->json($this->duplicateErrorPashto(), 422);
            }
            throw $e;
        }
    }

    public function destroy(Request $request, CarSalesCompany $carSalesCompany)
    {
        $user = $request->user(); // 🔔

        return DB::transaction(function () use ($carSalesCompany, $user) {
            $name = $carSalesCompany->car_sales_company_name;

            // ✅ delete picture file
            $this->deletePictureIfStored($carSalesCompany->picture);

            $carSalesCompany->delete();

            NotificationService::record(
                $user,
                module: 'موټر پلورونکي',
                action: 'deleted',
                title: 'شرکت حذف شوو',
                message: 'نوم: ' . ($name ?? ''),
                routeName: null,
                routeParams: ['car-sales-companies'],
                level: 'warning',
                changes: null
            );

            return response()->noContent();
        });
    }

    public function bulkDelete(Request $request)
    {
        $user = $request->user(); // 🔔
        $ids  = $request->input('ids', []);
        $ids  = is_array($ids) ? array_values(array_filter($ids)) : [];

        return DB::transaction(function () use ($ids, $user) {
            if (empty($ids)) {
                return response()->noContent();
            }

            // ✅ delete pictures first (so files don't remain)
            $rows = CarSalesCompany::whereIn('id', $ids)->get(['id', 'picture']);
            foreach ($rows as $row) {
                $this->deletePictureIfStored($row->picture);
            }

            CarSalesCompany::whereIn('id', $ids)->delete();

            NotificationService::record(
                $user,
                module: 'موټر پلورونکي',
                action: 'deleted',
                title: 'شرکت‌ها به‌صورت گروهی حذف شوو',
                message: 'تعداد: ' . count($ids),
                routeName: null,
                routeParams: ['car-sales-companies'],
                level: 'warning',
                changes: null
            );

            return response()->noContent();
        });
    }
}