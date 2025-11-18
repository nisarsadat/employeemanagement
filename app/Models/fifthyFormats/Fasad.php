<?php

namespace App\Models\fifthyFormats;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Fasad extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'picture',
        'nick_name',
        'father_name',
        'main_province',
        'main_district',
        'main_village',
        'now_province',
        'now_zone',
        'Province_addr',
        'now_district',
        'now_village',
        'specific_place',
        'street_number',
        'home_number',
        'jabha',
        'supplier_name',
        'responsibility',
        'activity_zone',
        'friends_number',
        'phone_number',
        'bond_distance',
        'last_band_reason',
        'band_place',
        'now_job',
        'resource_code',
        'resource_phone',
        'resource_id',
        'coordinates',
    ];

    /** Fasad belongs to one Resource (existing table/model). */
    public function resource()
    {
        return $this->belongsTo(\App\Models\Resource::class);
    }
}
