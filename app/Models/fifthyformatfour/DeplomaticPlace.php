<?php

namespace App\Models\fifthyformatfour;

use App\Models\Traits\EnforceSingleFormatPerResource;
use Illuminate\Database\Eloquent\Model;

class DeplomaticPlace extends Model
{
    use EnforceSingleFormatPerResource;

    protected $fillable = [
        'deplomatic_place_name','activity_type','activity_place',
        'enter_male_quantity','outer_male_quantity','enter_female_quantity','outer_female_quantity',
        'responsible_name','responsible_f_name','responsible_possition','responsible_country','responsible_phone',
        'province','zone','district','village','specefic_place','street_number','home_number',
        'supplier_country',
        'picture',
        'Province_addr',
        'guard_quantity','coordinates','resource_id','note',
    ];

    public function resource()
    {
        return $this->belongsTo(\App\Models\Resource::class);
    }
}
