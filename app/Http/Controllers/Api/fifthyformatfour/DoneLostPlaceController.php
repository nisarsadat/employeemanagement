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
    private function printSearchFields(): array
{
    return [
        'picture',

        'place_name',

        'lost_person_name',
        'lost_person_f_name',
        'lost_person_phone',
        'lost_person_job',

        'main_province',
        'main_district',
        'main_village',

        'now_province',
        'now_district',
        'now_village',

        'province',
        'zone',
        'district',
        'village',
        'specific_place',
        'coordinates',

        'lost_quantity',
        'Province_addr',

        'resource_id',
        'resource_code',
        'resource_phone',

        'note',
    ];
}

private function applyPrintFilters($query, Request $request)
{
    $search           = trim((string) $request->query('search', ''));
    $searchField      = trim((string) $request->query('search_field', ''));
    $searchValue      = trim((string) $request->query('search_value', ''));
    $cardFilter       = trim((string) $request->query('card_filter', 'all'));
    $fiftySixFormatId = $request->query('fifty_six_format_id');

    $allowedFields = $this->printSearchFields();

    if ($search !== '' && $searchField === '') {
        $query->where(function (Builder $q) use ($search) {
            $q->where('place_name', 'like', "%{$search}%")
                ->orWhere('lost_person_name', 'like', "%{$search}%")
                ->orWhere('lost_person_f_name', 'like', "%{$search}%")
                ->orWhere('lost_person_phone', 'like', "%{$search}%")
                ->orWhere('lost_person_job', 'like', "%{$search}%")
                ->orWhere('main_province', 'like', "%{$search}%")
                ->orWhere('main_district', 'like', "%{$search}%")
                ->orWhere('main_village', 'like', "%{$search}%")
                ->orWhere('now_province', 'like', "%{$search}%")
                ->orWhere('now_district', 'like', "%{$search}%")
                ->orWhere('now_village', 'like', "%{$search}%")
                ->orWhere('province', 'like', "%{$search}%")
                ->orWhere('zone', 'like', "%{$search}%")
                ->orWhere('district', 'like', "%{$search}%")
                ->orWhere('village', 'like', "%{$search}%")
                ->orWhere('specific_place', 'like', "%{$search}%")
                ->orWhere('coordinates', 'like', "%{$search}%")
                ->orWhere('lost_quantity', 'like', "%{$search}%")
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

    /**
     * If your done_lost_places table has a real fifty_six_format_id column,
     * uncomment this block.
     */
    // if (is_numeric($fiftySixFormatId) && (int) $fiftySixFormatId > 0) {
    //     $query->where('fifty_six_format_id', (int) $fiftySixFormatId);
    // }

    if ($cardFilter === 'withCoordinates') {
        $query->whereNotNull('coordinates')
            ->whereRaw("TRIM(coordinates) != ''");
    } elseif ($cardFilter === 'withPerson') {
        $query->whereNotNull('lost_person_name')
            ->whereRaw("TRIM(lost_person_name) != ''");
    } elseif ($cardFilter === 'withResource') {
        $query->whereNotNull('resource_id');
    }

    return $query;
}

public function printView(Request $request)
{
    $query = Donelostplace::query()
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

            $row->resource_phone =
                $row->resource?->phone_number
                ?? '-';

            return $row;
        });

    $selectedColumns = $request->input('selected_columns', []);
    $columnLabels    = $request->input('column_labels', []);
    $printLabels     = $request->input('print_labels', []);
    $locale          = $request->input('locale', 'fa');
    $title           = $request->input('title', 'چاپ محلات گمشده/یافته شده');

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
    $doneLostPlace = DoneLostPlace::findOrFail($id);

    return $this->removePictureFileAndClear($doneLostPlace);
}
}