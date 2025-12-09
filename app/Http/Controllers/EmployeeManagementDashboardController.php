<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

use App\Models\OfficeEmployee;
use App\Models\Resource;
use App\Models\Information;
use App\Models\Event;
use App\Models\CaptiveDelivery;

// --------- 56 formats ---------
// Main Fifty Formats (10)
use App\Models\fifthyFormats\{
    Fasad,
    SaeenFasad,
    Heist,
    Captive,
    Mulkano,
    EntryExit,
    Sarafyano,
    Clubs,
    Mahajerat,
    Soldier
};

// Fifthy Format One (10)
use App\Models\fifthyformatone\{
    Mawad,
    LostMujremeen,
    OldSoldiersHomes,
    Robbers,
    NonResponsablePeople,
    MashkokHomes,
    DamagePlaces,
    DamageRasanPlaces,
    MashkokShops,
    Luchakano
};

// Fifthy Format Two (10)
use App\Models\fifthyformatstow\{
    School,
    HadoBusStop,
    TransportCompany,
    CarSalesCompany,
    Parking,
    Bank,
    Apartment,
    RahnomaMamlat,
    Hosppital,
    WorkShop
};

// Fifthy Format Three (10)
use App\Models\fifthyformatsthree\{
    Badmashano,
    EmaratOffice,
    Hostel,
    Hotel,
    Madrasa,
    MashkokPeople,
    Mujahedino,
    OldJahadi,
    Sarayano,
    Tujaran
};

// Fifthy Format Four (16)
use App\Models\fifthyformatfour\{
    Checkpoint,
    Masjid,
    Highway,
    Company,
    Donelostplace,
    EconemyProject,
    CriminalCapturePlace,
    Pump,
    Shora,
    GovernmentPlace,
    SuperMarket,
    Park,
    Complex,
    DeplomaticPlace,
    University,
    Wakilano
};

class EmployeeManagementDashboardController extends Controller
{
    /**
     * Main cards for the Employee Management dashboard.
     *
     * Phase 1: all NON-financial metrics.
     * Money / oil are returned with 0 for now so the frontend
     * can already rely on the response shape.
     */
    public function cards(Request $request)
    {
        // Employees
        $totalEmployees = OfficeEmployee::count();

        // Resources
        $totalResources         = Resource::count();
        $totalActiveResources   = Resource::where('activity', OfficeEmployee::ACTIVE)->count();
        $totalInactiveResources = Resource::where('activity', OfficeEmployee::INACTIVE)->count();

        // Information & Events
        $totalInformations = Information::count();
        $totalEvents       = Event::count();

        // Captive deliveries
        $totalCaptiveDeliveries = CaptiveDelivery::count();

        return response()->json([
            // ---------------- EMPLOYEES / RESOURCES ----------------
            'total_employees'           => $totalEmployees,
            'total_resources'           => $totalResources,
            'total_resources_active'    => $totalActiveResources,
            'total_resources_inactive'  => $totalInactiveResources,

            // ---------------- INFO / EVENTS / CAPTIVES ------------- 
            'total_informations'        => $totalInformations,
            'total_events'              => $totalEvents,
            'total_captive_deliveries'  => $totalCaptiveDeliveries,

            // ---------------- MONEY (PLACEHOLDERS – you fill later)-
            'total_money'               => 0,
            'total_money_expense'       => 0,
            'total_money_income'        => 0,
            'total_money_balance'       => 0,

            // ---------------- OIL (PLACEHOLDERS – you fill later) --
            'total_oil'                 => 0,
            'total_oil_expense'         => 0,
            'total_oil_income'          => 0,
            'total_oil_balance'         => 0,
        ]);
    }

    /**
     * Helper: build time series for a table + date column,
     * grouped by mode: daily | weekly | monthly | yearly | all
     *
     * "all" means "whole time", grouped monthly.
     */
    protected function buildTimeseries(string $table, string $dateColumn, string $mode)
    {
        $col = $dateColumn;

        // "all" = whole time, grouped by month
        $groupMode = $mode === 'all' ? 'monthly' : $mode;

        switch ($groupMode) {
            case 'daily':
                // 2025-12-09
                $select = "DATE($col) as date";
                $group  = DB::raw("DATE($col)");
                $order  = DB::raw("DATE($col)");
                break;

            case 'weekly':
                // ISO year-week: 2025-W01
                $select = "DATE_FORMAT($col, '%x-و%v') as date";
                $group  = DB::raw("DATE_FORMAT($col, '%x-%v')");
                $order  = $group;
                break;

            case 'yearly':
                // 2025
                $select = "DATE_FORMAT($col, '%Y') as date";
                $group  = DB::raw("YEAR($col)");
                $order  = $group;
                break;

            case 'monthly':
            default:
                // 2025-12
                $select = "DATE_FORMAT($col, '%Y-%m') as date";
                $group  = DB::raw("DATE_FORMAT($col, '%Y-%m')");
                $order  = $group;
                break;
        }

        return DB::table($table)
            ->selectRaw("$select, COUNT(*) as count")
            ->whereNotNull($col)
            ->groupBy($group)
            ->orderBy($order)
            ->get();
    }

    /**
     * Timeseries data for graphs:
     * - resources by date (resource_saved_date)
     * - informations by date (information_saved_date)
     * - events by date (event_date)
     *
     * Query param:
     *  - mode = daily | weekly | monthly | yearly | all
     *
     * Default: mode=all (whole time, grouped monthly).
     * No from/to filter – filter is for the *whole* project history.
     */
    public function timeseries(Request $request)
    {
        // mode = daily | weekly | monthly | yearly | all
        $mode = $request->query('mode', 'all');
        $allowed = ['daily', 'weekly', 'monthly', 'yearly', 'all'];

        if (!in_array($mode, $allowed, true)) {
            $mode = 'all';
        }

        // ⚠️ No date range: whole history in DB.
        $resources = $this->buildTimeseries('resources', 'resource_saved_date', $mode);
        $informations = $this->buildTimeseries('information', 'information_saved_date', $mode);
        $events = $this->buildTimeseries('events', 'event_date', $mode);

        return response()->json([
            'mode'          => $mode,
            'resources'     => $resources,
            'informations'  => $informations,
            'events'        => $events,
        ]);
    }

    /**
     * 56 formats categorized by hawza.
     *
     * Logic:
     *  - Each 56-format row belongs to a Resource (resource_id).
     *  - Resource belongs to an OfficeEmployee (office_employee_id).
     *  - OfficeEmployee has hawza.
     *
     * We join: format -> resources -> office_employees and group by hawza.
     */
    public function formatsByHawza(Request $request)
    {
        // key in response => model class
        $formatModels = [
            // Main 10
            'fasads'          => Fasad::class,
            'saeen_fasads'    => SaeenFasad::class,
            'heists'          => Heist::class,
            'captives'        => Captive::class,
            'mulkanos'        => Mulkano::class,
            'entry_exits'     => EntryExit::class,
            'sarafyanos'      => Sarafyano::class,
            'clubs'           => Clubs::class,
            'mahajerats'      => Mahajerat::class,
            'soldiers'        => Soldier::class,

            // Format One (10)
            'mawads'                => Mawad::class,
            'lost_mujremeens'       => LostMujremeen::class,
            'old_soldiers_homes'    => OldSoldiersHomes::class,
            'robbers'               => Robbers::class,
            'non_responsable_people'=> NonResponsablePeople::class,
            'mashkok_homes'         => MashkokHomes::class,
            'damage_places'         => DamagePlaces::class,
            'damage_rasan_places'   => DamageRasanPlaces::class,
            'mashkok_shops'         => MashkokShops::class,
            'luchakanos'            => Luchakano::class,

            // Format Two (10)
            'schools'               => School::class,
            'hado_bus_stops'        => HadoBusStop::class,
            'transport_companies'   => TransportCompany::class,
            'car_sales_companies'   => CarSalesCompany::class,
            'parkings'              => Parking::class,
            'banks'                 => Bank::class,
            'apartments'            => Apartment::class,
            'rahnoma_mamlats'       => RahnomaMamlat::class,
            'hosppitals'            => Hosppital::class,
            'work_shops'            => WorkShop::class,

            // Format Three (10)
            'badmashanos'           => Badmashano::class,
            'emarat_offices'        => EmaratOffice::class,
            'hostels'               => Hostel::class,
            'hotels'                => Hotel::class,
            'madrasas'              => Madrasa::class,
            'mashkok_people'        => MashkokPeople::class,
            'mujahedinos'           => Mujahedino::class,
            'old_jahadis'           => OldJahadi::class,
            'sarayanos'             => Sarayano::class,
            'tujarans'              => Tujaran::class,

            // Format Four (16)
            'checkpoints'               => Checkpoint::class,
            'masjids'                   => Masjid::class,
            'highways'                  => Highway::class,
            'companies'                 => Company::class,
            'donelostplaces'            => Donelostplace::class,
            'econemy_projects'          => EconemyProject::class,
            'criminal_capture_places'   => CriminalCapturePlace::class,
            'pumps'                     => Pump::class,
            'shoras'                    => Shora::class,
            'governmentplaces'          => GovernmentPlace::class,
            'supermarkets'              => SuperMarket::class,
            'parks'                     => Park::class,
            'complexes'                 => Complex::class,
            'deplomaticplaces'          => DeplomaticPlace::class,
            'universities'              => University::class,
            'wakilanos'                 => Wakilano::class,
        ];

        $formatKeys = array_keys($formatModels);

        // Matrix: [hawza][format_key] = count
        $matrix = [];
        // Totals per hawza (all formats)
        $totalByHawza = [];
        // Totals per format (all hawzas)
        $totalPerFormat = array_fill_keys($formatKeys, 0);

        foreach ($formatModels as $key => $modelClass) {
            /** @var \Illuminate\Database\Eloquent\Model $instance */
            $instance = new $modelClass();
            $table    = $instance->getTable();

            $rows = DB::table($table)
                ->join('resources', 'resources.id', '=', $table . '.resource_id')
                ->join('office_employees', 'office_employees.id', '=', 'resources.office_employee_id')
                ->selectRaw('COALESCE(office_employees.hawza, "نامعلوم") as hawza, COUNT(*) as count')
                ->groupBy('hawza')
                ->get();

            foreach ($rows as $row) {
                $hawza = $row->hawza ?? 'نامعلوم';
                $count = (int) $row->count;

                // Ensure hawza row exists and has all format keys initialized with 0
                if (!isset($matrix[$hawza])) {
                    $matrix[$hawza] = array_fill_keys($formatKeys, 0);
                }

                // Set this format count
                $matrix[$hawza][$key] = $count;

                // Accumulate totals
                $totalByHawza[$hawza] = ($totalByHawza[$hawza] ?? 0) + $count;
                $totalPerFormat[$key] = ($totalPerFormat[$key] ?? 0) + $count;
            }
        }

        // Build final rows: each hawza has all formats + total
        $hawzaRows = [];
        foreach ($matrix as $hawza => $formats) {
            $row = [
                'hawza' => $hawza,
            ];

            // Add each format count
            foreach ($formatKeys as $key) {
                $row[$key] = $formats[$key] ?? 0;
            }

            // Total across all 56 formats for this hawza
            $row['total'] = array_sum($formats);

            $hawzaRows[] = $row;
        }

        // Sort hawzas by total desc
        usort($hawzaRows, function ($a, $b) {
            return ($b['total'] <=> $a['total']);
        });

        return response()->json([
            // For table:
            // [
            //   { hawza: '1', fasads: 3, heists: 2, ..., total: 10 },
            //   { hawza: '2', fasads: 0, heists: 5, ..., total: 7 },
            // ]
            'hawzas'            => $hawzaRows,

            // For legends / totals per format (for cards or stacked chart)
            // { fasads: 12, heists: 5, ..., wakilanos: 3 }
            'totals_per_format' => $totalPerFormat,

            // So frontend knows dynamic column order
            'format_keys'       => $formatKeys,
        ]);
    }

}
