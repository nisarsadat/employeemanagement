<?php

namespace App\Models\fifthyformatfour;

use Illuminate\Database\Eloquent\Model;

class Donelostplace extends Model
{
    protected $fillable = [
        'place_name',
        'lost_person_name','lost_person_f_name','lost_person_phone','lost_person_job',
        'main_province','main_district','main_village',
        'now_province','now_district','now_village',
        'picture',
        'province','zone','district','village','specific_place','coordinates',
        'lost_quantity',
        'Province_addr',
        'resource_id','note',
    ];

    public function resource()
    {
        return $this->belongsTo(\App\Models\Resource::class);
    }
}
