<?php

namespace App\Http\Controllers\Api\fifthyformatfour;

use App\Http\Controllers\Controller;
use App\Http\Requests\fifthyformatfour\StoreDonelostplaceRequest;
use App\Http\Requests\fifthyformatfour\UpdateDonelostplaceRequest;
use App\Http\Resources\fifthyformatfour\DonelostplaceResource;
use App\Models\fifthyformatfour\Donelostplace;
use App\Services\NotificationService; // 🔔 notifications
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DoneLostPlaceController extends Controller
{
    private function isDuplicateKey(QueryException $e): bool
    {
        // MySQL duplicate key code = 1062
        return (int) ($e->errorInfo[1] ?? 0) === 1062;
    }

    private function duplicateResponse()
    {
        return response()->json([
            'message' => 'این ثبت قبلاً موجود است.',
            'errors' => [
                'duplicate' => [
                    'مکان/شخص گمشده با همین مشخصات (نام مکان، مشخصات شخص گمشده و آدرس اصلی) قبلاً در سیستم ثبت شده است.',
                ],
            ],
        ], 422);
    }

    /**
     * GET /done-lost-places
     * Supports: ?search=...&search_field=...&search_value=...&perPage=...&page=...
     */
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

                'place_name',
        'lost_person_name','lost_person_f_name','lost_person_phone','lost_person_job',
        'main_province','main_district','main_village',
        'now_province','now_district','now_village',
        'picture',
        'province','zone','district','village','specific_place','coordinates',
        'lost_quantity',
        'Province_addr',
        'resource_id','note',
        ];

        $query = Donelostplace::query()
            ->with(['resource:id,name,code_number,phone_number'])
            ->when($search !== '' && $searchField === '', function (Builder $sub) use ($search) {
                $sub->where(function (Builder $q) use ($search) {
                    $q->where('place_name', 'like', "%{$search}%")
                        ->orWhere('lost_person_name', 'like', "%{$search}%")
                        ->orWhere('lost_person_phone', 'like', "%{$search}%")
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
            return DonelostplaceResource::collection($rows);
        }

        return DonelostplaceResource::collection($query->get());
    }

    /**
     * GET /done-lost-places/filter-options
     */
    public function filterOptions(Request $request)
    {
        $field = trim((string) $request->query('field', ''));
        $q = trim((string) $request->query('q', ''));

        $allowedFields = [
            'place_name',
            'lost_person_name',
            'father_name',
            'lost_person_phone',
            'province',
            'zone',
            'district',
            'village',
            'specific_place',
            'street',
            'house',
            'details',
            'note',
            'coordinates',
        ];

        if ($field === '' || !in_array($field, $allowedFields, true)) {
            return response()->json([
                'data' => [
                    'options' => [],
                ],
            ]);
        }

        $query = Donelostplace::query()
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
     * POST /done-lost-places
     * Accepts:
     * - picture as FILE or STRING
     */
    public function store(StoreDonelostplaceRequest $request)
    {
        $user = $request->user(); // 🔔
        $data = $request->validated();

        try {
            return DB::transaction(function () use ($request, $data, $user) {

                // ✅ picture: FILE OR STRING (same style as SaeenFasadController)
                if ($request->hasFile('picture')) {
                    $path = $request->file('picture')->store('uploads/done-lost-places', 'public');
                    $data['picture'] = "storage/{$path}";
                } else {
                    if (array_key_exists('picture', $data) && !is_string($data['picture'])) {
                        unset($data['picture']);
                    }
                }

                $row = Donelostplace::create($data);
                $row->load(['resource:id,name,code_number,phone_number']);

                // 🔔 Notification: CREATED
                NotificationService::record(
                    $user,
                    module: 'هغه ساحې چې غلا پکې ترسره شوي دي ',
                    action: 'created',
                    title: 'هغه ساحې چې غلا پکې ترسره شوي دي ثبت شوو',
                    message: 'نوم: ' . ($row->place_name ?? ''),
                    routeName: 'done-lost-places',      // ✅ set your real route name
                    routeParams: ['id' => $row->id],
                    level: 'success',
                    changes: null
                );

                return (new DonelostplaceResource($row))
                    ->additional(['message' => 'Created'])
                    ->response()
                    ->setStatusCode(201);
            });
        } catch (QueryException $e) {
            if ($this->isDuplicateKey($e)) return $this->duplicateResponse();
            throw $e;
        }
    }

    /**
     * GET /done-lost-places/{done_lost_place}
     */
    public function show(Donelostplace $done_lost_place)
    {
        $done_lost_place->load(['resource:id,name,code_number,phone_number']);
        return new DonelostplaceResource($done_lost_place);
    }

    /**
     * PUT/PATCH /done-lost-places/{done_lost_place}
     * Accepts:
     * - picture as FILE or STRING (replaces old file if new uploaded)
     */
    public function update(UpdateDonelostplaceRequest $request, Donelostplace $done_lost_place)
    {
        $user = $request->user(); // 🔔
        $data = $request->validated();

        try {
            return DB::transaction(function () use ($request, $done_lost_place, $data, $user) {
                // 🧮 changes BEFORE update
                $original = $done_lost_place->getOriginal();
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

                // ✅ picture: replace file if uploaded (same style as SaeenFasadController)
                if ($request->hasFile('picture')) {
                    if ($done_lost_place->picture && str_starts_with($done_lost_place->picture, 'storage/')) {
                        $old = str_replace('storage/', '', $done_lost_place->picture);
                        Storage::disk('public')->delete($old);
                    }

                    $path = $request->file('picture')->store('uploads/done-lost-places', 'public');
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

                $done_lost_place->update($data);
                $done_lost_place->load(['resource:id,name,code_number,phone_number']);

                // 🔔 Notification: UPDATED
                NotificationService::record(
                    $user,
                    module: 'هغه ساحې چې غلا پکې ترسره شوي دي ',
                    action: 'updated',
                    title: ' هغه ساحې چې غلا پکې ترسره شوي دي  اصلاح شوو',
                    message: 'نوم : ' . ($done_lost_place->place_name ?? ''),
                    routeName: 'done-lost-places',      // ✅ set your real route name
                    routeParams: ['id' => $done_lost_place->id],
                    level: 'info',
                    changes: !empty($changes) ? $changes : null
                );

                return new DonelostplaceResource($done_lost_place);
            });
        } catch (QueryException $e) {
            if ($this->isDuplicateKey($e)) return $this->duplicateResponse();
            throw $e;
        }
    }

    /**
     * DELETE /done-lost-places/{done_lost_place}
     */
    public function destroy(Request $request, Donelostplace $done_lost_place)
    {
        $user = $request->user(); // 🔔

        return DB::transaction(function () use ($done_lost_place, $user) {
            $place = $done_lost_place->place_name;

            // ✅ delete picture file (same style as SaeenFasadController)
            if ($done_lost_place->picture && str_starts_with($done_lost_place->picture, 'storage/')) {
                $old = str_replace('storage/', '', $done_lost_place->picture);
                Storage::disk('public')->delete($old);
            }

            $done_lost_place->delete();

            // 🔔 Notification: DELETED
            NotificationService::record(
                $user,
                module: 'هغه ساحې چې غلا پکې ترسره شوي دي ',
                action: 'deleted',
                title: ' هغه ساحې چې غلا پکې ترسره شوي دي  حذف شوو',
                message: 'نوم: ' . ($place ?? ''),
                routeName: null,
                routeParams: ['done-lost-places'],
                level: 'warning',
                changes: null
            );

            return response()->json(['message' => 'Deleted']);
        });
    }
}