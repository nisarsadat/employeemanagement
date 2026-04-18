<?php

namespace App\Http\Controllers\fifthyformatone;

use App\Http\Controllers\Controller;
use App\Http\Requests\fifthyformatone\StoreDamagePlacesRequest;
use App\Http\Requests\fifthyformatone\UpdateDamagePlacesRequest;
use App\Http\Resources\fifthyformatone\DamagePlacesResource;
use App\Models\fifthyformatone\DamagePlaces;
use App\Services\NotificationService; // 🔔 notifications
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DamagePlacesController extends Controller
{
    private function duplicateErrorPashto(): array
    {
        return [
            'message' => 'دا ریکارډ مخکې ثبت شوی دی. مهرباني وکړئ تکراري معلومات مه داخلوی.',
            'errors'  => [
                'duplicate' => [
                    'دا ځای مخکې ثبت شوی دی (موقعیت، د تهدید ډول او د مسؤل معلومات یو شان دي).',
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

    /** GET /damage-places */
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
            
                'province',
        'zone',
        'picture',
        'district',
        'village',
        'specific_place',
        'specific_dot',
        'coordinates',
        'tahdid_type',
        'percentage',
        'tahdid_place',
        'Province_addr',
        'amnyat_khala',
        'cameesa_tafseel',
        'guard_quantity',
        'masool_name',
        'masool_f_name',
        'masool_position',
        'masool_phone',
        'note',
        'resource_id',
        ];

        $q = DamagePlaces::query()->with([
            'resource' => fn ($qr) => $qr->select('id', 'name', 'code_number', 'phone_number'),
        ]);

        if ($search !== '' && $searchField === '') {
            $q->where(function ($w) use ($search) {
                $w->where('province', 'like', "%{$search}%")
                  ->orWhere('district', 'like', "%{$search}%")
                  ->orWhere('village', 'like', "%{$search}%")
                  ->orWhere('tahdid_type', 'like', "%{$search}%");
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

        return DamagePlacesResource::collection($q->paginate($perPage));
    }

    public function filterOptions(Request $request)
    {
        $field = trim((string) $request->query('field', ''));
        $q = trim((string) $request->query('q', ''));

        $allowedFields = [
            
                'province',
        'zone',
        'picture',
        'district',
        'village',
        'specific_place',
        'specific_dot',
        'coordinates',
        'tahdid_type',
        'percentage',
        'tahdid_place',
        'Province_addr',
        'amnyat_khala',
        'cameesa_tafseel',
        'guard_quantity',
        'masool_name',
        'masool_f_name',
        'masool_position',
        'masool_phone',
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

        $query = DamagePlaces::query()
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

    /** POST /damage-places
     * Accepts:
     * - picture as FILE or STRING
     */
    public function store(StoreDamagePlacesRequest $request)
    {
        $user = $request->user(); // 🔔
        $data = $request->validated();

        try {
            return DB::transaction(function () use ($request, $data, $user) {

                // ✅ picture: file OR string (same style as SaeenFasad)
                if ($request->hasFile('picture')) {
                    $path = $request->file('picture')->store('uploads/damage-places', 'public');
                    $data['picture'] = "storage/{$path}";
                } else {
                    if (array_key_exists('picture', $data) && !is_string($data['picture'])) {
                        unset($data['picture']);
                    }
                }

                $record = DamagePlaces::create($data);

                $record->load([
                    'resource' => fn ($qr) => $qr->select('id', 'name', 'code_number', 'phone_number'),
                ]);

                NotificationService::record(
                    $user,
                    module: 'آسیب پذیره ساحې',
                    action: 'created',
                    title: 'نوی ځای',
                    message: 'تهدید: ' . ($record->tahdid_type ?? '') . ' | ولایت: ' . ($record->province ?? ''),
                    routeName: 'damage-places', // ⚠️ set your real route name
                    routeParams: ['id' => $record->id],
                    level: 'success',
                    changes: null
                );

                return (new DamagePlacesResource($record))
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

    /** GET /damage-places/{damage_place} */
    public function show(DamagePlaces $damage_place)
    {
        $damage_place->load([
            'resource' => fn ($qr) => $qr->select('id', 'name', 'code_number', 'phone_number'),
        ]);

        return DamagePlacesResource::make($damage_place);
    }

    /** PUT /damage-places/{damage_place}
     * Accepts:
     * - picture as FILE or STRING (replaces old file if new uploaded)
     */
    public function update(UpdateDamagePlacesRequest $request, DamagePlaces $damage_place)
    {
        $user = $request->user(); // 🔔
        $validated = $request->validated();

        try {
            return DB::transaction(function () use ($request, $validated, $damage_place, $user) {

                // 🧮 changes BEFORE update
                $original = $damage_place->getOriginal();
                $changes  = [];

                foreach ($validated as $field => $newValue) {
                    if (array_key_exists($field, $original) && $original[$field] != $newValue) {
                        $changes[$field] = [
                            'old' => $original[$field],
                            'new' => $newValue,
                        ];
                    }
                }

                // ✅ picture: replace file if uploaded (same style as SaeenFasad)
                if ($request->hasFile('picture')) {
                    $this->deletePictureIfStored($damage_place->picture);

                    $path = $request->file('picture')->store('uploads/damage-places', 'public');
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

                $damage_place->update($validated);

                $damage_place->load([
                    'resource' => fn ($qr) => $qr->select('id', 'name', 'code_number', 'phone_number'),
                ]);

                NotificationService::record(
                    $user,
                    module: 'آسیب پذیره ساحې',
                    action: 'updated',
                    title: 'ځای اصلاح شوو',
                    message: 'تهدید: ' . ($damage_place->tahdid_type ?? '') . ' | ولایت: ' . ($damage_place->province ?? ''),
                    routeName: 'damage-places', // ⚠️ set your real route name
                    routeParams: ['id' => $damage_place->id],
                    level: 'info',
                    changes: !empty($changes) ? $changes : null
                );

                return DamagePlacesResource::make($damage_place);
            });
        } catch (QueryException $e) {
            if ($this->isDuplicateKeyException($e)) {
                return response()->json($this->duplicateErrorPashto(), 422);
            }
            throw $e;
        }
    }

    /** DELETE /damage-places/{damage_place} */
    public function destroy(Request $request, DamagePlaces $damage_place)
    {
        $user = $request->user(); // 🔔

        return DB::transaction(function () use ($damage_place, $user) {
            $tahdid = $damage_place->tahdid_type;

            // ✅ delete picture file (same style as SaeenFasad destroy)
            $this->deletePictureIfStored($damage_place->picture);

            $damage_place->delete();

            NotificationService::record(
                $user,
                module: 'آسیب پذیره ساحې',
                action: 'deleted',
                title: 'ځای حذف شوو',
                message: 'تهدید: ' . ($tahdid ?? ''),
                routeName: null,
                routeParams: ['damage-places'],
                level: 'warning',
                changes: null
            );

            return response()->noContent();
        });
    }

    /** POST /damage-places/bulk-delete  { "ids": [1,2,3] } */
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
            $rows = DamagePlaces::whereIn('id', $ids)->get(['id', 'picture']);
            foreach ($rows as $row) {
                $this->deletePictureIfStored($row->picture);
            }

            DamagePlaces::whereIn('id', $ids)->delete();

            NotificationService::record(
                $user,
                module: 'آسیب پذیره ساحې',
                action: 'deleted',
                title: 'محلات به‌صورت گروهی حذف شد',
                message: 'تعداد: ' . count($ids),
                routeName: null,
                routeParams: ['damage-places'],
                level: 'warning',
                changes: null
            );

            return response()->noContent();
        });
    }
}