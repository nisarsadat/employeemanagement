<?php

namespace App\Models;

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

}
