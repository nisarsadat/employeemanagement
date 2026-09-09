<?php

namespace App\Http\Controllers\fifthyformatsthree;

use App\Http\Controllers\Controller;
use App\Models\fifthyformatsthree\Hotel;
use App\Http\Resources\fifthyformatsthree\HotelResource;
use App\Http\Requests\fifthyformatsthree\StoreHotelRequest;
use App\Http\Requests\fifthyformatsthree\UpdateHotelRequest;
use App\Services\NotificationService; // 🔔 notifications
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\HotelExport;
class HotelController extends Controller
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
                    'د همدې معلوماتو سره (رسټورانټونه او هوټلونه نوم، ډول او ادرس) ریکارډ مخکې له مخکې موجود دی.',
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
            
        'hotel_name',
        'type',

        'responsible_name',
        'responsible_f_name',
        'responsible_position',
        'responsible_phone',

        'province',
        'zone',
        'Province_addr',
        'district',
        'village',
        'specific_place',
        'cordenate',

        'employee_quantity',
        'note',

        'resource_id',
        ];

        $q = Hotel::query()
            ->with(['resource:id,name,code_number,phone_number'])
            ->latest();

        if ($search !== '' && $searchField === '') {
            $q->where(function ($qq) use ($search) {
                $qq->where('hotel_name', 'like', "%{$search}%")
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

        return HotelResource::collection($q->paginate($perPage));
    }

    public function filterOptions(Request $request)
    {
        $field = trim((string) $request->query('field', ''));
        $q = trim((string) $request->query('q', ''));

        $allowedFields = [
            
        'hotel_name',
        'type',

        'responsible_name',
        'responsible_f_name',
        'responsible_position',
        'responsible_phone',

        'province',
        'zone',
        'Province_addr',
        'district',
        'village',
        'specific_place',
        'cordenate',

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

        $query = Hotel::query()
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

    public function store(StoreHotelRequest $request)
    {
        $user = $request->user(); // 🔔 who did this
        $data = $request->validated();

        try {
            // ✅ picture: file OR string (same style as SaeenFasad)
            if ($request->hasFile('picture')) {
                $path = $request->file('picture')->store('uploads/hotels', 'public');
                $data['picture'] = "storage/{$path}";
            } else {
                if (array_key_exists('picture', $data) && !is_string($data['picture'])) {
                    unset($data['picture']);
                }
            }

            $hotel = Hotel::create($data)
                ->load(['resource:id,name,code_number,phone_number']);

            // 🔔 Notification: CREATED
            NotificationService::record(
                $user,
                module: 'رسټورانټونه او هوټلونه',
                action: 'created',
                title: 'نوی رسټورانټونه او هوټلونه ثبت شو',
                message: 'نوم: ' . ($hotel->hotel_name ?? ''),
                routeName: 'hotels',               // ⚠️ set to your real route name
                routeParams: ['id' => $hotel->id],
                level: 'success',
                changes: null
            );

            return HotelResource::make($hotel);
        } catch (QueryException $e) {
            if ($this->isDuplicateKey($e)) {
                return $this->duplicateResponse();
            }
            throw $e;
        }
    }

    public function show(Hotel $hotel)
    {
        $hotel->load(['resource:id,name,code_number,phone_number']);
        return HotelResource::make($hotel);
    }

    public function update(UpdateHotelRequest $request, Hotel $hotel)
    {
        $user = $request->user(); // 🔔
        $data = $request->validated();

        try {
            // 🧮 build changes BEFORE update
            $original = $hotel->getOriginal();
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
                if ($hotel->picture && str_starts_with($hotel->picture, 'storage/')) {
                    $old = str_replace('storage/', '', $hotel->picture);
                    Storage::disk('public')->delete($old);
                }

                $path = $request->file('picture')->store('uploads/hotels', 'public');
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

            $hotel->update($data);
            $hotel->load(['resource:id,name,code_number,phone_number']);

            // 🔔 Notification: UPDATED
            NotificationService::record(
                $user,
                module: 'رسټورانټونه او هوټلونه',
                action: 'updated',
                title: 'رسټورانټونه او هوټلونه اصلاح شو',
                message: 'نوم: ' . ($hotel->hotel_name ?? ''),
                routeName: 'hotels',               // ⚠️ set to your real route name
                routeParams: ['id' => $hotel->id],
                level: 'info',
                changes: !empty($changes) ? $changes : null
            );

            return HotelResource::make($hotel);
        } catch (QueryException $e) {
            if ($this->isDuplicateKey($e)) {
                return $this->duplicateResponse();
            }
            throw $e;
        }
    }

    public function destroy(Request $request, Hotel $hotel)
    {
        $user = $request->user(); // 🔔
        $name = $hotel->hotel_name;

        // ✅ delete picture file (same style as SaeenFasad)
        if ($hotel->picture && str_starts_with($hotel->picture, 'storage/')) {
            $old = str_replace('storage/', '', $hotel->picture);
            Storage::disk('public')->delete($old);
        }

        $hotel->delete();

        // 🔔 Notification: DELETED
        NotificationService::record(
            $user,
            module: 'رسټورانټونه او هوټلونه',
            action: 'deleted',
            title: 'رسټورانټونه او هوټلونه حذف شو',
            message: 'نوم: ' . ($name ?? ''),
            routeName: null,
            routeParams: ['hotels'],
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
        Hotel::destroy($ids);

        // 🔔 Notification: BULK DELETED
        NotificationService::record(
            $user,
            module: 'رسټورانټونه او هوټلونه',
            action: 'bulk_deleted',
            title: 'ډله‌ییز حذف ترسره شو',
            message: 'حذف شوي ریکارډونه: ' . count($ids),
            routeName: null,
            routeParams: ['hotels'],
            level: 'warning',
            changes: null
        );

        return response()->noContent();
    }
    private function printSearchFields(): array
{
    return [
        'hotel_name',
        'type',

        'responsible_name',
        'responsible_f_name',
        'responsible_position',
        'responsible_phone',

        'province',
        'zone',
        'Province_addr',
        'district',
        'village',
        'specific_place',
        'cordenate',

        'employee_quantity',
        'note',

        'resource_id',
        'resource_code',
        'resource_phone',
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
        $query->where(function ($qq) use ($search) {
            $qq->where('hotel_name', 'like', "%{$search}%")
                ->orWhere('type', 'like', "%{$search}%")
                ->orWhere('responsible_name', 'like', "%{$search}%")
                ->orWhere('responsible_f_name', 'like', "%{$search}%")
                ->orWhere('responsible_position', 'like', "%{$search}%")
                ->orWhere('responsible_phone', 'like', "%{$search}%")
                ->orWhere('province', 'like', "%{$search}%")
                ->orWhere('zone', 'like', "%{$search}%")
                ->orWhere('Province_addr', 'like', "%{$search}%")
                ->orWhere('district', 'like', "%{$search}%")
                ->orWhere('village', 'like', "%{$search}%")
                ->orWhere('specific_place', 'like', "%{$search}%")
                ->orWhere('cordenate', 'like', "%{$search}%")
                ->orWhere('employee_quantity', 'like', "%{$search}%")
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

    if (
        is_numeric($fiftySixFormatId)
        && (int) $fiftySixFormatId > 0
        && Schema::hasColumn('hotels', 'fifty_six_format_id')
    ) {
        $query->where('fifty_six_format_id', (int) $fiftySixFormatId);
    }

    if ($cardFilter === 'withCoordinates') {
        $query->whereNotNull('cordenate')
            ->whereRaw("TRIM(cordenate) != ''");
    } elseif ($cardFilter === 'withRespPhone') {
        $query->whereNotNull('responsible_phone')
            ->whereRaw("TRIM(responsible_phone) != ''");
    } elseif ($cardFilter === 'withEmployees') {
        $query->whereNotNull('employee_quantity')
            ->whereRaw("TRIM(employee_quantity) != ''");
    }

    return $query;
}

public function printView(Request $request)
{
    $query = Hotel::query()
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
                ?? '-';

            return $row;
        });

    $selectedColumns = $request->input('selected_columns', []);
    $columnLabels    = $request->input('column_labels', []);
    $printLabels     = $request->input('print_labels', []);
    $locale          = $request->input('locale', 'fa');
    $title           = $request->input('title', 'چاپ هوتل‌ها');

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
    $hotel = Hotel::findOrFail($id);

    return $this->removePictureFileAndClear($hotel);
}

public function exportExcel(Request $request)
{
    $search = trim((string) $request->get('search', ''));
    $searchField = $request->get('search_field');
    $searchValue = $request->get('search_value');
    $cardFilter = trim((string) $request->get('card_filter', 'all'));

    $fileName = 'hotels_' . now()->format('Y_m_d_H_i_s') . '.xlsx';

    return Excel::download(
        new HotelExport([
            'search'       => $search,
            'search_field' => $searchField,
            'search_value' => $searchValue,
            'card_filter'  => $cardFilter,
            'resource_id'  => $request->get('resource_id'),
        ]),
        $fileName
    );
}
}