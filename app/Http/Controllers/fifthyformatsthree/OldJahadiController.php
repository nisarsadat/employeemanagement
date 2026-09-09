<?php

namespace App\Http\Controllers\fifthyformatsthree;

use App\Http\Controllers\Controller;
use App\Models\fifthyformatsthree\OldJahadi;
use App\Http\Resources\fifthyformatsthree\OldJahadiResource;
use App\Http\Requests\fifthyformatsthree\StoreOldJahadiRequest;
use App\Http\Requests\fifthyformatsthree\UpdateOldJahadiRequest;
use App\Services\NotificationService; // 🔔 notifications
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class OldJahadiController extends Controller
{
    private function isDuplicateKey(QueryException $e): bool
    {
        return (int) ($e->errorInfo[1] ?? 0) === 1062;
    }

    private function duplicateResponse()
    {
        return response()->json([
            'message' => 'دا ریکارډ مخکې ثبت شوی دی.',
            'errors'  => [
                'duplicate' => [
                    'د همدې معلوماتو سره (نوم، د پلار نوم او اصلي ادرس) ریکارډ مخکې له مخکې موجود دی.',
                ],
            ],
        ], 422);
    }

    // ✅ picture upload helper (same idea as other modules)
    private function storePicture(?\Illuminate\Http\UploadedFile $file): ?string
    {
        if (!$file) return null;

        $ext = strtolower($file->getClientOriginalExtension() ?: 'jpg');
        $name = 'oldjahadi_' . now()->format('Ymd_His') . '_' . Str::random(10) . '.' . $ext;

        // public disk => storage/app/public/oldjahadis/...
        return $file->storeAs('oldjahadis', $name, 'public');
    }

    private function deletePictureIfExists(?string $path): void
    {
        if (!$path) return;

        $p = $path;
        if (str_starts_with($p, 'storage/')) {
            $p = substr($p, strlen('storage/'));
        }

        if (Storage::disk('public')->exists($p)) {
            Storage::disk('public')->delete($p);
        }
    }

    public function index(Request $request)
    {
        $perPage = (int) $request->input('perPage', 10);
        $search  = trim((string) $request->input('search', ''));
        $searchField = trim((string) $request->input('search_field', ''));
        $searchValue = trim((string) $request->input('search_value', ''));

        $allowedSearchFields = [
           
         'picture',
        'nick_name',
        'name',
        'father_name',

        'main_province',
        'main_district',
        'main_village',

        'now_province',
        'now_zone',
        'now_district',
        'now_village',
        'now_specific_place',
        'now_street_number',
        'now_home_number',

        'old_job',
        'Province_addr',
        'now_job',
        'employee_quantity',
        'supplier',
        'phone_number',
        'tanzim',
        'jorm_history',
        'note',
        'organ',
        'coordinates',
        'resource_id',
        ];

        $q = OldJahadi::query()
            ->with(['resource:id,name,code_number,phone_number'])
            ->latest();

        if ($search !== '' && $searchField === '') {
            $q->where(function ($qq) use ($search) {
                $qq->where('name', 'like', "%{$search}%")
                   ->orWhere('father_name', 'like', "%{$search}%")
                   ->orWhere('now_province', 'like', "%{$search}%")
                   ->orWhere('tanzim', 'like', "%{$search}%");
            });
        }

        if ($searchField !== '' && in_array($searchField, $allowedSearchFields, true)) {
            $value = $searchValue !== '' ? $searchValue : $search;

            if ($value !== '') {
                $q->where($searchField, 'like', "%{$value}%");
            }
        }

        return OldJahadiResource::collection($q->paginate($perPage));
    }

    public function filterOptions(Request $request)
    {
        $field = trim((string) $request->query('field', ''));
        $q = trim((string) $request->query('q', ''));

        $allowedFields = [
           
         'picture',
        'nick_name',
        'name',
        'father_name',

        'main_province',
        'main_district',
        'main_village',

        'now_province',
        'now_zone',
        'now_district',
        'now_village',
        'now_specific_place',
        'now_street_number',
        'now_home_number',

        'old_job',
        'Province_addr',
        'now_job',
        'employee_quantity',
        'supplier',
        'phone_number',
        'tanzim',
        'jorm_history',
        'note',
        'organ',
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

        $query = OldJahadi::query()
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

    public function store(StoreOldJahadiRequest $request)
    {
        $user = $request->user(); // 🔔

        try {
            $data = $request->validated();

            // ✅ picture
            if ($request->hasFile('picture')) {
                $data['picture'] = $this->storePicture($request->file('picture'));
            }

            $item = OldJahadi::create($data)
                ->load(['resource:id,name,code_number,phone_number']);

            // 🔔 Notification: CREATED
            NotificationService::record(
                $user,
                module: 'پخواني جهادی',
                action: 'created',
                title: 'نوی پخواني جهادی ثبت شو',
                message: 'نوم: ' . ($item->name ?? ''),
                routeName: 'old-jahadis',
                routeParams: ['id' => $item->id],
                level: 'success',
                changes: null
            );

            return OldJahadiResource::make($item);
        } catch (QueryException $e) {
            if ($this->isDuplicateKey($e)) {
                return $this->duplicateResponse();
            }
            throw $e;
        }
    }

    public function show(OldJahadi $oldJahadi)
    {
        $oldJahadi->load(['resource:id,name,code_number,phone_number']);
        return OldJahadiResource::make($oldJahadi);
    }

    public function update(UpdateOldJahadiRequest $request, OldJahadi $oldJahadi)
    {
        $user = $request->user(); // 🔔

        try {
            $original  = $oldJahadi->getOriginal();
            $validated = $request->validated();
            $changes   = [];

            foreach ($validated as $field => $newValue) {
                if (array_key_exists($field, $original)) {
                    $oldValue = $original[$field];
                    if ($oldValue != $newValue) {
                        $changes[$field] = ['old' => $oldValue, 'new' => $newValue];
                    }
                }
            }

            // ✅ picture replace
            if ($request->hasFile('picture')) {
                $this->deletePictureIfExists($oldJahadi->picture);
                $validated['picture'] = $this->storePicture($request->file('picture'));
                $changes['picture'] = ['old' => $oldJahadi->picture, 'new' => $validated['picture']];
            }

            $oldJahadi->update($validated);
            $oldJahadi->load(['resource:id,name,code_number,phone_number']);

            // 🔔 Notification: UPDATED
            NotificationService::record(
                $user,
                module: 'پخواني جهادی',
                action: 'updated',
                title: 'قدیم جهادی اصلاح شو',
                message: 'نوم: ' . ($oldJahadi->name ?? ''),
                routeName: 'old-jahadis',
                routeParams: ['id' => $oldJahadi->id],
                level: 'info',
                changes: !empty($changes) ? $changes : null
            );

            return OldJahadiResource::make($oldJahadi);
        } catch (QueryException $e) {
            if ($this->isDuplicateKey($e)) {
                return $this->duplicateResponse();
            }
            throw $e;
        }
    }

    public function destroy(Request $request, OldJahadi $oldJahadi)
    {
        $user = $request->user(); // 🔔

        $name = $oldJahadi->name;

        // ✅ delete picture file
        $this->deletePictureIfExists($oldJahadi->picture);

        $oldJahadi->delete();

        // 🔔 Notification: DELETED
        NotificationService::record(
            $user,
            module: 'پخواني جهادی',
            action: 'deleted',
            title: 'قدیم جهادی حذف شو',
            message: 'نوم: ' . ($name ?? ''),
            routeName: null,
            routeParams: ['old-jahadis'],
            level: 'warning',
            changes: null
        );

        return response()->noContent();
    }

    public function bulkDelete(Request $request)
    {
        $user = $request->user(); // 🔔

        $ids = (array) $request->input('ids', []);
        if (!empty($ids)) {
            $items = OldJahadi::query()->whereIn('id', $ids)->get(['id', 'picture']);
            foreach ($items as $it) {
                $this->deletePictureIfExists($it->picture);
            }
        }

        OldJahadi::destroy($ids);

        // 🔔 Notification: BULK DELETED
        NotificationService::record(
            $user,
            module: 'پخواني جهادی',
            action: 'bulk_deleted',
            title: 'ډله‌ییز حذف ترسره شو',
            message: 'حذف شوي ریکارډونه: ' . count($ids),
            routeName: null,
            routeParams: ['old-jahadis'],
            level: 'warning',
            changes: null
        );

        return response()->noContent();
    }
    private function printSearchFields(): array
{
    return [
        'picture',
        'nick_name',
        'name',
        'father_name',

        'main_province',
        'main_district',
        'main_village',

        'now_province',
        'now_zone',
        'now_district',
        'now_village',
        'now_specific_place',
        'now_street_number',
        'now_home_number',

        'old_job',
        'now_job',
        'employee_quantity',
        'supplier',
        'phone_number',
        'tanzim',
        'jorm_history',
        'note',
        'organ',
        'Province_addr',
        'coordinates',

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
    $fiftySixFormatId = $request->query('fifty_six_format_id');
    $cardFilter       = trim((string) $request->query('card_filter', 'all'));

    $allowedFields = $this->printSearchFields();

    if ($search !== '' && $searchField === '') {
        $query->where(function ($qq) use ($search) {
            $qq->where('nick_name', 'like', "%{$search}%")
                ->orWhere('name', 'like', "%{$search}%")
                ->orWhere('father_name', 'like', "%{$search}%")
                ->orWhere('main_province', 'like', "%{$search}%")
                ->orWhere('main_district', 'like', "%{$search}%")
                ->orWhere('main_village', 'like', "%{$search}%")
                ->orWhere('now_province', 'like', "%{$search}%")
                ->orWhere('now_zone', 'like', "%{$search}%")
                ->orWhere('now_district', 'like', "%{$search}%")
                ->orWhere('now_village', 'like', "%{$search}%")
                ->orWhere('now_specific_place', 'like', "%{$search}%")
                ->orWhere('now_street_number', 'like', "%{$search}%")
                ->orWhere('now_home_number', 'like', "%{$search}%")
                ->orWhere('old_job', 'like', "%{$search}%")
                ->orWhere('now_job', 'like', "%{$search}%")
                ->orWhere('organ', 'like', "%{$search}%")
                ->orWhere('employee_quantity', 'like', "%{$search}%")
                ->orWhere('supplier', 'like', "%{$search}%")
                ->orWhere('phone_number', 'like', "%{$search}%")
                ->orWhere('jorm_history', 'like', "%{$search}%")
                ->orWhere('tanzim', 'like', "%{$search}%")
                ->orWhere('Province_addr', 'like', "%{$search}%")
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

    if (is_numeric($fiftySixFormatId) && (int) $fiftySixFormatId > 0) {
        $query->where('id', (int) $fiftySixFormatId);
    }

    if ($cardFilter === 'withCoordinates') {
        $query->whereNotNull('coordinates')
            ->whereRaw("TRIM(coordinates) != ''");
    } elseif ($cardFilter === 'withEmployees') {
        $query->whereNotNull('employee_quantity')
            ->whereRaw("TRIM(employee_quantity) != ''");
    } elseif ($cardFilter === 'withSupplier') {
        $query->whereNotNull('supplier')
            ->whereRaw("TRIM(supplier) != ''");
    }

    return $query;
}

public function printView(Request $request)
{
    $query = OldJahadi::query()
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
                ?? $row->resource_phone
                ?? '-';

            return $row;
        });

    $selectedColumns = $request->input('selected_columns', []);
    $columnLabels    = $request->input('column_labels', []);
    $printLabels     = $request->input('print_labels', []);
    $locale          = $request->input('locale', 'fa');
    $title           = $request->input('title', 'چاپ جهادی‌های قدیم');

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
    $oldJahadi = OldJahadi::findOrFail($id);

    return $this->removePictureFileAndClear($oldJahadi);
}

public function exportExcel(Request $request)
{
    $search = trim((string) $request->get('search', ''));
    $searchField = $request->get('search_field');
    $searchValue = $request->get('search_value');
    $fileName = 'old_jahadis_' . now()->format('Y_m_d_H_i_s') . '.xlsx';
    return Excel::download(new \App\Exports\OldJahadiExport(['search' => $search, 'search_field' => $searchField, 'search_value' => $searchValue, 'resource_id' => $request->get('resource_id')]), $fileName);
}
}
