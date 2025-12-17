<?php

namespace App\Http\Controllers\Api\Reports\Foul;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;

use App\Models\Resource;
use App\Models\Information;
use App\Models\Event;

// FifthyFormats (original)
use App\Models\fifthyFormats\Fasad;
use App\Models\fifthyFormats\SaeenFasad;
use App\Models\fifthyFormats\Heist;
use App\Models\fifthyFormats\Captive;
use App\Models\fifthyFormats\Mulkano;
use App\Models\fifthyFormats\EntryExit;
use App\Models\fifthyFormats\Sarafyano;
use App\Models\fifthyFormats\Clubs;
use App\Models\fifthyFormats\Mahajerat;
use App\Models\fifthyFormats\Soldier;

// Format One
use App\Models\fifthyformatone\Mawad;
use App\Models\fifthyformatone\LostMujremeen;
use App\Models\fifthyformatone\OldSoldiersHomes;
use App\Models\fifthyformatone\Robbers;
use App\Models\fifthyformatone\NonResponsablePeople;
use App\Models\fifthyformatone\MashkokHomes;
use App\Models\fifthyformatone\DamagePlaces;
use App\Models\fifthyformatone\DamageRasanPlaces;
use App\Models\fifthyformatone\MashkokShops;
use App\Models\fifthyformatone\Luchakano;

// Format Two
use App\Models\fifthyformatstow\WorkShop;
use App\Models\fifthyformatstow\Hosppital;
use App\Models\fifthyformatstow\RahnomaMamlat;
use App\Models\fifthyformatstow\Apartment;
use App\Models\fifthyformatstow\Bank;
use App\Models\fifthyformatstow\TransportCompany;
use App\Models\fifthyformatstow\HadoBusStop;
use App\Models\fifthyformatstow\CarSalesCompany;
use App\Models\fifthyformatstow\School;
use App\Models\fifthyformatstow\Parking;

// Format Four
use App\Models\fifthyformatfour\Wakilano;
use App\Models\fifthyformatfour\University;
use App\Models\fifthyformatfour\Masjid;
use App\Models\fifthyformatfour\Highway;
use App\Models\fifthyformatfour\Company;
use App\Models\fifthyformatfour\Donelostplace;
use App\Models\fifthyformatfour\EconemyProject;
use App\Models\fifthyformatfour\CriminalCapturePlace;
use App\Models\fifthyformatfour\Pump;
use App\Models\fifthyformatfour\Shora;
use App\Models\fifthyformatfour\Checkpoint;
use App\Models\fifthyformatfour\GovernmentPlace;
use App\Models\fifthyformatfour\SuperMarket;
use App\Models\fifthyformatfour\Park;
use App\Models\fifthyformatfour\Complex;
use App\Models\fifthyformatfour\DeplomaticPlace;

// Format Three
use App\Models\fifthyformatsthree\Badmashano;
use App\Models\fifthyformatsthree\EmaratOffice;
use App\Models\fifthyformatsthree\Hostel;
use App\Models\fifthyformatsthree\Hotel;
use App\Models\fifthyformatsthree\Madrasa;
use App\Models\fifthyformatsthree\MashkokPeople;
use App\Models\fifthyformatsthree\Mujahedino;
use App\Models\fifthyformatsthree\OldJahadi;
use App\Models\fifthyformatsthree\Sarayano;
use App\Models\fifthyformatsthree\Tujaran;

class DailyFoulReportController extends Controller
{
    /**
     * Daily Foul System Report
     * GET /api/reports/foul/daily?date=YYYY-MM-DD
     */
    public function __invoke(Request $request)
    {
        $date = $this->parseDate($request->query('date'));

        // 1) Resources total (by resource_saved_date)
        $resourcesTotal = Resource::query()
            ->whereDate('resource_saved_date', $date)
            ->count();

        // 2) Informations total + combined types (NO group)
        $infoBase = Information::query()->whereDate('information_saved_date', $date);

        $informationsTotal = (clone $infoBase)->count();

        $syasiTypes = (clone $infoBase)
            ->whereNotNull('syasi_info_type')
            ->where('syasi_info_type', '!=', '')
            ->selectRaw("syasi_info_type as label, COUNT(*) as count")
            ->groupBy('syasi_info_type')
            ->get();

        $jenayeeTypes = (clone $infoBase)
            ->whereNotNull('jenayee_info_type')
            ->where('jenayee_info_type', '!=', '')
            ->selectRaw("jenayee_info_type as label, COUNT(*) as count")
            ->groupBy('jenayee_info_type')
            ->get();

        // merge + if same label appears in both, SUM them
        $informationTypes = $syasiTypes
            ->concat($jenayeeTypes)
            ->groupBy('label')
            ->map(function ($items, $label) {
                return [
                    'label' => $label,
                    'count' => (int) $items->sum('count'),
                ];
            })
            ->values()
            ->sortByDesc('count')
            ->values();

        // 3) Events total + rows (each record, only selected fields)
        $eventBase = Event::query()->whereDate('event_date', $date);

        $eventsTotal = (clone $eventBase)->count();
    $eventRows = (clone $eventBase)
        ->select([
            'Province_addr',
            'event_hawza',
            'event_specific_type',
            'killed',
            'injured',
            'talafat_mali',
        ])
        ->orderBy('id', 'desc')
        ->get()
        ->map(function ($row) {
            return [
                'province' => $row->getRawOriginal('Province_addr') ?? $row->Province_addr,
                'hawza' => $row->getRawOriginal('event_hawza') ?? $row->event_hawza,
                'event_specific_type' => $row->getRawOriginal('event_specific_type') ?? $row->event_specific_type,

                // ✅ EXACTLY as saved in DB (string stays string, null stays null)
                'killed' => $row->getRawOriginal('killed'),
                'injured' => $row->getRawOriginal('injured'),
                'talafat_mali' => $row->getRawOriginal('talafat_mali'),
            ];
        });


        // 4) 56-format totals (only those with data)
        $formats = $this->formatCounts($date);

        return response()->json([
            'meta' => [
                'report' => 'daily',
                'date' => $date->toDateString(),
            ],

            'resources' => [
                'total' => $resourcesTotal,
            ],

            'informations' => [
                'total' => $informationsTotal,
                'types' => $informationTypes,
            ],

            'events' => [
                'total' => $eventsTotal,
                'rows' => $eventRows,
            ],

            'formats' => $formats,
        ]);
    }

    private function parseDate(?string $dateStr): Carbon
    {
        if ($dateStr) {
            try {
                return Carbon::parse($dateStr)->startOfDay();
            } catch (\Throwable $e) {
                // fallback below
            }
        }
        return Carbon::today()->startOfDay();
    }

    /**
     * Count records for all “formats” (models), daily-filtered if created_at exists.
     * Returns only formats with count > 0:
     * [ {key, name, count}, ... ]
     */
    private function formatCounts(Carbon $date): array
    {
        $map = [
            // FifthyFormats (original)
            ['key' => 'fasads', 'name' => 'fasads', 'model' => Fasad::class],
            ['key' => 'saeenFasads', 'name' => 'saeenFasads', 'model' => SaeenFasad::class],
            ['key' => 'heists', 'name' => 'heists', 'model' => Heist::class],
            ['key' => 'captives', 'name' => 'captives', 'model' => Captive::class],
            ['key' => 'mulkanos', 'name' => 'mulkanos', 'model' => Mulkano::class],
            ['key' => 'entryExits', 'name' => 'entryExits', 'model' => EntryExit::class],
            ['key' => 'sarafyanos', 'name' => 'sarafyanos', 'model' => Sarafyano::class],
            ['key' => 'clubs', 'name' => 'clubs', 'model' => Clubs::class],
            ['key' => 'mahajerats', 'name' => 'mahajerats', 'model' => Mahajerat::class],
            ['key' => 'soldiers', 'name' => 'soldiers', 'model' => Soldier::class],

            // Format One
            ['key' => 'mawads', 'name' => 'mawads', 'model' => Mawad::class],
            ['key' => 'lostmujremeens', 'name' => 'lostmujremeens', 'model' => LostMujremeen::class],
            ['key' => 'oldSoldiersHomes', 'name' => 'oldSoldiersHomes', 'model' => OldSoldiersHomes::class],
            ['key' => 'robbers', 'name' => 'robbers', 'model' => Robbers::class],
            ['key' => 'nonResponsablePeople', 'name' => 'nonResponsablePeople', 'model' => NonResponsablePeople::class],
            ['key' => 'mashkokHomes', 'name' => 'mashkokHomes', 'model' => MashkokHomes::class],
            ['key' => 'damagePlaces', 'name' => 'damagePlaces', 'model' => DamagePlaces::class],
            ['key' => 'damageRasanPlaces', 'name' => 'damageRasanPlaces', 'model' => DamageRasanPlaces::class],
            ['key' => 'mashkokShops', 'name' => 'mashkokShops', 'model' => MashkokShops::class],
            ['key' => 'luchakano', 'name' => 'luchakano', 'model' => Luchakano::class],

            // Format Two
            ['key' => 'workshops', 'name' => 'workshops', 'model' => WorkShop::class],
            ['key' => 'hosppitals', 'name' => 'hosppitals', 'model' => Hosppital::class],
            ['key' => 'rahnomaMamlats', 'name' => 'rahnomaMamlats', 'model' => RahnomaMamlat::class],
            ['key' => 'apartments', 'name' => 'apartments', 'model' => Apartment::class],
            ['key' => 'banks', 'name' => 'banks', 'model' => Bank::class],
            ['key' => 'transportCompanies', 'name' => 'transportCompanies', 'model' => TransportCompany::class],
            ['key' => 'hadoBusStops', 'name' => 'hadoBusStops', 'model' => HadoBusStop::class], // 
            ['key' => 'carSalesCompanies', 'name' => 'carSalesCompanies', 'model' => CarSalesCompany::class],
            ['key' => 'schools', 'name' => 'schools', 'model' => School::class],
            ['key' => 'parkings', 'name' => 'parkings', 'model' => Parking::class],

            // Format Four
            ['key' => 'wakilanos', 'name' => 'wakilanos', 'model' => Wakilano::class],
            ['key' => 'universities', 'name' => 'universities', 'model' => University::class],
            ['key' => 'masjids', 'name' => 'masjids', 'model' => Masjid::class],
            ['key' => 'highways', 'name' => 'highways', 'model' => Highway::class],
            ['key' => 'companies', 'name' => 'companies', 'model' => Company::class],
            ['key' => 'donelostplaces', 'name' => 'donelostplaces', 'model' => Donelostplace::class],
            ['key' => 'econemyProjects', 'name' => 'econemyProjects', 'model' => EconemyProject::class],
            ['key' => 'criminalCapturePlaces', 'name' => 'criminalCapturePlaces', 'model' => CriminalCapturePlace::class],
            ['key' => 'pumps', 'name' => 'pumps', 'model' => Pump::class],
            ['key' => 'shoras', 'name' => 'shoras', 'model' => Shora::class],
            ['key' => 'checkpoints', 'name' => 'checkpoints', 'model' => Checkpoint::class],
            ['key' => 'governmentplaces', 'name' => 'governmentplaces', 'model' => GovernmentPlace::class],
            ['key' => 'supermarkets', 'name' => 'supermarkets', 'model' => SuperMarket::class],
            ['key' => 'parks', 'name' => 'parks', 'model' => Park::class],
            ['key' => 'complexes', 'name' => 'complexes', 'model' => Complex::class],
            ['key' => 'deplomaticplaces', 'name' => 'deplomaticplaces', 'model' => DeplomaticPlace::class],

            // Format Three
            ['key' => 'badmashanos', 'name' => 'badmashanos', 'model' => Badmashano::class],
            ['key' => 'emaratOffices', 'name' => 'emaratOffices', 'model' => EmaratOffice::class],
            ['key' => 'hostels', 'name' => 'hostels', 'model' => Hostel::class],
            ['key' => 'hotels', 'name' => 'hotels', 'model' => Hotel::class],
            ['key' => 'madrasas', 'name' => 'madrasas', 'model' => Madrasa::class],
            ['key' => 'mashkokPeople', 'name' => 'mashkokPeople', 'model' => MashkokPeople::class],
            ['key' => 'mujahedinos', 'name' => 'mujahedinos', 'model' => Mujahedino::class],
            ['key' => 'oldJahadis', 'name' => 'oldJahadis', 'model' => OldJahadi::class],
            ['key' => 'sarayanos', 'name' => 'sarayanos', 'model' => Sarayano::class],
            ['key' => 'tujarans', 'name' => 'tujarans', 'model' => Tujaran::class],
        ];

        $out = [];

        foreach ($map as $item) {
            $modelClass = $item['model'];
            $model = new $modelClass;

            $q = $modelClass::query();

            // daily filter on created_at only if the table has created_at
            $table = $model->getTable();
            if (Schema::hasColumn($table, 'created_at')) {
                $q->whereDate('created_at', $date);
            }

            $count = (int) $q->count();

            if ($count > 0) {
                $out[] = [
                    'key' => $item['key'],
                    'name' => $item['name'],
                    'count' => $count,
                ];
            }
        }

        usort($out, fn($a, $b) => $b['count'] <=> $a['count']);

        return $out;
    }
}
