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
    public function syncActivityFromRecentRecords(): void
    {
        $since = now()->subDays(30)->toDateString();

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
                ->exists();

        $newActivity = $isActive ? 'فعال' : 'غیر فعال';

        if ($this->activity !== $newActivity) {
            $this->activity = $newActivity;
            $this->save();
        }
    }
   
}
