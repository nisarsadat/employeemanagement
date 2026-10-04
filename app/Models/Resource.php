<?php

namespace App\Models;

use App\Models\fifthyformatfour\Checkpoint;
use App\Models\fifthyformatfour\Company;
use App\Models\fifthyformatfour\Complex;
use App\Models\fifthyformatfour\CriminalCapturePlace;
use App\Models\fifthyformatfour\DeplomaticPlace;
use App\Models\fifthyformatfour\Donelostplace;
use App\Models\fifthyformatfour\EconemyProject;
use App\Models\fifthyformatfour\GovernmentPlace;
use App\Models\fifthyformatfour\Highway;
use App\Models\fifthyformatfour\Masjid;
use App\Models\fifthyformatfour\Park;
use App\Models\fifthyformatfour\Pump;
use App\Models\fifthyformatfour\Shora;
use App\Models\fifthyformatfour\SuperMarket;
use App\Models\fifthyformatfour\University;
use App\Models\fifthyformatfour\Wakilano;
use App\Models\fifthyformatone\DamagePlaces;
use App\Models\fifthyformatone\LostMujremeen;
use App\Models\fifthyformatone\Luchakano;
use App\Models\fifthyformatone\MashkokHomes;
use App\Models\fifthyformatone\MashkokShops;
use App\Models\fifthyformatone\Mawad;
use App\Models\fifthyformatone\NonResponsablePeople;
use App\Models\fifthyformatone\OldSoldiersHomes;
use App\Models\fifthyformatone\Robbers;
use App\Models\fifthyFormats\Captive;
use App\Models\fifthyFormats\Clubs;
use App\Models\fifthyFormats\EntryExit;
use App\Models\fifthyFormats\Fasad;
use App\Models\fifthyFormats\Heist;
use App\Models\fifthyFormats\Mahajerat;
use App\Models\fifthyFormats\Mulkano;
use App\Models\fifthyFormats\SaeenFasad;
use App\Models\fifthyFormats\Sarafyano;
use App\Models\fifthyFormats\Soldier;
use App\Models\fifthyformatone\DamageRasanPlaces;
use App\Models\fifthyformatstow\Apartment;
use App\Models\fifthyformatstow\Bank;
use App\Models\fifthyformatstow\CarSalesCompany;
use App\Models\fifthyformatstow\HadoBusStop;
use App\Models\fifthyformatstow\Hosppital;
use App\Models\fifthyformatstow\Parking;
use App\Models\fifthyformatstow\RahnomaMamlat;
use App\Models\fifthyformatstow\School;
use App\Models\fifthyformatstow\TransportCompany;
use App\Models\fifthyformatstow\WorkShop;

// ⭐ NEW: Fifthy Format Three models
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

use Illuminate\Database\Eloquent\Model;

class Resource extends Model
{
    protected $fillable = [
        'picture',
        // intro
        'name',
        'father_name',
        'grand_father_name',
        'nick_name',
        'code_number',
        'tazkira',

        // main address
        'main_province',
        'main_district',
        'main_village',
        'blood_group',

        // current address
        'now_province',
        'now_district',
        'now_village',
        'now_specific_place',
        'now_street_number',
        'now_home_number',
        'letry_format_name',
        // jobs
        'old_job',
        'now_job',
        'job_place',

        // status & education
        'activity',
        'halat_madani',
        'age',
        'elmi_level',
        'Province_addr',
        'faculty',
        'specific_skills',
        'train',
        'trust_percentage',

        // misc
        'resource_saved_date',
        'resource_activity_type',
        'office_employee_id',
        'resedint_code',
        'resource_cover',
        'language',
        'nation',
        'phone_number',
        'phone_number_1',
        'phone_number_2',
        'coordinates',
        'project',
        'zone',
        'hawza',
        'specefic_place',
        'note',
        'format_owner_type',
        'format_owner_id',
    ];

    // ✅ Casts (helps if you filter by month on saved date)
    protected $casts = [
        'resource_saved_date' => 'date',
    ];

    public function formatOwnerRecord()
    {
        if (!$this->format_owner_type || !$this->format_owner_id) {
            return null;
        }

        if (!class_exists($this->format_owner_type)) {
            return null;
        }

        return $this->format_owner_type::find($this->format_owner_id);
    }
    // Relations (unchanged)
    public function officeEmployee()
    {
        return $this->belongsTo(OfficeEmployee::class, 'office_employee_id');
    }

    public function events() { return $this->hasMany(Event::class); }
    public function informations() { return $this->hasMany(Information::class); }

    public function fasads() { return $this->hasMany(Fasad::class , 'resource_id','id'); }
    public function saeenFasads() { return $this->hasMany(SaeenFasad::class , 'resource_id','id'); }
    public function heists() { return $this->hasMany(Heist::class , 'resource_id','id'); }
    public function captives() { return $this->hasMany(Captive::class , 'resource_id','id'); }
    public function mulkanos() { return $this->hasMany(Mulkano::class , 'resource_id','id'); }
    public function entryExits() { return $this->hasMany(EntryExit::class , 'resource_id','id'); }
    public function sarafyanos() { return $this->hasMany(Sarafyano::class , 'resource_id','id'); }
    public function clubs() { return $this->hasMany(Clubs::class , 'resource_id','id'); }
    public function mahajerats() { return $this->hasMany(Mahajerat::class , 'resource_id','id'); }
    public function soldiers() { return $this->hasMany(Soldier::class , 'resource_id','id'); }
    public function mawads() { return $this->hasMany(Mawad::class , 'resource_id','id'); }
    public function lostmujremeens() { return $this->hasMany(LostMujremeen::class , 'resource_id','id'); }
    public function oldSoldiersHomes() { return $this->hasMany(OldSoldiersHomes::class , 'resource_id','id'); }
    public function robbers() { return $this->hasMany(Robbers::class , 'resource_id','id'); }
    public function nonResponsablePeople() { return $this->hasMany(NonResponsablePeople::class , 'resource_id','id'); }
    public function mashkokHomes() { return $this->hasMany(MashkokHomes::class , 'resource_id','id'); }
    public function damagePlaces() { return $this->hasMany(DamagePlaces::class , 'resource_id','id'); }
    public function damageRasanPlaces() { return $this->hasMany(DamageRasanPlaces::class , 'resource_id','id'); }
    public function mashkokShops() { return $this->hasMany(MashkokShops::class , 'resource_id','id'); }
    public function luchakano() { return $this->hasMany(Luchakano::class , 'resource_id','id'); }
    public function workshops() { return $this->hasMany(WorkShop::class , 'resource_id','id'); }
    public function hosppitals() { return $this->hasMany(Hosppital::class , 'resource_id','id'); }
    public function rahnomaMamlats() { return $this->hasMany(RahnomaMamlat::class , 'resource_id','id'); }
    public function apartments() { return $this->hasMany(Apartment::class , 'resource_id','id'); }
    public function banks() { return $this->hasMany(Bank::class , 'resource_id','id'); }
    public function transportCompanies() { return $this->hasMany(TransportCompany::class , 'resource_id','id'); }
    public function hadoBusStops() { return $this->hasMany(HadoBusStop::class , 'resource_id','id'); }
    public function carSalesCompanies() { return $this->hasMany(CarSalesCompany::class , 'resource_id','id'); }
    public function schools() { return $this->hasMany(School::class , 'resource_id','id'); }
    public function parkings() { return $this->hasMany(Parking::class , 'resource_id','id'); }
    public function wakilanos()
    {
        return $this->hasMany(Wakilano::class);
    }
    public function universities()
    {
        return $this->hasMany(University::class);
    }
    public function masjids()
    {
        return $this->hasMany(Masjid::class);
    }
    public function highways()
    {
        return $this->hasMany(Highway::class);
    }
    public function companies()
    {
        return $this->hasMany(Company::class);
    }
    public function donelostplaces()
    {
        return $this->hasMany(Donelostplace::class);
    }
    public function econemyProjects()
    {
        return $this->hasMany(EconemyProject::class);
    }
    public function criminalCapturePlaces()
    {
        return $this->hasMany(CriminalCapturePlace::class);
    }
    public function pumps()
    {
        return $this->hasMany(Pump::class);
    }
    public function shoras()
    {
        return $this->hasMany(Shora::class);
    }
    public function checkpoints()
    {
        return $this->hasMany(Checkpoint::class);
    }
    public function governmentplaces()
    {
        return $this->hasMany(GovernmentPlace::class);
    }
    public function supermarkets()
    {
        return $this->hasMany(SuperMarket::class);
    }
    public function parks()
    {
        return $this->hasMany(Park::class);
    }
    public function complexes()
    {
        return $this->hasMany(Complex::class);
    }
    public function deplomaticplaces()
    {
        return $this->hasMany(DeplomaticPlace::class);
    }

    // ⭐ NEW: Fifthy Format Three relations
    public function badmashanos()
    {
        return $this->hasMany(Badmashano::class, 'resource_id', 'id');
    }

    public function emaratOffices()
    {
        return $this->hasMany(EmaratOffice::class, 'resource_id', 'id');
    }

    public function hostels()
    {
        return $this->hasMany(Hostel::class, 'resource_id', 'id');
    }

    public function hotels()
    {
        return $this->hasMany(Hotel::class, 'resource_id', 'id');
    }

    public function madrasas()
    {
        return $this->hasMany(Madrasa::class, 'resource_id', 'id');
    }

    public function mashkokPeople()
    {
        return $this->hasMany(MashkokPeople::class, 'resource_id', 'id');
    }

    public function mujahedinos()
    {
        return $this->hasMany(Mujahedino::class, 'resource_id', 'id');
    }

    public function oldJahadis()
    {
        return $this->hasMany(OldJahadi::class, 'resource_id', 'id');
    }

    public function sarayanos()
    {
        return $this->hasMany(Sarayano::class, 'resource_id', 'id');
    }

    public function tujarans()
    {
        return $this->hasMany(Tujaran::class, 'resource_id', 'id');
    }

    public static function fiftySixFormatSources(): array
{

    return [
        // ---------------- Base ----------------
        ['relation' => 'fasads', 'table' => 'fasads', 'type' => 'fasad', 'label' => 'شر او فساد', 'path' => '/app/fasads'],
        ['relation' => 'saeenFasads', 'table' => 'saeen_fasads', 'type' => 'saeen_fasad', 'label' => 'ساعین بالفساد', 'path' => '/app/saeenfasad'],
        ['relation' => 'heists', 'table' => 'heists', 'type' => 'heist', 'label' => 'سرقت کوونکی', 'path' => '/app/heist'],
        ['relation' => 'captives', 'table' => 'captives', 'type' => 'captive', 'label' => 'راخوشي شوي بندي', 'path' => '/app/captives'],
        ['relation' => 'mulkanos', 'table' => 'mulkanos', 'type' => 'mulkano', 'label' => 'د کوڅې نماینده', 'path' => '/app/mulkanos'],
        ['relation' => 'entryExits', 'table' => 'entry_exits', 'type' => 'entry_exit', 'label' => 'د ښار اړوند دخولي/خروجي دروازه او فرعي لاره', 'path' => '/app/entry-exits'],
        ['relation' => 'sarafyanos', 'table' => 'sarafyanos', 'type' => 'sarafyano', 'label' => 'صرافي', 'path' => '/app/sarafyanos'],
        ['relation' => 'clubs', 'table' => 'clubs', 'type' => 'clubs', 'label' => 'ورزشي کلپ', 'path' => '/app/clubs'],
        ['relation' => 'mahajerats', 'table' => 'mahajerats', 'type' => 'mahajerat', 'label' => 'نوی راغلی کور', 'path' => '/app/mahajerats'],
        ['relation' => 'soldiers', 'table' => 'soldiers', 'type' => 'soldier', 'label' => 'پخوانی چارواکی', 'path' => '/app/soldiers'],

        // ---------------- Format One ----------------
        ['relation' => 'mawads', 'table' => 'mawads', 'type' => 'mawad', 'label' => 'د نشه یی توکو پلورونکی', 'path' => '/app/mawads'],
        ['relation' => 'lostmujremeens', 'table' => 'lost_mujremeens', 'type' => 'lost_mujremeen', 'label' => 'نادرکه شوي مجرم', 'path' => '/app/lostmujremeens'],
        ['relation' => 'oldSoldiersHomes', 'table' => 'old_soldiers_homes', 'type' => 'old_soldiers_homes', 'label' => 'د پخوانی چارواکی کور', 'path' => '/app/oldsoldiershomes'],
        ['relation' => 'robbers', 'table' => 'robbers', 'type' => 'robbers', 'label' => 'اختطاف ګر', 'path' => '/app/robbers'],
        ['relation' => 'nonResponsablePeople', 'table' => 'non_responsable_people', 'type' => 'non_responsable_people', 'label' => 'غیر مسؤوله وسله وال', 'path' => '/app/nonresponsablepeople'],
        ['relation' => 'mashkokHomes', 'table' => 'mashkok_homes', 'type' => 'mashkok_homes', 'label' => 'مشکوک کور', 'path' => '/app/mashkokhomes'],
        ['relation' => 'damagePlaces', 'table' => 'damage_places', 'type' => 'damage_places', 'label' => 'آسیب پذیره ساحې', 'path' => '/app/damageplaces'],
        ['relation' => 'damageRasanPlaces', 'table' => 'damage_rasan_places', 'type' => 'damage_rasan_places', 'label' => 'آسیب رسانه ساحه', 'path' => '/app/damagerasanplaces'],
        ['relation' => 'mashkokShops', 'table' => 'mashkok_shops', 'type' => 'mashkok_shops', 'label' => 'مشکوک دوکان', 'path' => '/app/mashkokshops'],
        ['relation' => 'luchakano', 'table' => 'luchakanos', 'type' => 'luchakanos', 'label' => 'لوچک او بی بند و باره فرد', 'path' => '/app/luchakanos'],

        // ---------------- Format Two ----------------
        ['relation' => 'schools', 'table' => 'schools', 'type' => 'schools', 'label' => 'ښونځی او روزنیز مرکز', 'path' => '/app/schools'],
        ['relation' => 'hadoBusStops', 'table' => 'hado_bus_stops', 'type' => 'hado_bus_stops', 'label' => 'هډه', 'path' => '/app/hadobusstops'],
        ['relation' => 'transportCompanies', 'table' => 'transport_companies', 'type' => 'transport_companies', 'label' => 'ترانسپورتي شرکت', 'path' => '/app/transportcompanies'],
        ['relation' => 'carSalesCompanies', 'table' => 'car_sales_companies', 'type' => 'car_sales_companies', 'label' => 'موټر پلورونځی', 'path' => '/app/carsalescompanies'],
        ['relation' => 'parkings', 'table' => 'parkings', 'type' => 'parkings', 'label' => 'موټرو تمځای(پارکینګ)', 'path' => '/app/parkings'],
        ['relation' => 'banks', 'table' => 'banks', 'type' => 'banks', 'label' => 'بانک', 'path' => '/app/banks'],
        ['relation' => 'apartments', 'table' => 'apartments', 'type' => 'apartments', 'label' => 'رهایشي بلاک', 'path' => '/app/apartments'],
        ['relation' => 'rahnomaMamlats', 'table' => 'rahnoma_mamlats', 'type' => 'rahnoma_mamlats', 'label' => 'د معاملاتو رهنما', 'path' => '/app/rahnomamamlats'],
        ['relation' => 'hosppitals', 'table' => 'hosppitals', 'type' => 'hosppitals', 'label' => 'روغتون او صحي مرکز', 'path' => '/app/hosppitals'],
        ['relation' => 'workshops', 'table' => 'work_shops', 'type' => 'work_shops', 'label' => 'مستري خانه', 'path' => '/app/workshops'],

        // ---------------- Format Three ----------------
        ['relation' => 'badmashanos', 'table' => 'badmashanos', 'type' => 'badmashanos', 'label' => 'زورواک او ولکه دار', 'path' => '/app/badmashanos'],
        ['relation' => 'emaratOffices', 'table' => 'emarat_offices', 'type' => 'emarat_offices', 'label' => 'امارتي مسوول کور او میلمستون', 'path' => '/app/emaratoffices'],
        ['relation' => 'hostels', 'table' => 'hostels', 'type' => 'hostels', 'label' => 'لیله او کرایي اطاق', 'path' => '/app/hostels'],
        ['relation' => 'hotels', 'table' => 'hotels', 'type' => 'hotels', 'label' => 'میلمستون ،مسافرخانه ،هوټل او رسټوردانټ', 'path' => '/app/hotels'],
        ['relation' => 'madrasas', 'table' => 'madrasas', 'type' => 'madrasas', 'label' => 'دینې مدرسه', 'path' => '/app/madrasas'],
        ['relation' => 'mashkokPeople', 'table' => 'mashkok_people', 'type' => 'mashkok_people', 'label' => 'مشکوک فرد', 'path' => '/app/mashkokpeoples'],
        ['relation' => 'mujahedinos', 'table' => 'mujahedinos', 'type' => 'mujahedinos', 'label' => 'مجاهد', 'path' => '/app/mujahedinos'],
        ['relation' => 'oldJahadis', 'table' => 'old_jahadis', 'type' => 'old_jahadis', 'label' => 'پخوانی جهادي قومندان', 'path' => '/app/oldjahadis'],
        ['relation' => 'sarayanos', 'table' => 'sarayanos', 'type' => 'sarayanos', 'label' => 'سرای', 'path' => '/app/sarayanos'],
        ['relation' => 'tujarans', 'table' => 'tujarans', 'type' => 'tujarans', 'label' => 'عالم ، متنفذ ، تجار او عالي رتبه شخص', 'path' => '/app/tujarans'],

        // ---------------- Format Four ----------------
        ['relation' => 'checkpoints', 'table' => 'checkpoints', 'type' => 'checkpoints', 'label' => 'چک پواینټ او پوسته', 'path' => '/app/checkpoints'],
        ['relation' => 'masjids', 'table' => 'masjids', 'type' => 'masjids', 'label' => 'مسجد، خاناقا او تکیه خانه', 'path' => '/app/masjids'],
        ['relation' => 'highways', 'table' => 'highways', 'type' => 'highways', 'label' => 'څلور لارې او درې لارې', 'path' => '/app/highways'],
        ['relation' => 'companies', 'table' => 'companies', 'type' => 'companies', 'label' => 'فابریکه او شرکت', 'path' => '/app/companies'],
        ['relation' => 'donelostplaces', 'table' => 'done_lost_places', 'type' => 'done_lost_places', 'label' => 'هغه ساحه چې اختطاف پکې ترسره شوی', 'path' => '/app/donelostplaces'],
        ['relation' => 'econemyProjects', 'table' => 'econemy_projects', 'type' => 'econemy_projects', 'label' => 'مهم تاسیسات او اقتصادي پرو‌ژه', 'path' => '/app/econemyprojects'],
        ['relation' => 'criminalCapturePlaces', 'table' => 'criminal_capture_places', 'type' => 'criminal_capture_places', 'label' => 'هغې ساحه چې مجرم پکې نیول شوی', 'path' => '/app/criminalcaptureplaces'],
        ['relation' => 'pumps', 'table' => 'pumps', 'type' => 'pumps', 'label' => 'پمپ', 'path' => '/app/pumps'],
        ['relation' => 'shoras', 'table' => 'shoras', 'type' => 'shoras', 'label' => 'محلي شورا', 'path' => '/app/shoras'],
        ['relation' => 'governmentplaces', 'table' => 'governmentplaces', 'type' => 'governmentplaces', 'label' => 'امارتي ځاي', 'path' => '/app/governmentplaces'],
        ['relation' => 'supermarkets', 'table' => 'supermarkets', 'type' => 'supermarkets', 'label' => 'تجارتي مرکز او سوپر مارکیټ', 'path' => '/app/supermarkets'],
        ['relation' => 'parks', 'table' => 'parks', 'type' => 'parks', 'label' => 'تفریحي پارک او سیاحتي سیمه', 'path' => '/app/parks'],
        ['relation' => 'complexes', 'table' => 'complexes', 'type' => 'complexes', 'label' => 'داخلي موسسه او بنسټ', 'path' => '/app/complexes'],
        ['relation' => 'deplomaticplaces', 'table' => 'deplomaticplaces', 'type' => 'deplomaticplaces', 'label' => 'دیپلوماتیک ځای،خارجي موسسه،دبهرنیانو د اوسیدو او کار ځای', 'path' => '/app/deplomaticplaces'],
        ['relation' => 'universities', 'table' => 'universities', 'type' => 'universities', 'label' => 'پوهنتونون ، دلوړو زده کړو موسسه او انسټیټیوټ', 'path' => '/app/universities'],
        ['relation' => 'wakilanos', 'table' => 'wakilanos', 'type' => 'wakilanos', 'label' => 'وکیل او ملک', 'path' => '/app/wakilanos'],
    ];

}

public function fiftySixFormatsTotalCount(): int
{
    return collect(self::fiftySixFormatSources())
        ->sum(function ($source) {
            $relation = $source['relation'];

            return method_exists($this, $relation)
                ? $this->{$relation}()->count()
                : 0;
        });
}

   protected static function booted()
{
    static::saved(function ($resource) {
        if ($resource->office_employee_id) {
            $resource->officeEmployee?->syncActivityFromResources();
        }
    });

    static::deleted(function ($resource) {
        if ($resource->office_employee_id) {
            OfficeEmployee::find($resource->office_employee_id)?->syncActivityFromResources();
        }
    });
}
    public function finances()
    {
        return $this->hasMany(Finance::class, 'resource_id', 'id');
    }
    public function KhayratReports()
    {
        return $this->hasMany(KhayratReport::class);
    }
    /**
     * Number of days used as the activity window.
     */
    public const ACTIVITY_WINDOW_DAYS = 30;

    /**
     * Minimum khayrat reports inside the activity window required for a
     * resource to count as active.
     */
    public const MIN_KHAYRAT_REPORTS_FOR_ACTIVE = 40;

    public function syncActivityFromRecentRecords(): void
    {
        $since = now()->subDays(self::ACTIVITY_WINDOW_DAYS)->toDateString();

        $isActive =
            $this->events()
                ->whereNotNull('event_date')
                ->whereDate('event_date', '>=', $since)
                ->exists()
            ||
            $this->informations()
                ->whereNotNull('information_saved_date')
                ->whereDate('information_saved_date', '>=', $since)
                ->exists()
            ||
            $this->khayratReports()
                ->whereNotNull('saved_date')
                ->whereDate('saved_date', '>=', $since)
                ->count() >= self::MIN_KHAYRAT_REPORTS_FOR_ACTIVE;

        $newActivity = $isActive ? 'فعال' : 'غیر فعال';

        if ($this->activity !== $newActivity) {
            $this->activity = $newActivity;
            $this->save();
        }
    }
   
}
