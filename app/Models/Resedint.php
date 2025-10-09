<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Resedint extends Model
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
        'code_number',

        'phone_number',
        'whatsapp_number',
        'hawza',
        'zone',
        'code_number',

        'officeemployee_id', // FK to office_employees.id
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
                ->orWhere('whatsapp_number', 'like', "%{$search}%")
                ->orWhere('zone', 'like', "%{$search}%")
                ->orWhere('code_number', 'like', "%{$search}%");
        });
    }

    // 🔗 many resedints -> one office employee
    public function officeEmployee()
    {
        return $this->belongsTo(OfficeEmployee::class, 'officeemployee_id');
    }

    // (optional) one resedint -> many zone employees
    public function zoneEmployees()
    {
        return $this->hasMany(ZoneEmployee::class, 'resedint_id');
    }
    public function resources()
    {
        return $this->hasMany(\App\Models\Resource::class, 'officeemployee_id');
    }

}
