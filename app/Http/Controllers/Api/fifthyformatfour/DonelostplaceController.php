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

class DonelostPlaceController extends Controller
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
     * Supports: ?search=...&perPage=...&page=...
     */
    public function index(Request $request)
    {
        $query = Donelostplace::query()
            ->with(['resource:id,name,code_number,phone_number']);

        if ($s = trim((string) $request->query('search', ''))) {
            $query->where(function (Builder $q) use ($s) {
                $q->where('place_name', 'like', "%{$s}%")
                    ->orWhere('lost_person_name', 'like', "%{$s}%")
                    ->orWhere('lost_person_phone', 'like', "%{$s}%")
                    ->orWhere('village', 'like', "%{$s}%");
            });
        }

        $perPage = $request->query('perPage');
        $page    = $request->query('page');

        $perPageInt = filter_var($perPage, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $pageInt    = filter_var($page, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

        // ✅ Same pattern: if perPage / page invalid or missing → return ALL rows, no pagination
        if ($perPageInt === false || $pageInt === false) {
            $rows = $query->orderByDesc('id')->get();
            return DonelostplaceResource::collection($rows);
        }

        $rows = $query
            ->orderByDesc('id')
            ->paginate($perPageInt, ['*'], 'page', $pageInt);

        return DonelostplaceResource::collection($rows);
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
