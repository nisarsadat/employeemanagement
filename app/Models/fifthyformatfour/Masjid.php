<?php

namespace App\Models\fifthyformatfour;

use Illuminate\Database\Eloquent\Model;

class Masjid extends Model
{
    protected $fillable = [
        
        'masjid_name','masjid_type',
        'responsible_name','responsible_f_name','responsible_position',
        'responsible_mazhab','responsible_old_job','responsible_now_job','responsible_tarfdary',
        'responsible_main_province','responsible_main_district','responsible_main_village',
        'responsible_now_province','responsible_now_district','responsible_now_village',
        'masjid_people_mazhab','masjid_homes_quantity',
        'masjid_province','masjid_zone','masjid_district','masjid_village',
        'masjid_specific_place','masjid_street_number','masjid_coordinates',
        'resource_id','note',
    ];

    public function resource()
    {
        return $this->belongsTo(\App\Models\Resource::class);
    }
}
