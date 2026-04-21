<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ResourceResource;
use App\Models\Resource;
use Illuminate\Http\Request;

class ResourceActivityController extends Controller
{
    /**
     * Active means:
     * - Any Event in last 30 days
     * - OR any Information in last 30 days
     * - OR any KhayratReport in last 30 days
     *
     * Inactive means:
     * - No Event, Information, or KhayratReport in last 30 days
     */
    private function applyActiveScope($query, $since)
    {
        return $query->where(function ($q) use ($since) {
            $q->whereHas('events', function ($qq) use ($since) {
                $qq->where('created_at', '>=', $since);
            });

            $q->orWhereHas('informations', function ($qq) use ($since) {
                $qq->where('created_at', '>=', $since);
            });

            $q->orWhereHas('khayratReports', function ($qq) use ($since) {
                $qq->where('created_at', '>=', $since);
            });
        });
    }

    private function applySearch($query, ?string $search)
    {
        $s = trim((string) $search);

        if ($s === '') {
            return;
        }

        $query->where(function ($q) use ($s) {
            $q->where('name', 'like', "%{$s}%")
              ->orWhere('code_number', 'like', "%{$s}%")
              ->orWhere('phone_number', 'like', "%{$s}%");
        });
    }

    private function withCounts($query)
    {
        return $query->withCount([
            'events',
            'informations',
            'khayratReports',
        ]);
    }

    public function counts(Request $request)
    {
        $since = now()->subDays(30);

        $total = Resource::count();

        $activeQ = Resource::query();
        $this->applyActiveScope($activeQ, $since);
        $active = $activeQ->count();

        $inactive = $total - $active;

        $today = now()->startOfDay();
        $todayAdded = Resource::query()
            ->where(function ($q) use ($today) {
                $q->whereDate('resource_saved_date', $today)
                  ->orWhereDate('created_at', $today);
            })
            ->count();

        return response()->json([
            'total'       => $total,
            'active'      => $active,
            'inactive'    => $inactive,
            'today_added' => $todayAdded,
            'since'       => $since->toDateTimeString(),
        ]);
    }

    public function index(Request $request)
    {
        $mode = $request->get('mode', 'active'); // active | inactive
        $perPage = $request->get('perPage', 25); // 10/25/50 or "all"
        $search = $request->get('search');

        $since = now()->subDays(30);

        $q = Resource::query()
            ->with(['officeEmployee:id,name,code_number,phone_number,father_name,nick_name'])
            ->orderByDesc('id');

        $this->withCounts($q);
        $this->applySearch($q, $search);

        if ($mode === 'active') {
            $this->applyActiveScope($q, $since);
        } else {
            $activeSub = Resource::query()->select('id');
            $this->applyActiveScope($activeSub, $since);
            $q->whereNotIn('id', $activeSub);
        }

        if ($perPage === 'all') {
            $items = $q->get();

            return ResourceResource::collection($items)->additional([
                'meta' => [
                    'total' => $items->count(),
                ],
            ]);
        }

        $perPage = (int) $perPage;
        $perPage = max(1, min(200, $perPage));

        $page = $q->paginate($perPage);

        return ResourceResource::collection($page)->additional([
            'meta' => [
                'total' => $page->total(),
            ],
        ]);
    }

    public function show($id)
    {
        $q = Resource::query()
            ->with(['officeEmployee:id,name,code_number,phone_number,father_name,nick_name'])
            ->whereKey($id);

        $this->withCounts($q);

        $row = $q->firstOrFail();

        return new ResourceResource($row);
    }
}