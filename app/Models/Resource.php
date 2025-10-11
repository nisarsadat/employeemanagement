<?php

namespace App\Models;

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
}
