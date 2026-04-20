<?php

namespace App\Http\Controllers\Api\fifthyformatfour;

use App\Http\Controllers\Controller;
use App\Http\Requests\fifthyformatfour\StoreShoraRequest;
use App\Http\Requests\fifthyformatfour\UpdateShoraRequest;
use App\Http\Resources\fifthyformatfour\ShoraResource;
use App\Models\fifthyformatfour\Shora;
use App\Services\NotificationService; // 🔔 notifications
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ShoraController extends Controller
{
    private function isDuplicateKey(QueryException $e): bool
    {
        return (int) ($e->errorInfo[1] ?? 0) === 1062; // MySQL duplicate key
    }

    private function duplicateResponse()
    {
        return response()->json([
            'message' => 'دا محلي شوراګانې مخکې ثبت شوې دي.',
            'errors' => [
                'duplicate' => [
                    'محلي شوراګانې په همدې مشخصاتو (قریه، نوم، هدف، د افرادو/جلسو شمېر، ثبت او د جلسې ځای) سره مخکې په سیستم کې موجودې دي.',
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
                    'shora_village','note',
        'shora_name','shora_goal','people_quantity','week_meeting_quantity','regester','shora_meeting_place',
        'responsible_name','responsible_f_name','responsible_job','responsible_phone',
        'picture',
        'supplier_name','supplier_job','supplier_phone',
        'emarat_tarfdary',
        'shora_province','shora_zone','shora_district',
        'Province_addr',
        'coordinates',
        'resource_id',
        ];

        $query = Shora::query()
            ->with(['resource:id,name,code_number,phone_number'])
            ->when($search !== '' && $searchField === '', function (Builder $sub) use ($search) {
                $sub->where(function (Builder $q) use ($search) {
                    $q->where('shora_name', 'like', "%{$search}%")
                        ->orWhere('responsible_name', 'like', "%{$search}%")
                        ->orWhere('shora_province', 'like', "%{$search}%")
                        ->orWhere('shora_village', 'like', "%{$search}%");
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
            return ShoraResource::collection($rows);
        }

        return ShoraResource::collection($query->get());
    }

    /**
     * GET /shoras/filter-options
     */
    public function filterOptions(Request $request)
    {
        $field = trim((string) $request->query('field', ''));
        $q = trim((string) $request->query('q', ''));

        $allowedFields = [
                    'shora_village','note',
        'shora_name','shora_goal','people_quantity','week_meeting_quantity','regester','shora_meeting_place',
        'responsible_name','responsible_f_name','responsible_job','responsible_phone',
        'picture',
        'supplier_name','supplier_job','supplier_phone',
        'emarat_tarfdary',
        'shora_province','shora_zone','shora_district',
        'Province_addr',
        'coordinates',
        'resource_id',
        ];

        if ($field === '' || !in_array($field, $allowedFields, true)) {
            return response()->json([
                'data' => [
                    'options' => [],
                ],
            ]);
        }

        $query = Shora::query()
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
     * POST /shoras
     * Accepts:
     * - picture as FILE or STRING
     */
    public function store(StoreShoraRequest $request)
    {
        $user = $request->user(); // 🔔
        $data = $request->validated();

        try {
            return DB::transaction(function () use ($request, $data, $user) {

                // ✅ picture: FILE OR STRING (same style as SaeenFasadController)
                if ($request->hasFile('picture')) {
                    $path = $request->file('picture')->store('uploads/shoras', 'public');
                    $data['picture'] = "storage/{$path}";
                } else {
                    if (array_key_exists('picture', $data) && !is_string($data['picture'])) {
                        unset($data['picture']);
                    }
                }

                $row = Shora::create($data);
                $row->load(['resource:id,name,code_number,phone_number']);

                // 🔔 Notification: CREATED
                NotificationService::record(
                    $user,
                    module: 'محلي شوراګانې',
                    action: 'created',
                    title: 'محلي شوراګانې  ثبت شوو',
                    message: 'نوم: ' . ($row->shora_name ?? ''),
                    routeName: 'shoras',
                    routeParams: ['id' => $row->id],
                    level: 'success',
                    changes: null
                );

                return (new ShoraResource($row))
                    ->additional(['message' => 'Created'])
                    ->response()
                    ->setStatusCode(201);
            });
        } catch (QueryException $e) {
            if ($this->isDuplicateKey($e)) return $this->duplicateResponse();
            throw $e;
        }
    }

    public function show(Shora $shora)
    {
        $shora->load(['resource:id,name,code_number,phone_number']);
        return ShoraResource::make($shora);
    }

    /**
     * PUT /shoras/{shora}
     * Accepts:
     * - picture as FILE or STRING (replaces old file if new uploaded)
     */
    public function update(UpdateShoraRequest $request, Shora $shora)
    {
        $user = $request->user(); // 🔔
        $data = $request->validated();

        try {
            return DB::transaction(function () use ($request, $shora, $data, $user) {
                // 🧮 changes BEFORE update
                $original = $shora->getOriginal();
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
                    if ($shora->picture && str_starts_with($shora->picture, 'storage/')) {
                        $old = str_replace('storage/', '', $shora->picture);
                        Storage::disk('public')->delete($old);
                    }

                    $path = $request->file('picture')->store('uploads/shoras', 'public');
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

                $shora->update($data);
                $shora->load(['resource:id,name,code_number,phone_number']);

                // 🔔 Notification: UPDATED
                NotificationService::record(
                    $user,
                    module: 'محلي شوراګانې',
                    action: 'updated',
                    title: 'محلي شوراګانې اصلاح شوو',
                    message: ' نوم : ' . ($shora->shora_name ?? ''),
                    routeName: 'shoras',
                    routeParams: ['id' => $shora->id],
                    level: 'info',
                    changes: !empty($changes) ? $changes : null
                );

                return new ShoraResource($shora);
            });
        } catch (QueryException $e) {
            if ($this->isDuplicateKey($e)) return $this->duplicateResponse();
            throw $e;
        }
    }

    public function destroy(Request $request, Shora $shora)
    {
        $user = $request->user(); // 🔔

        return DB::transaction(function () use ($shora, $user) {
            $name = $shora->shora_name;

            // ✅ delete picture file (same style as SaeenFasadController)
            if ($shora->picture && str_starts_with($shora->picture, 'storage/')) {
                $old = str_replace('storage/', '', $shora->picture);
                Storage::disk('public')->delete($old);
            }

            $shora->delete();

            // 🔔 Notification: DELETED
            NotificationService::record(
                $user,
                module: 'محلي شوراګانې',
                action: 'deleted',
                title: 'محلي شوراګانې حذف شوږ',
                message: 'نوم: ' . ($name ?? ''),
                routeName: null,
                routeParams: ['shoras'],
                level: 'warning',
                changes: null
            );

            return response()->json(['message' => 'Deleted']);
        });
    }
}