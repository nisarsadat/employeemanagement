<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OfficeEmployee extends Model
{
    use HasFactory;

    protected $fillable = [
        'picture',
        'name',
        'father_name',
        'nickname',
        'main_province',
        'main_district',
        'main_village',
        'now_province',
        'now_district',
        'now_village',
        'releg_study',
        'new_study',
        'phone_number',
        'tazkira',
        'code_number',
        'apply_date',
        'join_date',
        'left_date',
        'notes',
    ];

    public function scopeSearch($query, $search)
    {
        if (!$search)
            return $query;

        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
                ->orWhere('father_name', 'like', "%{$search}%")
                ->orWhere('nickname', 'like', "%{$search}%")
                ->orWhere('phone_number', 'like', "%{$search}%")
                ->orWhere('tazkira', 'like', "%{$search}%")
                ->orWhere('main_province', 'like', "%{$search}%")
                ->orWhere('now_province', 'like', "%{$search}%");
        });
    }

    // 🔗 one office employee -> many resedints
    public function resedints()
    {
        return $this->hasMany(Resedint::class, 'officeemployee_id');
    }
    public function resources()
    {
        return $this->hasMany(\App\Models\Resource::class, 'officeemployee_id');

    }

    public function informations()
    {
        return $this->hasMany(\App\Models\Information::class);
    }

    public function events()
    {
        return $this->hasMany(Event::class, 'office_employee_id');
    }

    public function assignedEvents()
    {
        return $this->hasMany(Event::class, 'event_office_employee_id');
    }

}
