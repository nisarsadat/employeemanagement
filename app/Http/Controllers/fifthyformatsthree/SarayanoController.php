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
}