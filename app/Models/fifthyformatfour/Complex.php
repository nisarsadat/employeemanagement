<?php

namespace App\Models\fifthyformatfour;

use Illuminate\Database\Eloquent\Model;

class Complex extends Model
{
    protected $fillable = [
        'province','zone','district','village','specefic_place','street_number','home_number',
        'complex_name','activity_type','activity_place','supplier',
        'enter_male_quantity','outer_male_quantity','enter_femal_quantity','outer_femal_quantity',
        'responsible_name',
        'responsible_job',
        'responsible_f_name','responsible_phone','responsible_country',
        'Province_addr',
        'coordinates',
        'guard_quantity','resource_id','note',
    ];

    public function resource()
    {
        return $this->belongsTo(\App\Models\Resource::class);
    }
}
