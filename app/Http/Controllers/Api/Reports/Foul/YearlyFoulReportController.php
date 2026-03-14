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
use App\Models\fifthyFormats\Jawasis;

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

class YearlyFoulReportController extends Controller
{
    /**
     * Yearly Foul System Report
     * GET /api/reports/foul/yearly?year=YYYY
     *
     * NOTE:
     * - We use [start, end) (end is exclusive) everywhere for safety.
     */
    public function __invoke(Request $request)
    {
        $year = $this->parseYear($request->query('year'));
        [$start, $end, $label] = $this->parseYearRange($year);

        // 1) Resources total (by resource_saved_date)
        $resourcesTotal = Resource::query()
            ->whereDate('resource_saved_date', '>=', $start)
            ->whereDate('resource_saved_date', '<', $end)
            ->count();

        // 2) Informations total + type counts (ONLY those that exist)
        $infoBase = Information::query()
            ->whereDate('information_saved_date', '>=', $start)
            ->whereDate('information_saved_date', '<', $end);

        $informationsTotal = (clone $infoBase)->count();

        $syasiTypes = (clone $infoBase)
            ->whereNotNull('syasi_info_type')
            ->where('syasi_info_type', '!=', '')
            ->selectRaw("syasi_info_type as label, COUNT(*) as count")
            ->groupBy('syasi_info_type')
            ->get()
            ->map(fn ($r) => ['label' => $r->label, 'count' => (int) $r->count]);

        $jenayeeTypes = (clone $infoBase)
            ->whereNotNull('jenayee_info_type')
            ->where('jenayee_info_type', '!=', '')
            ->selectRaw("jenayee_info_type as label, COUNT(*) as count")
            ->groupBy('jenayee_info_type')
            ->get()
            ->map(fn ($r) => ['label' => $r->label, 'count' => (int) $r->count]);

        $informationTypes = $syasiTypes
            ->concat($jenayeeTypes)
            ->groupBy('label')
            ->map(function ($items, $label) {
                return [
                    'label' => $label,
                    'count' => (int) $items->sum('count'),
                ];
            })
            ->filter(fn ($x) => (int) $x['count'] > 0)
            ->values()
            ->sortByDesc('count')
            ->values();

        // 3) Events total + event_type grouped (ONLY those that exist)
        $eventBase = Event::query()
            ->whereDate('event_date', '>=', $start)
            ->whereDate('event_date', '<', $end);

        $eventsTotal = (clone $eventBase)->count();

        $eventTypes = (clone $eventBase)
            ->whereNotNull('event_type')
            ->where('event_type', '!=', '')
            ->selectRaw("event_type as label, COUNT(*) as count")
            ->groupBy('event_type')
            ->orderByDesc('count')
            ->get()
            ->map(fn ($r) => ['label' => $r->label, 'count' => (int) $r->count]);

        // 4) 56-format totals (yearly filter on created_at if exists)
        $formats = $this->formatCounts($start, $end);

        return response()->json([
            'meta' => [
                'report' => 'yearly',
                'year' => (int) $year,
                'label' => $label, // YYYY
                'range' => [
                    'from' => $start->toDateString(),
                    'to_exclusive' => $end->toDateString(),
                ],
            ],

            'resources' => [
                'total' => (int) $resourcesTotal,
            ],

            'informations' => [
                'total' => (int) $informationsTotal,
                'types' => $informationTypes,
            ],

            'events' => [
                'total' => (int) $eventsTotal,
                'types' => $eventTypes,
            ],

            'formats' => $formats,
        ]);
    }

    private function parseYear($year): int
    {
        $y = (int) $year;
        if ($y >= 2000 && $y <= 2100) return $y;
        return (int) now()->year;
    }

    /**
     * Build [start, endExclusive, label] for the year
     */
    private function parseYearRange(int $year): array
    {
        $start = Carbon::create($year, 1, 1)->startOfDay();
        $end = $start->copy()->addYear(); // exclusive
        $label = sprintf('%04d', $year);

        return [$start, $end, $label];
    }

    /**
     * Count records for all “formats” (models), filtered by [start, end)
     * using created_at ONLY if the table has created_at.
     * Returns only formats with count > 0:
     * [ {key, name, count}, ... ]
     */
    private function formatCounts(Carbon $start, Carbon $end): array
    {
        $map = [
           
        // FifthyFormats
        ['key' => 'fasads', 'name' => 'شر او فساد', 'model' => Fasad::class],
        ['key' => 'saeenFasads', 'name' => 'ساعین بالفساد ', 'model' => SaeenFasad::class],
        ['key' => 'heists', 'name' => 'غلاوې', 'model' => Heist::class],
        ['key' => 'captives', 'name' => '
خوشې شوي بندیان', 'model' => Captive::class],
        ['key' => 'mulkanos', 'name' => 'نمایندهگانو', 'model' => Mulkano::class],
        ['key' => 'entryExits', 'name' => 'دخولي ، خروجي او فرعي لارې', 'model' => EntryExit::class],
        ['key' => 'sarafyanos', 'name' => 'صرافان', 'model' => Sarafyano::class],
        ['key' => 'clubs', 'name' => 'کلبونه', 'model' => Clubs::class],
        ['key' => 'mahajerats', 'name' => 'مهاجرین', 'model' => Mahajerat::class],
        ['key' => 'soldiers', 'name' => 'سرتیري', 'model' => Soldier::class],

        // Format One
        ['key' => 'mawads', 'name' => 'د مخدره مواد پلورونکي', 'model' => Mawad::class],
        ['key' => 'lostmujremeens', 'name' => 'ورک شوي مجرمین', 'model' => LostMujremeen::class],
        ['key' => 'oldSoldiersHomes', 'name' => 'د پخوانیو عسکرو کورونه', 'model' => OldSoldiersHomes::class],
        ['key' => 'robbers', 'name' => 'غله', 'model' => Robbers::class],
        ['key' => 'nonResponsablePeople', 'name' => 'غیر مسوله وسله وال', 'model' => NonResponsablePeople::class],
        ['key' => 'mashkokHomes', 'name' => 'مشکوک کورونه', 'model' => MashkokHomes::class],
        ['key' => 'damagePlaces', 'name' => 'زیانمن ځایونه', 'model' => DamagePlaces::class],
        ['key' => 'damageRasanPlaces', 'name' => 'زیان رسونکي ځایونه', 'model' => DamageRasanPlaces::class],
        ['key' => 'mashkokShops', 'name' => 'مشکوک دوکانونه', 'model' => MashkokShops::class],
        ['key' => 'luchakano', 'name' => 'لوچکان', 'model' => Luchakano::class],

        // Format Two
        ['key' => 'workshops', 'name' => 'ورکشاپونه', 'model' => WorkShop::class],
        ['key' => 'hosppitals', 'name' => 'روغتونونه', 'model' => Hosppital::class],
        ['key' => 'rahnomaMamlats', 'name' => 'راهنما معاملات', 'model' => RahnomaMamlat::class],
        ['key' => 'apartments', 'name' => 'اپارتمانونه', 'model' => Apartment::class],
        ['key' => 'banks', 'name' => 'بانکونه', 'model' => Bank::class],
        ['key' => 'transportCompanies', 'name' => 'ترانسپورتي شرکتونه', 'model' => TransportCompany::class],
        ['key' => 'hadoBusStops', 'name' => 'هډې', 'model' => HadoBusStop::class],
        ['key' => 'carSalesCompanies', 'name' => 'د موټر خرڅلاو شرکتونه', 'model' => CarSalesCompany::class],
        ['key' => 'schools', 'name' => 'شونیز مراکز', 'model' => School::class],
        ['key' => 'parkings', 'name' => 'پارکینګونه', 'model' => Parking::class],

        // Format Four
        ['key' => 'wakilanos', 'name' => 'وکیلان اوملکان', 'model' => Wakilano::class],
        ['key' => 'universities', 'name' => 'پوهنتونونه', 'model' => University::class],
        ['key' => 'masjids', 'name' => 'جوماتونه', 'model' => Masjid::class],
        ['key' => 'highways', 'name' => 'لویې لارې', 'model' => Highway::class],
        ['key' => 'companies', 'name' => 'شرکتونه او فابریکې', 'model' => Company::class],
        ['key' => 'donelostplaces', 'name' => 'هغه ساحې چې غلا پکې ترسره شوي دي', 'model' => Donelostplace::class],
        ['key' => 'econemyProjects', 'name' => 'اقتصادي پروژې', 'model' => EconemyProject::class],
        ['key' => 'criminalCapturePlaces', 'name' => 'د مجرم نیولو ځایونه', 'model' => CriminalCapturePlace::class],
        ['key' => 'pumps', 'name' => 'پمپونه', 'model' => Pump::class],
        ['key' => 'shoras', 'name' => 'شوراګانې', 'model' => Shora::class],
        ['key' => 'checkpoints', 'name' => 'چک پاینتونه', 'model' => Checkpoint::class],
        ['key' => 'governmentplaces', 'name' => 'دولتې ځایونه', 'model' => GovernmentPlace::class],
        ['key' => 'supermarkets', 'name' => 'سوپرمارکېټونه', 'model' => SuperMarket::class],
        ['key' => 'parks', 'name' => 'پارکونه', 'model' => Park::class],
        ['key' => 'complexes', 'name' => 'بنسټونو او موسسو ګانو', 'model' => Complex::class],
        ['key' => 'deplomaticplaces', 'name' => 'دیپلوماتیک ځایونه', 'model' => DeplomaticPlace::class],

        // Format Three
        ['key' => 'badmashanos', 'name' => 'بدماشانو', 'model' => Badmashano::class],
        ['key' => 'emaratOffices', 'name' => 'امارتي کورونه او میلمستونونه', 'model' => EmaratOffice::class],
        ['key' => 'hostels', 'name' => 'لیلي او کرایي اطاقونه', 'model' => Hostel::class],
        ['key' => 'hotels', 'name' => 'هوټلونه', 'model' => Hotel::class],
        ['key' => 'madrasas', 'name' => 'مدرسې', 'model' => Madrasa::class],
        ['key' => 'mashkokPeople', 'name' => 'مشکوک خلک', 'model' => MashkokPeople::class],
        ['key' => 'mujahedinos', 'name' => 'مجاهدین', 'model' => Mujahedino::class],
        ['key' => 'oldJahadis', 'name' => 'پخواني جهادي قومندانان', 'model' => OldJahadi::class],
        ['key' => 'sarayanos', 'name' => 'سرایان', 'model' => Sarayano::class],
        ['key' => 'tujarans', 'name' => 'تجاران', 'model' => Tujaran::class],
        ['key' => 'jawasis', 'name' => 'جواسیس', 'model' => Jawasis::class],
                ];

        $out = [];

        foreach ($map as $item) {
            $modelClass = $item['model'];
            $model = new $modelClass;

            $q = $modelClass::query();
            $table = $model->getTable();

            // ✅ IMPORTANT: yearly range filter, NOT whereDate(created_at, $date)
            if (Schema::hasColumn($table, 'created_at')) {
                $q->where('created_at', '>=', $start)
                  ->where('created_at', '<', $end);
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

        usort($out, fn ($a, $b) => $b['count'] <=> $a['count']);

        return $out;
    }
}
