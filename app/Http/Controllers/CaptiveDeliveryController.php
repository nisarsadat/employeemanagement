<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\CaptiveDeliveryRequest;
use App\Http\Requests\StoreCaptiveDeliveryRequest;
use App\Http\Requests\UpdateCaptiveDeliveryRequest;
use App\Http\Resources\CaptiveDeliveryResource;
use App\Models\CaptiveDelivery;
use App\Services\NotificationService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\QueryException;
use App\Exports\CaptiveDeliveriesExport;
use Maatwebsite\Excel\Facades\Excel;
use Hekmatinasser\Verta\Verta;
class CaptiveDeliveryController extends Controller
{
    private function searchableColumns(): array
    {
        return [
            'name',
            'father_name',
            'grand_father_name',
            'nickname',
            'tazkira',
            'age',
            'halat_madani',
            'criminal_history',
            'old_job',
            'new_job',
            'language',
            'scientific_level',
            'main_province',
            'main_district',
            'main_village',
            'now_province',
            'now_district',
            'now_village',
            'captive_place',
            'coordinates',
            'captive_marja',
            'captive_date',
            'captive_hour',
            'suspect_type',
            'phone_number',
            'captive_halat',
            'suspect_tools',
            'notes',
            'captive_reason',
            'taslimi_warkawonky_person',
            'taslimi_akhistonky_person',
            'taslimi_akhistonky_organ',
            'maznonyat_type',
            'Province_addr',
        ];
    }

    private function resolveSearchField(?string $field): ?string
    {
        $field = trim((string) $field);

        return in_array($field, $this->searchableColumns(), true) ? $field : null;
    }

    private function isDuplicateKey(QueryException $e): bool
    {
        return (int) ($e->errorInfo[1] ?? 0) === 1062;
    }

    private function duplicateResponse()
    {
        return response()->json([
            'message' => 'دا تسلیم شوی زنداني مخکې ثبت شوی دی.',
            'errors'  => [
                'duplicate' => [
                    'د همدې مشخصاتو سره زنداني مخکې له مخکې په سیسټم کې موجود دی.',
                ],
            ],
        ], 422);
    }

    private function baseListQuery(): Builder
    {
        return CaptiveDelivery::query()->with(['documents']);
    }

    private function applyCommonFilters(
        Builder $query,
        ?string $search,
        ?string $searchField = null,
        ?string $searchValue = null,
        string $stat = 'all'
    ): Builder {
        $resolvedField = $this->resolveSearchField($searchField);
        $search = trim((string) $search);
        $searchValue = trim((string) $searchValue);

        if ($resolvedField) {
            $value = $searchValue !== '' ? $searchValue : $search;

            if ($value !== '') {
                $query->where($resolvedField, 'like', "%{$value}%");
            }
        } elseif ($search !== '') {
            $query->where(function (Builder $sub) use ($search) {
                foreach ($this->searchableColumns() as $index => $column) {
                    if ($index === 0) {
                        $sub->where($column, 'like', "%{$search}%");
                    } else {
                        $sub->orWhere($column, 'like', "%{$search}%");
                    }
                }
            });
        }

        $query->when($stat === 'spy', function (Builder $q) {
            $q->where(function (Builder $sub) {
                $sub->where('maznonyat_type', 'جاسوسی')
                    ->orWhere('maznonyat_type', 'جاسوسي');
            });
        });

        $query->when($stat === 'saeen', function (Builder $q) {
            $q->where('maznonyat_type', 'ساعین بالفساد');
        });

        $query->when($stat === 'sharFasad', function (Builder $q) {
            $q->where('maznonyat_type', 'شر او فساد');
        });

        $query->when($stat === 'today', function (Builder $q) {
            $q->whereDate('captive_date', now()->toDateString());
        });

        return $query;
    }

    private function buildCardCounts(
        ?string $search,
        ?string $searchField = null,
        ?string $searchValue = null
    ): array {
        $base = CaptiveDelivery::query();

        $resolvedField = $this->resolveSearchField($searchField);
        $search = trim((string) $search);
        $searchValue = trim((string) $searchValue);

        if ($resolvedField) {
            $value = $searchValue !== '' ? $searchValue : $search;

            if ($value !== '') {
                $base->where($resolvedField, 'like', "%{$value}%");
            }
        } elseif ($search !== '') {
            $base->where(function (Builder $sub) use ($search) {
                foreach ($this->searchableColumns() as $index => $column) {
                    if ($index === 0) {
                        $sub->where($column, 'like', "%{$search}%");
                    } else {
                        $sub->orWhere($column, 'like', "%{$search}%");
                    }
                }
            });
        }

        return [
            'total' => (clone $base)->count(),
            'spy' => (clone $base)
                ->where(function (Builder $q) {
                    $q->where('maznonyat_type', 'جاسوسی')
                        ->orWhere('maznonyat_type', 'جاسوسي');
                })
                ->count(),
            'saeen' => (clone $base)
                ->where('maznonyat_type', 'ساعین بالفساد')
                ->count(),
            'sharFasad' => (clone $base)
                ->where('maznonyat_type', 'شر او فساد')
                ->count(),
            'today' => (clone $base)
                ->whereDate('captive_date', now()->toDateString())
                ->count(),
        ];
    }

    public function index(Request $request)
    {
        $request->validate([
            'page' => 'sometimes|integer|min:1',
            'perPage' => 'sometimes',
            'search' => 'nullable|string',
            'search_field' => 'nullable|string',
            'search_value' => 'nullable|string',
            'stat' => 'nullable|string|in:all,spy,saeen,sharFasad,today',
            'fifty_six_format_id' => 'nullable',
            'source' => 'nullable|string',
            'open' => 'nullable|string',
        ]);

        $search = $request->input('search');
        $searchField = $request->input('search_field');
        $searchValue = $request->input('search_value');
        $stat = $request->input('stat', 'all');
        $rawPerPage = $request->input('perPage', null);
        $page = (int) $request->input('page', 1);
        $fiftySixFormatId = $request->input('fifty_six_format_id');

        $cardCounts = $this->buildCardCounts($search, $searchField, $searchValue);

        $query = $this->baseListQuery();
        $this->applyCommonFilters($query, $search, $searchField, $searchValue, $stat);

        $query->when(
            is_numeric($fiftySixFormatId) && (int) $fiftySixFormatId > 0,
            function (Builder $q) use ($fiftySixFormatId) {
                $q->where('id', (int) $fiftySixFormatId);
            }
        );

        $query->latest('id');

        if (
            is_null($rawPerPage) ||
            $rawPerPage === '' ||
            strtolower((string) $rawPerPage) === 'all' ||
            (int) $rawPerPage === 0
        ) {
            return CaptiveDeliveryResource::collection($query->get())
                ->additional([
                    'card_counts' => $cardCounts,
                ]);
        }

        return CaptiveDeliveryResource::collection(
            $query->paginate(max(1, (int) $rawPerPage), ['*'], 'page', $page)
        )->additional([
            'card_counts' => $cardCounts,
        ]);
    }

    public function filterOptions(Request $request)
    {
        $field = $this->resolveSearchField($request->query('field'));
        $q = trim((string) $request->query('q', ''));

        if (!$field) {
            return response()->json([
                'data' => [
                    'options' => [],
                ],
            ]);
        }

        $query = CaptiveDelivery::query()
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

    public function show($id)
    {
        $item = $this->baseListQuery()->findOrFail($id);
        return new CaptiveDeliveryResource($item);
    }

    public function store(StoreCaptiveDeliveryRequest $request)
    {
        DB::beginTransaction();

        try {
            $user = $request->user();

            $captive = CaptiveDelivery::create($request->validated());

            if ($request->hasFile('picture')) {
                $path = $request->file('picture')->store('uploads/captive-deliveries', 'public');
                $captive->update(['picture' => $path]);
            }

            if ($request->hasFile('documents')) {
                foreach ($request->file('documents') as $file) {
                    $path = $file->store("captive_deliveries/{$captive->id}", 'public');
                    $captive->documents()->create([
                        'file_name'  => $file->getClientOriginalName(),
                        'file_path'  => $path,
                        'mime_type'  => $file->getClientMimeType(),
                        'size_bytes' => $file->getSize(),
                    ]);
                }
            }

            NotificationService::recordForAllUsers(
                actor: $user,
                module: 'تسلیم شوی زنداني',
                action: 'created',
                title: 'تسلیم شوی زنداني ثبت شوو',
                message: 'زندانی: ' . ($captive->name ?? ''),
                routeName: 'delevirycaptive',
                routeParams: ['id' => $captive->id],
                level: 'success',
                changes: null,
            );

            DB::commit();

            return new CaptiveDeliveryResource(
                $captive->load(['documents'])
            );
        } catch (QueryException $e) {
            DB::rollBack();

            if ($this->isDuplicateKey($e)) {
                return $this->duplicateResponse();
            }

            return response()->json([
                'message' => 'Captive delivery creation failed',
                'error'   => $e->getMessage(),
            ], 500);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Captive delivery creation failed',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function update(UpdateCaptiveDeliveryRequest $request, $id)
    {
        DB::beginTransaction();

        try {
            $user = $request->user();
            $captive = CaptiveDelivery::with(['documents'])->findOrFail($id);

            $original = $captive->getOriginal();

            $data = $request->validated();
            $captive->fill($data);

            $dirty = $captive->getDirty();
            $changes = [];

            foreach ($dirty as $field => $newValue) {
                $changes[$field] = [
                    'old' => $original[$field] ?? null,
                    'new' => $newValue,
                ];
            }

            $captive->save();

            if ($request->boolean('remove_picture')) {
                if ($captive->picture) {
                    Storage::disk('public')->delete($captive->picture);
                }

                $captive->update(['picture' => null]);

                $changes['picture'] = [
                    'old' => $original['picture'] ?? null,
                    'new' => null,
                ];
            }
            if (!$request->boolean('remove_picture') && $request->hasFile('picture')) {
                if ($captive->picture) {
                    Storage::disk('public')->delete($captive->picture);
                }

                $path = $request->file('picture')->store('uploads/captive-deliveries', 'public');
                $captive->update(['picture' => $path]);

                $changes['picture'] = [
                    'old' => $original['picture'] ?? null,
                    'new' => $path,
                ];
            }

            if ($request->hasFile('documents')) {
                foreach ($request->file('documents') as $file) {
                    $path = $file->store("captive_deliveries/{$captive->id}", 'public');
                    $captive->documents()->create([
                        'file_name'  => $file->getClientOriginalName(),
                        'file_path'  => $path,
                        'mime_type'  => $file->getClientMimeType(),
                        'size_bytes' => $file->getSize(),
                    ]);
                }

                $changes['documents'] = [
                    'old' => null,
                    'new' => 'updated',
                ];
            }

            NotificationService::recordForAllUsers(
                actor: $user,
                module: 'تسلیم شوی زنداني',
                action: 'updated',
                title: 'تسلیم شوی زنداني اصلاح شوو',
                message: 'زندانی: ' . ($captive->name ?? ''),
                routeName: 'delevirycaptive',
                routeParams: ['id' => $captive->id],
                level: 'info',
                changes: $changes ?: null,
            );

            DB::commit();

            return new CaptiveDeliveryResource(
                $captive->load(['documents'])
            );
        } catch (QueryException $e) {
            DB::rollBack();

            if ($this->isDuplicateKey($e)) {
                return $this->duplicateResponse();
            }

            return response()->json([
                'message' => 'Captive delivery update failed',
                'error'   => $e->getMessage(),
            ], 500);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Captive delivery update failed',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(Request $request, $id)
    {
        DB::beginTransaction();

        try {
            $captive = CaptiveDelivery::with('documents')->findOrFail($id);
            $user = $request->user();

            foreach ($captive->documents as $doc) {
                Storage::disk('public')->delete($doc->file_path);
            }

            $captive->documents()->delete();

            if ($captive->picture) {
                Storage::disk('public')->delete($captive->picture);
            }

            $captive->delete();

            NotificationService::recordForAllUsers(
                actor: $user,
                module: 'تسلیم شوی زنداني',
                action: 'deleted',
                title: 'تسلیم شوی زنداني حذف شو',
                message: 'زندانی: ' . ($captive->name ?? ''),
                routeName: null,
                routeParams: ['delevirycaptive'],
                level: 'warning',
                changes: null,
            );

            DB::commit();

            return response()->json([
                'message' => 'Captive delivery deleted successfully',
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Captive delivery delete failed',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function exportExcel(Request $request)
    {
        $request->validate([
            'search' => 'nullable|string',
            'search_field' => 'nullable|string',
            'search_value' => 'nullable|string',
            'stat' => 'nullable|string|in:all,spy,saeen,sharFasad,today',
            'date_field' => 'nullable|string|in:captive_date,created_at,updated_at',
        ]);

        return Excel::download(
            new CaptiveDeliveriesExport(
                search: $request->input('search'),
                searchField: $request->input('search_field'),
                searchValue: $request->input('search_value'),
                stat: $request->input('stat', 'all'),
                dateField: $request->input('date_field', 'captive_date'),
            ),
            'captive_deliveries.xlsx'
        );
    }
    private function gregorianToJalali(int $gy, int $gm, int $gd): array
{
    $g_d_m = [0, 31, 59, 90, 120, 151, 181, 212, 243, 273, 304, 334];

    if ($gy > 1600) {
        $jy = 979;
        $gy -= 1600;
    } else {
        $jy = 0;
        $gy -= 621;
    }

    $gy2 = ($gm > 2) ? ($gy + 1) : $gy;
    $days = (365 * $gy)
        + intdiv(($gy2 + 3), 4)
        - intdiv(($gy2 + 99), 100)
        + intdiv(($gy2 + 399), 400)
        - 80
        + $gd
        + $g_d_m[$gm - 1];

    $jy += 33 * intdiv($days, 12053);
    $days %= 12053;

    $jy += 4 * intdiv($days, 1461);
    $days %= 1461;

    if ($days > 365) {
        $jy += intdiv(($days - 1), 365);
        $days = ($days - 1) % 365;
    }

    if ($days < 186) {
        $jm = 1 + intdiv($days, 31);
        $jd = 1 + ($days % 31);
    } else {
        $jm = 7 + intdiv(($days - 186), 30);
        $jd = 1 + (($days - 186) % 30);
    }

    return [$jy, $jm, $jd];
}
    public function printView(Request $request)
{
    $query = $this->baseListQuery();

    $search      = $request->input('search');
    $searchField = $request->input('search_field');
    $searchValue = $request->input('search_value');
    $stat        = $request->input('stat', 'all');

    $this->applyCommonFilters(
        $query,
        $search,
        $searchField,
        $searchValue,
        $stat
    );

    $fiftySixFormatId = $request->input('fifty_six_format_id');

    $query->when(
        is_numeric($fiftySixFormatId) && (int) $fiftySixFormatId > 0,
        function (Builder $q) use ($fiftySixFormatId) {
            $q->where('id', (int) $fiftySixFormatId);
        }
    );

    
    $rows = $query
    ->latest('id')
    ->get()
    ->map(function ($item) {

        // ✅ ADD IT HERE
        if (!empty($item->captive_date)) {
            try {
                $carbon = \Carbon\Carbon::parse($item->captive_date);

                [$jy, $jm, $jd] = $this->gregorianToJalali(
                    (int) $carbon->format('Y'),
                    (int) $carbon->format('m'),
                    (int) $carbon->format('d')
                );

                $item->captive_date = sprintf('%04d/%02d/%02d', $jy, $jm, $jd);
            } catch (\Throwable $e) {
                // keep original value
            }
        }

        return $item;
    });

    $selectedColumns = $request->input('selected_columns', []);
    $columnLabels    = $request->input('column_labels', []);
    $printLabels     = $request->input('print_labels', []);
    $locale          = $request->input('locale', 'fa');
    $title           = $request->input('title', 'چاپ زندانی های تسلیم شده');

    return response()->view('prints.table-print', [
        'title'           => $title,
        'rows'            => $rows,
        'selectedColumns' => $selectedColumns,
        'columnLabels'    => $columnLabels,
        'printLabels'     => $printLabels,
        'locale'          => $locale,
    ]);
}
}