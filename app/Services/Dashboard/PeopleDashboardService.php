<?php

namespace App\Services\Dashboard;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

// Models
use App\Models\OfficeEmployee;
use App\Models\Resource;
use App\Models\CaptiveDelivery;
use App\Models\Event;
use App\Models\Information;

class PeopleDashboardService
{
    /**
     * Normalize common query params.
     *
     * Returns: [Carbon $from, Carbon $to, ?string $zone, ?string $search, string $groupBy]
     */
    private function range(Request $r): array
    {
        $from = $r->filled('from')
            ? Carbon::parse($r->input('from'))->startOfDay()
            : now()->copy()->subMonth()->startOfDay();

        $to = $r->filled('to')
            ? Carbon::parse($r->input('to'))->endOfDay()
            : now()->copy()->endOfDay();

        $zone    = $r->input('zone');                 // nullable
        $search  = $r->input('search');               // optional (not used below but kept)
        $groupBy = strtolower($r->input('groupBy', 'month'));

        return [$from, $to, $zone, $search, $groupBy];
    }

    // ---------------------------------------------------------------------
    // CARDS
    // ---------------------------------------------------------------------
    public function cards(Request $r)
    {
        [$from, $to, $zone] = $this->range($r);

        $activeEmployees = OfficeEmployee::when($zone, fn($q) => $q->where('zone', $zone))
            ->where('activity', 'فعال')
            ->count();

        // For these two we keep it simple (filter by current province if zone was passed)
        $newResources = Resource::when($zone, fn($q) => $q->where('now_province', $zone))
            ->whereBetween('created_at', [$from, $to])
            ->count();

        $captiveDeliveries = CaptiveDelivery::when($zone, fn($q) => $q->where('now_province', $zone))
            ->whereBetween('created_at', [$from, $to])
            ->count();

        return [
            'active_employees'   => $activeEmployees,
            'new_resources'      => $newResources,
            'captive_deliveries' => $captiveDeliveries,
        ];
    }

    // ---------------------------------------------------------------------
    // CHARTS: EMPLOYEES BY ZONE (all employees)
    // ---------------------------------------------------------------------
    public function employeesByZone(Request $r)
    {
        [$from, $to, $zone] = $this->range($r);

        return OfficeEmployee::when($zone, fn($q) => $q->where('zone', $zone))
            ->selectRaw('COALESCE(zone, "نامعلوم") as zone, COUNT(*) as total, SUM(activity = "فعال") as active')
            ->groupBy('zone')
            ->orderBy('zone')
            ->get();
    }

    // ---------------------------------------------------------------------
    // CHARTS: EMPLOYEES WITH EVENTS BY ZONE
    // (JOIN events -> office_employees and use the correct event date column)
    // ---------------------------------------------------------------------
    public function employeesWithEventsByZone(Request $r)
    {
        [$from, $to, $zone] = $this->range($r);

        $eventsTable = (new Event())->getTable();          // typically 'events'
        $empTable    = (new OfficeEmployee())->getTable(); // 'office_employees'
        $eventDate   = 'event_date';                       // your schema uses event_date

        return DB::table("$eventsTable as ev")
            ->leftJoin("$empTable as e", 'ev.office_employee_id', '=', 'e.id')
            ->when($zone, fn($q) => $q->where('e.zone', $zone))
            ->whereBetween("ev.$eventDate", [$from, $to])
            ->selectRaw('COALESCE(e.zone, "نامعلوم") as zone, COUNT(*) as count')
            ->groupBy('e.zone')
            ->orderBy('zone')
            ->get();
    }

    // ---------------------------------------------------------------------
    // CHARTS: EMPLOYEES WITH INFORMATIONS BY ZONE
    // (JOIN information -> office_employees and use information_saved_date)
    // ---------------------------------------------------------------------
    public function employeesWithInformationsByZone(Request $r)
    {
        [$from, $to, $zone] = $this->range($r);

        $infoTable = (new Information())->getTable();      // in your DB this is 'information' (uncountable)
        $empTable  = (new OfficeEmployee())->getTable();   // 'office_employees'
        $infoDate  = 'information_saved_date';             // correct date column for this table

        return DB::table("$infoTable as i")
            ->leftJoin("$empTable as e", 'i.office_employee_id', '=', 'e.id')
            ->when($zone, fn($q) => $q->where('e.zone', $zone))
            ->whereBetween("i.$infoDate", [$from, $to])
            ->selectRaw('COALESCE(e.zone, "نامعلوم") as zone, COUNT(*) as count')
            ->groupBy('e.zone')
            ->orderBy('zone')
            ->get();
    }

    // ---------------------------------------------------------------------
    // CHARTS: TIME SERIES (resources / captive deliveries)
    // ---------------------------------------------------------------------
    public function resourcesOverTime(Request $r)
    {
        [$from, $to, $zone, $search, $groupBy] = $this->range($r);

        return Resource::when($zone, fn($q) => $q->where('now_province', $zone))
            ->whereBetween('created_at', [$from, $to])
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as period, COUNT(*) as count")
            ->groupBy('period')
            ->orderBy('period')
            ->get();
    }

    public function captiveDeliveriesOverTime(Request $r)
    {
        [$from, $to, $zone, $search, $groupBy] = $this->range($r);

        return CaptiveDelivery::when($zone, fn($q) => $q->where('now_province', $zone))
            ->whereBetween('created_at', [$from, $to])
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as period, COUNT(*) as count")
            ->groupBy('period')
            ->orderBy('period')
            ->get();
    }

    // ---------------------------------------------------------------------
    // TABLE: EMPLOYEE ACTIVITY (events | info) grouped by employee
    // ---------------------------------------------------------------------
    public function employeeActivity(Request $r)
    {
        [$from, $to, $zone] = $this->range($r);
        $group = $r->input('group', 'events');   // 'events' | 'info'
        $per   = (int) $r->input('per_page', 10);

        if ($group === 'info') {
            $infoTable = (new Information())->getTable();
            $empTable  = (new OfficeEmployee())->getTable();
            $infoDate  = 'information_saved_date';

            return DB::table("$infoTable as i")
                ->leftJoin("$empTable as e", 'i.office_employee_id', '=', 'e.id')
                ->when($zone, fn($q) => $q->where('e.zone', $zone))
                ->whereBetween("i.$infoDate", [$from, $to])
                ->selectRaw('e.id as office_employee_id, COALESCE(e.name,"نامعلوم") as name, COUNT(i.id) as total')
                ->groupBy('e.id', 'e.name')
                ->orderByDesc('total')
                ->paginate($per);
        }

        $eventsTable = (new Event())->getTable();
        $empTable    = (new OfficeEmployee())->getTable();
        $eventDate   = 'event_date';

        return DB::table("$eventsTable as ev")
            ->leftJoin("$empTable as e", 'ev.office_employee_id', '=', 'e.id')
            ->when($zone, fn($q) => $q->where('e.zone', $zone))
            ->whereBetween("ev.$eventDate", [$from, $to])
            ->selectRaw('e.id as office_employee_id, COALESCE(e.name,"نامعلوم") as name, COUNT(ev.id) as total')
            ->groupBy('e.id', 'e.name')
            ->orderByDesc('total')
            ->paginate($per);
    }
}
