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
    // =========================
    // ✅ DUPLICATE HELPERS
    // =========================
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

    // =========================
    // ✅ INDEX (with month/year filter + backend stats + stat filter)
    // =========================
    public function index(Request $req)
    {
        $search = trim((string) $req->get('search', ''));

        // ✅ existing month/year filter inputs
        $year  = $req->filled('year') ? (int) $req->get('year') : null;
        $month = $req->filled('month') ? (int) $req->get('month') : null;

        // ✅ NEW: stat filter from cards
        // allowed: all | today | active | inactive
        $stat = trim((string) $req->get('stat', 'all'));
        $allowedStats = ['all', 'today', 'active', 'inactive'];
        if (!in_array($stat, $allowedStats, true)) {
            $stat = 'all';
        }

        // ✅ existing safe date field
        $dateField = $req->get('date_field', 'resource_saved_date');
        $allowedDateFields = ['resource_saved_date', 'created_at', 'updated_at'];
        if (!in_array($dateField, $allowedDateFields, true)) {
            $dateField = 'resource_saved_date';
        }

        // =========================
        // ✅ base query for all filters except stat
        // =========================
        $baseQuery = Resource::query()
            ->when($year !== null, function ($w) use ($dateField, $year) {
                $w->whereNotNull($dateField)->whereYear($dateField, $year);
            })
            ->when($month !== null && $month >= 1 && $month <= 12, function ($w) use ($dateField, $month) {
                $w->whereNotNull($dateField)->whereMonth($dateField, $month);
            })
            ->when($search !== '', function ($w) use ($search) {
                $w->where(function ($q2) use ($search) {
                    $q2->where('name', 'like', "%{$search}%")
                        ->orWhere('father_name', 'like', "%{$search}%")
                        ->orWhere('grand_father_name', 'like', "%{$search}%")
                        ->orWhere('nick_name', 'like', "%{$search}%")
                        ->orWhere('code_number', 'like', "%{$search}%")
                        ->orWhere('tazkira', 'like', "%{$search}%")
                        ->orWhere('phone_number', 'like', "%{$search}%");
                });
            });

        // =========================
        // ✅ stats from backend
        // these follow current search/year/month/date_field
        // but do NOT follow stat filter itself
        // =========================
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

        // =========================
        // ✅ main list query (same as before)
        // =========================
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
            ->when($search !== '', function ($w) use ($search) {
                $w->where(function ($q2) use ($search) {
                    $q2->where('name', 'like', "%{$search}%")
                        ->orWhere('father_name', 'like', "%{$search}%")
                        ->orWhere('grand_father_name', 'like', "%{$search}%")
                        ->orWhere('nick_name', 'like', "%{$search}%")
                        ->orWhere('code_number', 'like', "%{$search}%")
                        ->orWhere('tazkira', 'like', "%{$search}%")
                        ->orWhere('phone_number', 'like', "%{$search}%");
                });
            })

            // ✅ backend stat filter to table rows
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

            // ✅ IMPORTANT: code_number must always be ID (ignore incoming code_number)
            unset($data['code_number']);

            // Image upload (optional)
            if ($req->hasFile('picture')) {
                $path = $req->file('picture')->store('uploads/resources', 'public');
                $data['picture'] = "storage/{$path}";
            }

            $row = Resource::create($data);

            // ✅ SET code_number = id right after create
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

            // ✅ IMPORTANT: freeze code_number (never update it)
            unset($data['code_number']);

            // Replace image if a new one is provided
            if ($req->hasFile('picture')) {
                if ($row->picture && str_starts_with($row->picture, 'storage/')) {
                    Storage::disk('public')->delete(str_replace('storage/', '', $row->picture));
                }

                $path = $req->file('picture')->store('uploads/resources', 'public');
                $data['picture'] = "storage/{$path}";
            } else {
                unset($data['picture']); // keep existing
            }

            $row->fill($data)->save();

            // ✅ Ensure code_number stays correct (safety)
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

    // =========================
    // ✅ YEARS OPTIONS (for year dropdown)
    // =========================
    public function dateOptions(Request $req)
    {
        $dateField = $req->get('date_field', 'resource_saved_date');

        // allow only safe columns
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