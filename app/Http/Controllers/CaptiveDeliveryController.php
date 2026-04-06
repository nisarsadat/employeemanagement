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
            'captive_reason',
            'maznonyat_type',
            'taslimi_warkawonky_person',
            'taslimi_akhistonky_person',
            'taslimi_akhistonky_organ',
            'captive_place',
            'coordinates',
            'captive_marja',
            'captive_date',
            'captive_hour',
            'phone_number',
            'captive_halat',
            'suspect_tools',
            'notes',
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
            'message' => 'این زندانی قبلاً ثبت شده است.',
            'errors'  => [
                'duplicate' => [
                    'زندانی با همین مشخصات قبلاً در سیستم موجود است.',
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
        string $stat = 'all'
    ): Builder {
        $query->when($search, function (Builder $q) use ($search, $searchField) {
            $resolvedField = $this->resolveSearchField($searchField);

            if ($resolvedField) {
                $q->where($resolvedField, 'like', "%{$search}%");
                return;
            }

            $q->where(function (Builder $sub) use ($search) {
                foreach ($this->searchableColumns() as $index => $column) {
                    if ($index === 0) {
                        $sub->where($column, 'like', "%{$search}%");
                    } else {
                        $sub->orWhere($column, 'like', "%{$search}%");
                    }
                }
            });
        });

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

    private function buildCardCounts(?string $search, ?string $searchField = null): array
    {
        $base = CaptiveDelivery::query();

        if ($search) {
            $resolvedField = $this->resolveSearchField($searchField);

            if ($resolvedField) {
                $base->where($resolvedField, 'like', "%{$search}%");
            } else {
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
            'stat' => 'nullable|string|in:all,spy,saeen,sharFasad,today',
        ]);

        $search = $request->input('search');
        $searchField = $this->resolveSearchField($request->input('search_field'));
        $stat = $request->input('stat', 'all');
        $rawPerPage = $request->input('perPage', null);
        $page = (int) $request->input('page', 1);

        $cardCounts = $this->buildCardCounts($search, $searchField);

        $query = $this->baseListQuery();
        $this->applyCommonFilters($query, $search, $searchField, $stat);

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

            if ($request->hasFile('picture')) {
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
        'stat' => 'nullable|string|in:all,spy,saeen,sharFasad,today',
        'date_field' => 'nullable|string|in:captive_date,created_at,updated_at',
    ]);

    return Excel::download(
        new CaptiveDeliveriesExport(
            search: $request->input('search'),
            searchField: $request->input('search_field'),
            stat: $request->input('stat', 'all'),
            dateField: $request->input('date_field', 'captive_date'),
        ),
        'captive_deliveries.xlsx'
    );
}
}