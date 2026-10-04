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
use App\Services\Reports\FoulActivityRows;

class YearlyFoulReportController extends Controller
{
    /**
     * GET /api/reports/foul/yearly?year=1403
     * year is Hijri Shamsi (Jalali)
     */
    public function __invoke(Request $request)
    {
        $year = $this->parseShamsiYear($request->query('year'));
        [$start, $end, $label] = $this->parseShamsiYearRange($year);

        // 1) Resources total
        $resourcesTotal = Resource::query()
            ->whereDate('resource_saved_date', '>=', $start->toDateString())
            ->whereDate('resource_saved_date', '<', $end->toDateString())
            ->count();

        // 2) Informations total + type counts
        $infoBase = Information::query()
            ->whereDate('information_saved_date', '>=', $start->toDateString())
            ->whereDate('information_saved_date', '<', $end->toDateString());

        $informationsTotal = (clone $infoBase)->count();

        $syasiTypes = (clone $infoBase)
            ->whereNotNull('syasi_info_type')
            ->where('syasi_info_type', '!=', '')
            ->selectRaw("syasi_info_type as label, COUNT(*) as count")
            ->groupBy('syasi_info_type')
            ->get()
            ->map(fn ($r) => [
                'label' => $r->label,
                'count' => (int) $r->count,
            ]);

        $jenayeeTypes = (clone $infoBase)
            ->whereNotNull('jenayee_info_type')
            ->where('jenayee_info_type', '!=', '')
            ->selectRaw("jenayee_info_type as label, COUNT(*) as count")
            ->groupBy('jenayee_info_type')
            ->get()
            ->map(fn ($r) => [
                'label' => $r->label,
                'count' => (int) $r->count,
            ]);

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

        // 3) Events total + types
        $eventBase = Event::query()
            ->whereDate('event_date', '>=', $start->toDateString())
            ->whereDate('event_date', '<', $end->toDateString());

        $eventsTotal = (clone $eventBase)->count();

        $eventTypes = (clone $eventBase)
            ->whereNotNull('event_type')
            ->where('event_type', '!=', '')
            ->selectRaw("event_type as label, COUNT(*) as count")
            ->groupBy('event_type')
            ->orderByDesc('count')
            ->get()
            ->map(fn ($r) => [
                'label' => $r->label,
                'count' => (int) $r->count,
            ]);

        // 4) 50-format totals
        $formats = $this->formatCounts($start, $end);
        $activity = FoulActivityRows::between($start, $end);

        return response()->json([
            'meta' => [
                'report' => 'yearly',
                'calendar' => 'jalali',
                'year' => (int) $year,
                'label' => $label,
                'range' => [
                    'jalali_from' => "{$year}/01/01",
                    'jalali_to_exclusive' => ($year + 1) . "/01/01",
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
            'meetings' => $activity['meetings'],
            'security_assessments' => $activity['security_assessments'],
        ]);
    }

    /**
     * GET /api/reports/foul/yearly/date-options
     * Returns Hijri Shamsi years for yearly filter select
     */
    public function dateOptions()
    {
        $dates = [];

        // main tables
        $dates[] = Resource::query()->whereNotNull('resource_saved_date')->min('resource_saved_date');
        $dates[] = Resource::query()->whereNotNull('resource_saved_date')->max('resource_saved_date');

        $dates[] = Information::query()->whereNotNull('information_saved_date')->min('information_saved_date');
        $dates[] = Information::query()->whereNotNull('information_saved_date')->max('information_saved_date');

        $dates[] = Event::query()->whereNotNull('event_date')->min('event_date');
        $dates[] = Event::query()->whereNotNull('event_date')->max('event_date');

        // 50 format tables via created_at
        foreach ($this->formatModelMap() as $item) {
            $modelClass = $item['model'];
            $model = new $modelClass;
            $table = $model->getTable();

            if (Schema::hasColumn($table, 'created_at')) {
                $dates[] = $modelClass::query()->whereNotNull('created_at')->min('created_at');
                $dates[] = $modelClass::query()->whereNotNull('created_at')->max('created_at');
            }
        }

        $dates = array_values(array_filter($dates));

        if (empty($dates)) {
            $currentJalaliYear = (int) $this->gregorianToJalali(
                (int) now()->format('Y'),
                (int) now()->format('m'),
                (int) now()->format('d')
            )[0];

            return response()->json([
                'calendar' => 'jalali',
                'current_year' => $currentJalaliYear,
                'years' => [$currentJalaliYear],
            ]);
        }

        $minDate = Carbon::parse(min($dates));
        $maxDate = Carbon::parse(max($dates));

        [$minJy] = $this->gregorianToJalali(
            (int) $minDate->format('Y'),
            (int) $minDate->format('m'),
            (int) $minDate->format('d')
        );

        [$maxJy] = $this->gregorianToJalali(
            (int) $maxDate->format('Y'),
            (int) $maxDate->format('m'),
            (int) $maxDate->format('d')
        );

        $years = [];
        for ($y = $maxJy; $y >= $minJy; $y--) {
            $years[] = (int) $y;
        }

        $currentJalaliYear = (int) $this->gregorianToJalali(
            (int) now()->format('Y'),
            (int) now()->format('m'),
            (int) now()->format('d')
        )[0];

        return response()->json([
            'calendar' => 'jalali',
            'current_year' => $currentJalaliYear,
            'years' => $years,
        ]);
    }

    private function parseShamsiYear($year): int
    {
        $y = (int) $year;

        if ($y >= 1300 && $y <= 1600) {
            return $y;
        }

        [$jy] = $this->gregorianToJalali(
            (int) now()->format('Y'),
            (int) now()->format('m'),
            (int) now()->format('d')
        );

        return (int) $jy;
    }

    /**
     * Returns [Carbon $startGregorian, Carbon $endExclusiveGregorian, string $label]
     * Example for 1403:
     * start = Gregorian date of 1403/01/01
     * end   = Gregorian date of 1404/01/01
     */
    private function parseShamsiYearRange(int $year): array
    {
        [$gy1, $gm1, $gd1] = $this->jalaliToGregorian($year, 1, 1);
        [$gy2, $gm2, $gd2] = $this->jalaliToGregorian($year + 1, 1, 1);

        $start = Carbon::create($gy1, $gm1, $gd1)->startOfDay();
        $end = Carbon::create($gy2, $gm2, $gd2)->startOfDay(); // exclusive
        $label = (string) $year;

        return [$start, $end, $label];
    }

    private function formatCounts(Carbon $start, Carbon $end): array
    {
        $map = $this->formatModelMap();
        $out = [];

        foreach ($map as $item) {
            $modelClass = $item['model'];
            $model = new $modelClass;

            $q = $modelClass::query();
            $table = $model->getTable();

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

    private function formatModelMap(): array
    {
        return [
            // FifthyFormats
            ['key' => 'fasads', 'name' => 'شر او فساد', 'model' => Fasad::class],
            ['key' => 'saeenFasads', 'name' => 'ساعین بالفساد', 'model' => SaeenFasad::class],
            ['key' => 'heists', 'name' => 'غلاوې', 'model' => Heist::class],
            ['key' => 'captives', 'name' => 'خوشې شوي بندیان', 'model' => Captive::class],
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
            ['key' => 'robbers', 'name' => 'اختطاف ګران', 'model' => Robbers::class],
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
    }

    /**
     * Jalali -> Gregorian
     * returns [gy, gm, gd]
     */
    private function jalaliToGregorian(int $jy, int $jm, int $jd): array
    {
        $jy += 1595;
        $days = -355668 + (365 * $jy) + intdiv($jy, 33) * 8 + intdiv((($jy % 33) + 3), 4) + $jd;

        if ($jm < 7) {
            $days += ($jm - 1) * 31;
        } else {
            $days += (($jm - 7) * 30) + 186;
        }

        $gy = 400 * intdiv($days, 146097);
        $days %= 146097;

        if ($days > 36524) {
            $gy += 100 * intdiv(--$days, 36524);
            $days %= 36524;

            if ($days >= 365) {
                $days++;
            }
        }

        $gy += 4 * intdiv($days, 1461);
        $days %= 1461;

        if ($days > 365) {
            $gy += intdiv(($days - 1), 365);
            $days = ($days - 1) % 365;
        }

        $gd = $days + 1;

        $sal_a = [
            0,
            31,
            (($gy % 4 == 0 && $gy % 100 != 0) || ($gy % 400 == 0)) ? 29 : 28,
            31,
            30,
            31,
            30,
            31,
            31,
            30,
            31,
            30,
            31
        ];

        $gm = 0;
        for ($gm = 1; $gm <= 12 && $gd > $sal_a[$gm]; $gm++) {
            $gd -= $sal_a[$gm];
        }

        return [$gy, $gm, $gd];
    }

    /**
     * Gregorian -> Jalali
     * returns [jy, jm, jd]
     */
    private function gregorianToJalali(int $gy, int $gm, int $gd): array
    {
        $g_d_m = [0,31,59,90,120,151,181,212,243,273,304,334];
        $jy = ($gy <= 1600) ? 0 : 979;
        $gy -= ($gy <= 1600) ? 621 : 1600;
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
}