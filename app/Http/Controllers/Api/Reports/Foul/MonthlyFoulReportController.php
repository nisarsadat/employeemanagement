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

class MonthlyFoulReportController extends Controller
{
    /**
     * Monthly Foul System Report (range-based)
     *
     * ✅ NEW:
     * GET /api/reports/foul/monthly?from=YYYY-MM-DD&to=YYYY-MM-DD
     *
     * ✅ Backward compatible:
     * GET /api/reports/foul/monthly?month=YYYY-MM
     */
    public function __invoke(Request $request)
    {
        [$start, $endExclusive, $meta] = $this->parseRange($request);

        // 1) Resources total (by resource_saved_date)
        $resourcesTotal = Resource::query()
            ->whereDate('resource_saved_date', '>=', $start->toDateString())
            ->whereDate('resource_saved_date', '<', $endExclusive->toDateString())
            ->count();

        // 2) Informations total + type counts
        $infoBase = Information::query()
            ->whereDate('information_saved_date', '>=', $start->toDateString())
            ->whereDate('information_saved_date', '<', $endExclusive->toDateString());

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

        // 3) Events total + event_type grouped
        $eventBase = Event::query()
            ->whereDate('event_date', '>=', $start->toDateString())
            ->whereDate('event_date', '<', $endExclusive->toDateString());

        $eventsTotal = (clone $eventBase)->count();

        $eventTypes = (clone $eventBase)
            ->whereNotNull('event_type')
            ->where('event_type', '!=', '')
            ->selectRaw("event_type as label, COUNT(*) as count")
            ->groupBy('event_type')
            ->orderByDesc('count')
            ->get()
            ->map(fn ($r) => ['label' => $r->label, 'count' => (int) $r->count]);

        // 4) Formats totals (filter created_at if exists)
        $formats = $this->formatCounts($start, $endExclusive);

        return response()->json([
            'meta' => [
                'report' => 'monthly',
                'mode' => $meta['mode'], // range|month
                'month' => $meta['month'], // YYYY-MM if available else null
                'range' => [
                    'from' => $start->toDateString(),
                    'to_exclusive' => $endExclusive->toDateString(),
                ],
                'query' => $meta['query'], // echo what was used
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
                'types' => $eventTypes,
            ],

            'formats' => $formats,
        ]);
    }

    /**
     * ✅ NEW: Parse range from request:
     * - Prefer from/to (YYYY-MM-DD)
     * - Fallback to month=YYYY-MM
     * - Default current month
     *
     * Returns:
     * [start(Carbon startOfDay), endExclusive(Carbon startOfNextDay), meta(array)]
     */
    private function parseRange(Request $request): array
    {
        $from = trim((string) $request->query('from', ''));
        $to   = trim((string) $request->query('to', ''));
        $month = trim((string) $request->query('month', ''));

        // Default: current month
        $start = Carbon::now()->startOfMonth()->startOfDay();
        $endExclusive = $start->copy()->addMonthNoOverflow()->startOfDay();

        $meta = [
            'mode' => 'range',
            'month' => null,
            'query' => [],
        ];

        // ✅ If from/to provided => use them
        if ($from !== '' || $to !== '') {
            try {
                $startParsed = $from !== '' ? Carbon::parse($from)->startOfDay() : $start->copy();
                $toParsed = $to !== '' ? Carbon::parse($to)->startOfDay() : $startParsed->copy();

                // ensure order
                if ($startParsed->gt($toParsed)) {
                    [$startParsed, $toParsed] = [$toParsed, $startParsed];
                }

                $start = $startParsed;
                $endExclusive = $toParsed->copy()->addDay()->startOfDay(); // ✅ inclusive "to"

                $meta['mode'] = 'range';
                $meta['month'] = ($start->format('Y-m') === $toParsed->format('Y-m')) ? $start->format('Y-m') : null;
                $meta['query'] = ['from' => $startParsed->toDateString(), 'to' => $toParsed->toDateString()];

                return [$start, $endExclusive, $meta];
            } catch (\Throwable $e) {
                // fallback to month/current below
            }
        }

        // ✅ Backward compatibility: month=YYYY-MM
        if ($month !== '') {
            try {
                $base = Carbon::parse($month . '-01')->startOfMonth()->startOfDay();
                $start = $base;
                $endExclusive = $start->copy()->addMonthNoOverflow()->startOfDay();

                $meta['mode'] = 'month';
                $meta['month'] = $start->format('Y-m');
                $meta['query'] = ['month' => $meta['month']];

                return [$start, $endExclusive, $meta];
            } catch (\Throwable $e) {
                // fallback to current month
            }
        }

        // current month fallback
        $meta['mode'] = 'month';
        $meta['month'] = $start->format('Y-m');
        $meta['query'] = ['month' => $meta['month']];

        return [$start, $endExclusive, $meta];
    }

    /**
     * Count records for all “formats”.
     * ✅ Filters by created_at only if the column exists.
     *
     * NOTE:
     * We use datetime range:
     * created_at >= $start AND created_at < $endExclusive
     */
    private function formatCounts(Carbon $start, Carbon $endExclusive): array
    {
        $map = [
            // FifthyFormats
            ['key' => 'fasads', 'name' => 'فسادونه', 'model' => Fasad::class],
            ['key' => 'saeenFasads', 'name' => 'ساعین بالفساد ', 'model' => SaeenFasad::class],
            ['key' => 'heists', 'name' => 'غلاوې', 'model' => Heist::class],
            ['key' => 'captives', 'name' => 'خوشې شوي بندیان', 'model' => Captive::class],
            ['key' => 'mulkanos', 'name' => 'نمایندهگانو', 'model' => Mulkano::class],
            ['key' => 'entryExits', 'name' => 'داخل او خارج', 'model' => EntryExit::class],
            ['key' => 'sarafyanos', 'name' => 'صرافان', 'model' => Sarafyano::class],
            ['key' => 'clubs', 'name' => 'کلبونه', 'model' => Clubs::class],
            ['key' => 'mahajerats', 'name' => 'مهاجرین', 'model' => Mahajerat::class],
            ['key' => 'soldiers', 'name' => 'سرتیري', 'model' => Soldier::class],

            // Format One
            ['key' => 'mawads', 'name' => 'مواد', 'model' => Mawad::class],
            ['key' => 'lostmujremeens', 'name' => 'ورک مجرمین', 'model' => LostMujremeen::class],
            ['key' => 'oldSoldiersHomes', 'name' => 'د پخوانیو عسکرو کورونه', 'model' => OldSoldiersHomes::class],
            ['key' => 'robbers', 'name' => 'غله', 'model' => Robbers::class],
            ['key' => 'nonResponsablePeople', 'name' => 'بې مسؤله خلک', 'model' => NonResponsablePeople::class],
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
            ['key' => 'hadoBusStops', 'name' => 'بس تمځایونه', 'model' => HadoBusStop::class],
            ['key' => 'carSalesCompanies', 'name' => 'د موټر خرڅلاو شرکتونه', 'model' => CarSalesCompany::class],
            ['key' => 'schools', 'name' => 'ښوونځي', 'model' => School::class],
            ['key' => 'parkings', 'name' => 'پارکینګونه', 'model' => Parking::class],

            // Format Four
            ['key' => 'wakilanos', 'name' => 'وکیلان', 'model' => Wakilano::class],
            ['key' => 'universities', 'name' => 'پوهنتونونه', 'model' => University::class],
            ['key' => 'masjids', 'name' => 'جوماتونه', 'model' => Masjid::class],
            ['key' => 'highways', 'name' => 'لویې لارې', 'model' => Highway::class],
            ['key' => 'companies', 'name' => 'شرکتونه', 'model' => Company::class],
            ['key' => 'donelostplaces', 'name' => 'ورک شوي ځایونه', 'model' => Donelostplace::class],
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
            ['key' => 'emaratOffices', 'name' => 'امارت دفترونه', 'model' => EmaratOffice::class],
            ['key' => 'hostels', 'name' => 'لیلي او کرایي اطاقونه', 'model' => Hostel::class],
            ['key' => 'hotels', 'name' => 'هوټلونه', 'model' => Hotel::class],
            ['key' => 'madrasas', 'name' => 'مدرسې', 'model' => Madrasa::class],
            ['key' => 'mashkokPeople', 'name' => 'مشکوک خلک', 'model' => MashkokPeople::class],
            ['key' => 'mujahedinos', 'name' => 'مجاهدین', 'model' => Mujahedino::class],
            ['key' => 'oldJahadis', 'name' => 'پخواني جهادي قومندانان', 'model' => OldJahadi::class],
            ['key' => 'sarayanos', 'name' => 'سرایان', 'model' => Sarayano::class],
            ['key' => 'tujarans', 'name' => 'تجاران', 'model' => Tujaran::class],

            // Jawasis
            ['key' => 'jawasis', 'name' => 'جواسیس', 'model' => Jawasis::class],
        ];

        $out = [];

        foreach ($map as $item) {
            $modelClass = $item['model'];
            $model = new $modelClass;

            $q = $modelClass::query();
            $table = $model->getTable();

            if (Schema::hasColumn($table, 'created_at')) {
                $q->where('created_at', '>=', $start)
                  ->where('created_at', '<', $endExclusive);
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
