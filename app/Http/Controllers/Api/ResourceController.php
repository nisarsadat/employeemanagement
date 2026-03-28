<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ResourceRequest;
use App\Http\Resources\ResourceResource;
use App\Models\Resource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Services\NotificationService;
use Illuminate\Database\QueryException;

class ResourceController extends Controller
{
    private function isDuplicateKey(QueryException $e): bool
    {
        return (int) ($e->errorInfo[1] ?? 0) === 1062;
    }

    private function duplicateResponsePashto()
    {
        return response()->json([
            'message' => 'دا منبع مخکې ثبت شوی دی.',
            'errors'  => [
                'duplicate' => [
                    'په همدې نوم، د پلار نوم، د نيکه نوم، مستعار نوم، کوډ نمبر او د اصلي استوګنې ځای (ولايت/ولسوالي/کلی) سره منبع مخکې په سیستم کې موجوده ده.',
                ],
            ],
        ], 422);
    }

    private function searchableColumns(): array
    {
        return [
            'letry_format_name',
            'resedint_code',
            'code_number',
            'name',
            'father_name',
            'grand_father_name',
            'nick_name',
            'tazkira',
            'main_province',
            'main_district',
            'main_village',
            'now_province',
            'now_district',
            'now_village',
            'now_specific_place',
            'now_street_number',
            'now_home_number',
            'old_job',
            'now_job',
            'job_place',
            'zone',
            'hawza',
            'specefic_place',
            'coordinates',
            'activity',
            'halat_madani',
            'age',
            'elmi_level',
            'faculty',
            'specific_skills',
            'resource_saved_date',
            'resource_activity_type',
            'resource_cover',
            'language',
            'nation',
            'blood_group',
            'train',
            'project',
            'phone_number',
            'trust_percentage',
            'note',
        ];
    }

    private function resolveSearchField(?string $field): ?string
    {
        $field = trim((string) $field);

        return in_array($field, $this->searchableColumns(), true) ? $field : null;
    }

    public function index(Request $req)
    {
        $search = trim((string) $req->get('search', ''));
        $searchField = $this->resolveSearchField($req->get('search_field'));

        $year  = $req->filled('year') ? (int) $req->get('year') : null;
        $month = $req->filled('month') ? (int) $req->get('month') : null;

        $stat = trim((string) $req->get('stat', 'all'));
        $allowedStats = ['all', 'today', 'active', 'inactive'];
        if (!in_array($stat, $allowedStats, true)) {
            $stat = 'all';
        }

        $dateField = $req->get('date_field', 'resource_saved_date');
        $allowedDateFields = ['resource_saved_date', 'created_at', 'updated_at'];
        if (!in_array($dateField, $allowedDateFields, true)) {
            $dateField = 'resource_saved_date';
        }

        $baseQuery = Resource::query()
            ->when($year !== null, function ($w) use ($dateField, $year) {
                $w->whereNotNull($dateField)->whereYear($dateField, $year);
            })
            ->when($month !== null && $month >= 1 && $month <= 12, function ($w) use ($dateField, $month) {
                $w->whereNotNull($dateField)->whereMonth($dateField, $month);
            })
            ->when($search !== '', function ($w) use ($search, $searchField) {
                if ($searchField) {
                    $w->where($searchField, 'like', "%{$search}%");
                } else {
                    $w->where(function ($q2) use ($search) {
                        foreach ($this->searchableColumns() as $index => $column) {
                            if ($index === 0) {
                                $q2->where($column, 'like', "%{$search}%");
                            } else {
                                $q2->orWhere($column, 'like', "%{$search}%");
                            }
                        }
                    });
                }
            });

        $today = now()->toDateString();

        $totalCount = (clone $baseQuery)->count();

        $todayAddedCount = (clone $baseQuery)
            ->whereNotNull($dateField)
            ->whereDate($dateField, $today)
            ->count();

        $activeCount = (clone $baseQuery)
            ->where('activity', 'فعال')
            ->count();

        $inactiveCount = (clone $baseQuery)
            ->where('activity', 'غیر فعال')
            ->count();

        $q = Resource::query()
            ->with(['officeEmployee:id,name,code_number,father_name'])
            ->withSum('finances as payed_money', 'money_amount_number')
            ->withCount([
                'events',
                'informations',
                'khayratReports',
            ])
            ->when($year !== null, function ($w) use ($dateField, $year) {
                $w->whereNotNull($dateField)->whereYear($dateField, $year);
            })
            ->when($month !== null && $month >= 1 && $month <= 12, function ($w) use ($dateField, $month) {
                $w->whereNotNull($dateField)->whereMonth($dateField, $month);
            })
            ->when($search !== '', function ($w) use ($search, $searchField) {
                if ($searchField) {
                    $w->where($searchField, 'like', "%{$search}%");
                } else {
                    $w->where(function ($q2) use ($search) {
                        foreach ($this->searchableColumns() as $index => $column) {
                            if ($index === 0) {
                                $q2->where($column, 'like', "%{$search}%");
                            } else {
                                $q2->orWhere($column, 'like', "%{$search}%");
                            }
                        }
                    });
                }
            })
            ->when($stat === 'today', function ($w) use ($dateField, $today) {
                $w->whereNotNull($dateField)->whereDate($dateField, $today);
            })
            ->when($stat === 'active', function ($w) {
                $w->where('activity', 'فعال');
            })
            ->when($stat === 'inactive', function ($w) {
                $w->where('activity', 'غیر فعال');
            })
            ->latest('id');

        $perPage = $req->filled('perPage') ? (int) $req->get('perPage') : null;

        if ($perPage !== null && $perPage > 0) {
            $data = $q->paginate($perPage);

            return ResourceResource::collection($data)->additional([
                'meta' => [
                    'stats' => [
                        'total_count'       => $totalCount,
                        'active_count'      => $activeCount,
                        'inactive_count'    => $inactiveCount,
                        'today_added_count' => $todayAddedCount,
                    ],
                ],
            ]);
        }

        $rows = $q->get();

        return ResourceResource::collection($rows)
            ->additional([
                'meta' => [
                    'total' => $rows->count(),
                    'stats' => [
                        'total_count'       => $totalCount,
                        'active_count'      => $activeCount,
                        'inactive_count'    => $inactiveCount,
                        'today_added_count' => $todayAddedCount,
                    ],
                ],
            ]);
    }

    public function show($id)
    {
        $row = Resource::query()
            ->with(['officeEmployee:id,name,code_number,father_name,nick_name'])
            ->withSum('finances as payed_money', 'money_amount_number')
            ->withCount([
                'events',
                'informations',
                'khayratReports',
            ])
            ->findOrFail($id);

        return new ResourceResource($row);
    }

    public function store(ResourceRequest $req)
    {
        try {
            $data = $req->validated();
            $user = $req->user();

            unset($data['code_number']);

            if ($req->hasFile('picture')) {
                $path = $req->file('picture')->store('uploads/resources', 'public');
                $data['picture'] = "storage/{$path}";
            }

            $row = Resource::create($data);

            $row->code_number = $row->id;
            $row->save();

            NotificationService::record(
                $user,
                module: 'resources',
                action: 'created',
                title: 'نوی منبع ثبت شوو',
                message: 'د منبع کوډ' . ($row->code_number ?? ''),
                routeName: 'resources.edit',
                routeParams: ['id' => $row->id],
                level: 'success',
            );

            return (new ResourceResource(
                $row->loadMissing('officeEmployee:id,name,code_number,father_name')->loadCount(['events', 'informations', 'khayratReports'])
            ))
                ->additional(['message' => 'Created'])
                ->response()
                ->setStatusCode(201);
        } catch (QueryException $e) {
            if ($this->isDuplicateKey($e)) {
                return $this->duplicateResponsePashto();
            }

            return response()->json([
                'message' => 'Resource creation failed',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function update(ResourceRequest $req, $id)
    {
        try {
            $row  = Resource::findOrFail($id);
            $data = $req->validated();
            $user = $req->user();

            unset($data['code_number']);

            if ($req->hasFile('picture')) {
                if ($row->picture && str_starts_with($row->picture, 'storage/')) {
                    Storage::disk('public')->delete(str_replace('storage/', '', $row->picture));
                }

                $path = $req->file('picture')->store('uploads/resources', 'public');
                $data['picture'] = "storage/{$path}";
            } else {
                unset($data['picture']);
            }

            $row->fill($data)->save();

            if ((string) $row->code_number !== (string) $row->id) {
                $row->code_number = $row->id;
                $row->save();
            }

            NotificationService::record(
                $user,
                module: 'resources',
                action: 'updated',
                title: 'منبع اصلاح شوو',
                message: 'د منبع کوډ' . ($row->code_number ?? ''),
                routeName: 'resources',
                routeParams: ['id' => $row->id],
                level: 'info',
            );

            return new ResourceResource(
                $row->loadMissing('officeEmployee:id,name,code_number,father_name')->loadCount(['events', 'informations', 'khayratReports'])
            );
        } catch (QueryException $e) {
            if ($this->isDuplicateKey($e)) {
                return $this->duplicateResponsePashto();
            }

            return response()->json([
                'message' => 'Resource update failed',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(Request $request, $id)
    {
        $row  = Resource::findOrFail($id);
        $user = $request->user();

        $code = $row->code_number;

        if ($row->picture && str_starts_with($row->picture, 'storage/')) {
            Storage::disk('public')->delete(str_replace('storage/', '', $row->picture));
        }

        $row->delete();

        NotificationService::record(
            $user,
            module: 'resources',
            action: 'deleted',
            title: 'منبع حذف شوو',
            message: 'د منبع کوډ' . ($code ?? ''),
            routeName: null,
            routeParams: [],
            level: 'warning',
        );

        return response()->json(['message' => 'Deleted']);
    }

    public function dateOptions(Request $req)
    {
        $dateField = $req->get('date_field', 'resource_saved_date');

        if (!in_array($dateField, ['resource_saved_date', 'created_at', 'updated_at'], true)) {
            $dateField = 'resource_saved_date';
        }

        $years = Resource::query()
            ->whereNotNull($dateField)
            ->selectRaw("YEAR($dateField) as y")
            ->groupBy('y')
            ->orderBy('y', 'desc')
            ->pluck('y')
            ->values();

        return response()->json([
            'years' => $years,
        ]);
    }
}