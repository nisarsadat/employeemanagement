<?php

namespace App\Http\Controllers\fifthyformatstow;

use App\Http\Controllers\Controller;
use App\Http\Requests\fifthyformatstow\StoreBankRequest;
use App\Http\Requests\fifthyformatstow\UpdateBankRequest;
use App\Http\Resources\fifthyformatstow\BankResource;
use App\Models\fifthyformatstow\Bank;
use App\Services\NotificationService; // 🔔 (if you have it for this module)
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class BankController extends Controller
{
    /* =======================
     | Helpers (same style)
     ======================= */
    private function duplicateErrorPashto(): array
    {
        return [
            'message' => 'دا بانک مخکې ثبت شوی دی. مهرباني وکړئ تکراري معلومات مه داخلوی.',
            'errors'  => [
                'duplicate' => [
                    'دا بانک مخکې ثبت شوی دی (نوم/ډول، مسؤل معلومات او پته یو شان دي).',
                ],
            ],
        ];
    }

    private function isDuplicateKey(QueryException $e): bool
    {
        return (int) ($e->errorInfo[1] ?? 0) === 1062;
    }

    // ✅ picture delete helper (handles "storage/..." too)
    private function deletePictureIfStored(?string $picture): void
    {
        if (!$picture) return;

        $path = $picture;
        if (str_starts_with($path, 'storage/')) {
            $path = str_replace('storage/', '', $path);
        }

        Storage::disk('public')->delete($path);
    }

    /* =======================
     | GET /banks
     ======================= */
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
        $sortDir = in_array($request->input('sort_dir'), ['asc', 'desc'], true)
            ? $request->input('sort_dir')
            : 'desc';

        $allowedSearchFields = [
           
                'bank_name',
        'bank_type',
        'bank_owner',

        'responsible_name',
        'responsible_f_name',
        'responsible_position',
        'responsible_phone',

        'which_offices_sallary',
        'province',
        'zone',
        'picture',
        'district',
        'village',
        'specific_place',
        'Province_addr',
        'coordinates',

        'guard_quantity',
        'note',
        'resource_id',
        ];

        $q = Bank::query()
            ->with(['resource:id,name,code_number,phone_number']);

        if ($search !== '' && $searchField === '') {
            $q->where(function ($qq) use ($search) {
                $qq->where('bank_name', 'like', "%{$search}%")
                    ->orWhere('bank_type', 'like', "%{$search}%")
                    ->orWhere('bank_owner', 'like', "%{$search}%")
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
            return BankResource::collection(
                $q->paginate((int) $perPage, ['*'], 'page', (int) $page)
            );
        }

        return BankResource::collection($q->get());
    }

    /**
     * GET /banks/filter-options
     */
    public function filterOptions(Request $request)
    {
        $field = trim((string) $request->query('field', ''));
        $q     = trim((string) $request->query('q', ''));

        $allowedFields = [
           
                'bank_name',
        'bank_type',
        'bank_owner',

        'responsible_name',
        'responsible_f_name',
        'responsible_position',
        'responsible_phone',

        'which_offices_sallary',
        'province',
        'zone',
        'picture',
        'district',
        'village',
        'specific_place',
        'Province_addr',
        'coordinates',

        'guard_quantity',
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

        $query = Bank::query()
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

    /* =======================
     | POST /banks
     | Accepts picture as FILE or STRING
     ======================= */
    public function store(StoreBankRequest $request)
    {
        $user = $request->user(); // 🔔
        $data = $request->validated();

        try {
            return DB::transaction(function () use ($request, $data, $user) {

                // ✅ picture: file OR string
                if ($request->hasFile('picture')) {
                    $path = $request->file('picture')->store('uploads/banks', 'public');
                    $data['picture'] = "storage/{$path}";
                } else {
                    if (array_key_exists('picture', $data) && !is_string($data['picture'])) {
                        unset($data['picture']);
                    }
                }

                $bank = Bank::create($data);
                $bank->load(['resource:id,name,code_number,phone_number']);

                // 🔔 (optional) notification
                if (class_exists(NotificationService::class)) {
                    NotificationService::record(
                        $user,
                        module: 'بانکونه او نماینده ګیانې',
                        action: 'created',
                        title: 'بانک/نماینده ګي ثبت شوو',
                        message: 'نوم: ' . ($bank->bank_name ?? ''),
                        routeName: 'banks', // ⚠️ adjust if needed
                        routeParams: ['id' => $bank->id],
                        level: 'success',
                        changes: null
                    );
                }

                return (new BankResource($bank))
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

    /* =======================
     | GET /banks/{bank}
     ======================= */
    public function show(Bank $bank)
    {
        $bank->load(['resource:id,name,code_number,phone_number']);
        return BankResource::make($bank);
    }

    /* =======================
     | PUT /banks/{bank}
     | Replaces old picture if new uploaded
     ======================= */
    public function update(UpdateBankRequest $request, Bank $bank)
    {
        $user = $request->user(); // 🔔
        $validated = $request->validated();

        try {
            return DB::transaction(function () use ($request, $validated, $bank, $user) {

                // 🧮 changes BEFORE update
                $original = $bank->getOriginal();
                $changes  = [];

                foreach ($validated as $field => $newValue) {
                    if (array_key_exists($field, $original) && $original[$field] != $newValue) {
                        $changes[$field] = ['old' => $original[$field], 'new' => $newValue];
                    }
                }

                // ✅ picture replace
                if ($request->hasFile('picture')) {
                    $this->deletePictureIfStored($bank->picture);

                    $path = $request->file('picture')->store('uploads/banks', 'public');
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

                $bank->update($validated);
                $bank->load(['resource:id,name,code_number,phone_number']);

                // 🔔 (optional) notification
                if (class_exists(NotificationService::class)) {
                    NotificationService::record(
                        $user,
                        module: 'بانکونه او نماینده ګیانې',
                        action: 'updated',
                        title: 'بانک اصلاح شوو',
                        message: 'نوم: ' . ($bank->bank_name ?? ''),
                        routeName: 'banks',
                        routeParams: ['id' => $bank->id],
                        level: 'info',
                        changes: !empty($changes) ? $changes : null
                    );
                }

                return BankResource::make($bank);
            });
        } catch (QueryException $e) {
            if ($this->isDuplicateKey($e)) {
                return response()->json($this->duplicateErrorPashto(), 422);
            }
            throw $e;
        }
    }

    /* =======================
     | DELETE /banks/{bank}
     ======================= */
    public function destroy(Request $request, Bank $bank)
    {
        $user = $request->user(); // 🔔

        return DB::transaction(function () use ($bank, $user) {

            $bankName = $bank->bank_name;

            // ✅ delete picture file
            $this->deletePictureIfStored($bank->picture);

            $bank->delete();

            // 🔔 (optional) notification
            if (class_exists(NotificationService::class)) {
                NotificationService::record(
                    $user,
                    module: 'بانکونه او نماینده ګیانې',
                    action: 'deleted',
                    title: 'بانک حذف شوو',
                    message: 'نوم: ' . ($bankName ?? ''),
                    routeName: null,
                    routeParams: ['banks'],
                    level: 'warning',
                    changes: null
                );
            }

            return response()->noContent();
        });
    }

    /* =======================
     | POST /banks/bulk-delete
     ======================= */
    public function bulkDelete(Request $request)
    {
        $user = $request->user(); // 🔔

        $ids = $request->input('ids', []);
        $ids = is_array($ids) ? array_values(array_filter($ids)) : [];

        return DB::transaction(function () use ($ids, $user) {
            if (empty($ids)) {
                return response()->noContent();
            }

            // ✅ delete pictures first
            $rows = Bank::whereIn('id', $ids)->get(['id', 'picture']);
            foreach ($rows as $row) {
                $this->deletePictureIfStored($row->picture);
            }

            Bank::whereIn('id', $ids)->delete();

            // 🔔 (optional) notification
            if (class_exists(NotificationService::class)) {
                NotificationService::record(
                    $user,
                    module: 'بانکونه او نماینده ګیانې',
                    action: 'deleted',
                    title: 'بانکونه به‌صورت گروهی حذف شوو',
                    message: 'تعداد: ' . count($ids),
                    routeName: null,
                    routeParams: ['banks'],
                    level: 'warning',
                    changes: null
                );
            }

            return response()->noContent();
        });
    }
}