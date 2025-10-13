<?php

namespace App\Models;

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
use Illuminate\Database\Eloquent\Model;

class Resource extends Model
{
    protected $fillable = [
        'picture',
        'name',
        'father_name',
        'grand_father_name',
        'nickname',
        'tazkira',
        'code_number',
        'main_province',
        'main_district',
        'main_village',
        'now_province',
        'now_district',
        'now_village',
        'old_job',
        'new_job',
        'job_place',
        'zone',
        'hawza',
        'specific_place',
        'phone_number',
        'activity',
        'halat_madani',
        'age',
        'study_status',
        'resource_saved_date',
        'resource_activity_type',
        'resource_cover',
        'language',
        'nation',
        'project',
        'resedint_id',
        'officeemployee_id',
        'notes',
    ];

    public function resedint()
    {
        return $this->belongsTo(\App\Models\Resedint::class);
    }
    public function officeEmployee()
    {
        return $this->belongsTo(\App\Models\OfficeEmployee::class, 'officeemployee_id');
    }
    public function events()
    {
        return $this->hasMany(Event::class);
    }
    public function fasads()
    {
        return $this->hasMany(Fasad::class , 'resource_id', 'id');
    }
    public function saeenFasads()
    {
        return $this->hasMany(SaeenFasad::class  , 'resource_id', 'id');
    }
    public function heists()
    {
        return $this->hasMany(Heist::class , 'resource_id', 'id');
    }
    public function captives()
    {
        return $this->hasMany(Captive::class , 'resource_id', 'id');
    }
    public function mulkanos()
    {
        return $this->hasMany(Mulkano::class , 'resource_id', 'id');
    }
    public function entryExits()
    {
        return $this->hasMany(EntryExit::class , 'resource_id', 'id');
    }
    public function sarafyanos()
    {
        return $this->hasMany(Sarafyano::class , 'resource_id', 'id');
    }
    public function clubs()
    {
        return $this->hasMany(Clubs::class , 'resource_id', 'id');
    }
    public function mahajerats()
    {
        return $this->hasMany(Mahajerat::class , 'resource_id', 'id');
    }
    public function soldiers()
    {
        return $this->hasMany(Soldier::class , 'resource_id', 'id');
    }
    public function mawads()
    {
        return $this->hasMany(Mawad::class , 'resource_id' , 'id');
    }
    public function lostmujremeens()
    {
        return $this->hasMany(LostMujremeen::class , 'resource_id' , 'id');
    }
    public function oldSoldiersHomes()
    {
        return $this->hasMany(OldSoldiersHomes::class , 'resource_id' , 'id');
    }
    public function robbers()
    {
        return $this->hasMany(Robbers::class , 'resource_id' , 'id');
    }
    public function nonResponsablePeople()
    {
        return $this->hasMany(NonResponsablePeople::class , 'resource_id' , 'id');
    }
    public function mashkokHomes()
    {
        return $this->hasMany(MashkokHomes::class , 'resource_id' , 'id');
    }
    public function damagePlaces()
    {
        return $this->hasMany(DamagePlaces::class , 'resource_id' , 'id');
    }
    public function damageRasanPlaces()
    {
        return $this->hasMany(DamageRasanPlaces::class , 'resource_id' , 'id');
    }
    public function mashkokShops()
    {
        return $this->hasMany(MashkokShops::class , 'resource_id' , 'id');
    }
    public function luchakano()
    {
        return $this->hasMany(Luchakano::class , 'resource_id' , 'id');
    }
    public function workshops()
    {
        return $this->hasMany(WorkShop::class , 'resource_id' , 'id');
    }

    public function hosppitals()
    {
        return $this->hasMany(Hosppital::class , 'resource_id' , 'id');
    }

    public function rahnomaMamlats()
    {
        return $this->hasMany(RahnomaMamlat::class , 'resource_id' , 'id');
    }

    public function apartments()
    {
        return $this->hasMany(Apartment::class , 'resource_id' , 'id');
    }

    public function banks()
    {
        return $this->hasMany(Bank::class , 'resource_id' , 'id');
    }

    public function transportCompanies()
    {
        return $this->hasMany(TransportCompany::class , 'resource_id' , 'id');
    }

    public function hadoBusStops()
    {
        return $this->hasMany(HadoBusStop::class , 'resource_id' , 'id');
    }

    public function carSalesCompanies()
    {
        return $this->hasMany(CarSalesCompany::class , 'resource_id' , 'id');
    }

    public function schools()
    {
        return $this->hasMany(School::class , 'resource_id' , 'id');
    }

    public function parkings()
    {
        return $this->hasMany(Parking::class , 'resource_id' , 'id');
    }






}
