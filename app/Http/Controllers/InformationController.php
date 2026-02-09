<?php

namespace App\Http\Controllers;

use App\Models\Information;
use App\Models\InformationDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\QueryException;

use App\Http\Requests\StoreInformationRequest;
use App\Http\Requests\UpdateInformationRequest;
use App\Http\Resources\InformationResource;
use App\Services\NotificationService; // 🔔 notifications

class InformationController extends Controller
{
    // =========================
    // ✅ DATE FILTER HELPERS (same style as OfficeEmployee)
    // =========================
    private function resolveDateField(?string $dateField): string
    {
        // ✅ allow only known date fields (adjust if you have more)
        $allowed = ['information_saved_date', 'hamahangi_date', 'created_at'];

        $field = trim((string) $dateField);
        if ($field === '' || !in_array($field, $allowed, true)) {
            return 'information_saved_date';
        }

        return $field;
    }

    private function applyMonthYearFilter($query, ?int $year, ?int $month, string $dateField)
    {
        if ($year) {
            $query->whereYear($dateField, $year);
        }

        if ($month) {
            $query->whereMonth($dateField, $month);
        }

        return $query;
    }

    // ✅ keep the main list query consistent everywhere
    private function baseListQuery()
    {
        return Information::query()->with([
            'resource',
            'officeEmployee',
            'officeEmployeeInfo',
            'documents',
            'account:id,name,price',
            'finances',
        ]);
    }

    /**
     * ✅ NEW: Return available years/months based on REAL DB data.
     *
     * Query params:
     * - date_field (information_saved_date|hamahangi_date|created_at) default information_saved_date
     * - year (optional) if provided, returns available months for that year too
     *
     * Response:
     * {
     *   "date_field": "information_saved_date",
     *   "years": [2026,2025,2024],
     *   "months": [1,2,3] // only when year is given
     * }
     */
    public function dateOptions(Request $request)
    {
        $request->validate([
            'date_field' => 'nullable|string',
            'year'       => 'nullable|integer|min:1900|max:2500',
        ]);

        $dateField = $this->resolveDateField($request->input('date_field'));
        $year      = $request->filled('year') ? (int) $request->input('year') : null;

        $base = Information::query()->whereNotNull($dateField);

        $years = (clone $base)
            ->selectRaw("YEAR($dateField) as y")
            ->distinct()
            ->orderByDesc('y')
            ->pluck('y')
            ->filter()
            ->values()
            ->all();

        $months = [];
        if ($year) {
            $months = (clone $base)
                ->whereYear($dateField, $year)
                ->selectRaw("MONTH($dateField) as m")
                ->distinct()
                ->orderBy('m')
                ->pluck('m')
                ->filter()
                ->values()
                ->all();
        }

        return response()->json([
            'date_field' => $dateField,
            'years'      => $years,
            'months'     => $months,
        ]);
    }

    /**
     * List all informations with search + month/year filters.
     */
    public function index(Request $request)
    {
        $request->validate([
            'page'       => 'sometimes|integer|min:1',
            'perPage'    => 'sometimes',
            'search'     => 'nullable|string',

            // ✅ month/year filters
            'year'       => 'nullable|integer|min:1900|max:2500',
            'month'      => 'nullable|integer|min:1|max:12',
            'date_field' => 'nullable|string',

            // ✅ optional: day or range filters (kept from your controller)
            'date'       => 'nullable|string',
            'date_from'  => 'nullable|string',
            'date_to'    => 'nullable|string',
            'from_date'  => 'nullable|string',
            'to_date'    => 'nullable|string',
        ]);

        $search     = trim((string) $request->input('search', ''));
        $rawPerPage = $request->input('perPage', null);
        $page       = (int) $request->input('page', 1);

        $year      = $request->filled('year') ? (int) $request->input('year') : null;
        $month     = $request->filled('month') ? (int) $request->input('month') : null;
        $dateField = $this->resolveDateField($request->input('date_field'));

        // ✅ keep old date-range behavior but now use dateField (not only created_at)
        $date     = trim((string) $request->input('date', ''));
        $dateFrom = trim((string) $request->input('date_from', $request->input('from_date', '')));
        $dateTo   = trim((string) $request->input('date_to', $request->input('to_date', '')));

        $query = $this->baseListQuery()
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($qq) use ($search) {
                    $qq->where('name', 'like', "%{$search}%")
                       ->orWhere('father_name', 'like', "%{$search}%")
                       ->orWhere('nick_name', 'like', "%{$search}%");
                });
            });

        // ✅ month/year filter (same behavior as office employee)
        $this->applyMonthYearFilter($query, $year, $month, $dateField);

        // ✅ day or range filters (optional) — on same dateField
        $query
            ->when($date !== '', function ($q) use ($date, $dateField) {
                $q->whereDate($dateField, $date);
            })
            ->when($dateFrom !== '' && $dateTo !== '', function ($q) use ($dateFrom, $dateTo, $dateField) {
                $q->whereDate($dateField, '>=', $dateFrom)
                  ->whereDate($dateField, '<=', $dateTo);
            })
            ->when($dateFrom !== '' && $dateTo === '' && $date === '', function ($q) use ($dateFrom, $dateField) {
                $q->whereDate($dateField, '>=', $dateFrom);
            })
            ->when($dateTo !== '' && $dateFrom === '' && $date === '', function ($q) use ($dateTo, $dateField) {
                $q->whereDate($dateField, '<=', $dateTo);
            });

        $query->orderByDesc('id');

        if (is_null($rawPerPage) || $rawPerPage === '' || strtolower((string) $rawPerPage) === 'all' || (int) $rawPerPage === 0) {
            return InformationResource::collection($query->get());
        }

        return InformationResource::collection(
            $query->paginate(max(1, (int) $rawPerPage), ['*'], 'page', $page)
        );
    }

    public function show($id)
    {
        $information = $this->baseListQuery()->findOrFail($id);
        return new InformationResource($information);
    }

    public function store(StoreInformationRequest $request)
    {
        $user = $request->user(); // 🔔 who did this

        DB::beginTransaction();

        try {
            $info = Information::create($request->validated());

            // 📎 documents
            if ($request->hasFile('documents')) {
                foreach ($request->file('documents') as $file) {
                    $path = $file->store("information_docs/{$info->id}", 'public');
                    InformationDocument::create([
                        'information_id' => $info->id,
                        'file_name'      => $file->getClientOriginalName(),
                        'file_path'      => $path,
                        'mime_type'      => $file->getMimeType(),
                        'size_bytes'     => $file->getSize(),
                    ]);
                }
            }

            $info->load([
                'resource',
                'officeEmployee',
                'officeEmployeeInfo',
                'documents',
                'finances',
            ]);

            NotificationService::record(
                $user,
                module: 'اطلاع',
                action: 'created',
                title: 'اطلاع جدید ثبت شد',
                message: 'نام اطلاع: ' . ($info->name ?? ''),
                routeName: 'informations',
                routeParams: ['id' => $info->id],
                level: 'success',
                changes: null
            );

            DB::commit();

            return new InformationResource($info);

        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Information creation failed',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function update(UpdateInformationRequest $request, $id)
    {
        $user = $request->user(); // 🔔

        DB::beginTransaction();

        try {
            $information = Information::with([
                'resource',
                'officeEmployee',
                'officeEmployeeInfo',
                'documents',
                'finances',
            ])->findOrFail($id);

            // 🧮 build changes BEFORE update
            $original  = $information->getOriginal();
            $validated = $request->validated();

            $information->fill($validated);
            $dirty   = $information->getDirty();
            $changes = [];

            foreach ($dirty as $field => $newValue) {
                $changes[$field] = [
                    'old' => $original[$field] ?? null,
                    'new' => $newValue,
                ];
            }

            $information->save();

            // 🔔 Reset missing-result notification tracking when result is provided
            if (
                array_key_exists('result', $validated) &&
                !is_null($validated['result']) &&
                trim((string) $validated['result']) !== ''
            ) {
                $information->forceFill([
                    'result_missing_first_notified_at' => null,
                    'result_missing_last_notified_at'  => null,
                    'result_missing_notify_count'      => 0,
                ])->save();
            }

            // 📎 new documents
            if ($request->hasFile('documents')) {
                foreach ($request->file('documents') as $file) {
                    $path = $file->store("information_docs/{$information->id}", 'public');
                    InformationDocument::create([
                        'information_id' => $information->id,
                        'file_name'      => $file->getClientOriginalName(),
                        'file_path'      => $path,
                        'mime_type'      => $file->getMimeType(),
                        'size_bytes'     => $file->getSize(),
                    ]);
                }

                $changes['documents'] = [
                    'old' => null,
                    'new' => 'updated',
                ];
            }

            $information->load([
                'resource',
                'officeEmployee',
                'officeEmployeeInfo',
                'documents',
                'finances',
            ]);

            NotificationService::record(
                $user,
                module: 'اطلاع',
                action: 'updated',
                title: 'اطلاع اصلاح شد',
                message: 'نام اطلاع: ' . ($information->name ?? ''),
                routeName: 'informations',
                routeParams: ['id' => $information->id],
                level: 'info',
                changes: $changes ?: null
            );

            DB::commit();

            return new InformationResource($information);

        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Information update failed',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(Request $request, $id)
    {
        $user = $request->user(); // 🔔

        DB::beginTransaction();

        try {
            $information = Information::with('documents')->findOrFail($id);
            $name = $information->name;

            // 🗑 delete files + db rows
            foreach ($information->documents as $doc) {
                Storage::disk('public')->delete($doc->file_path);
            }
            $information->documents()->delete();

            $information->delete();

            NotificationService::record(
                $user,
                module: 'اطلاع',
                action: 'deleted',
                title: 'اطلاع حذف شد',
                message: 'نام اطلاع: ' . ($name ?? ''),
                routeName: null,
                routeParams: ['informations'],
                level: 'warning',
                changes: null
            );

            DB::commit();

            return response()->json(['message' => 'Deleted']);

        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Information delete failed',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}
