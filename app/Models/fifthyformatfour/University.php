<?php

namespace App\Models\fifthyformatfour;

use App\Models\Traits\EnforceSingleFormatPerResource;
use Illuminate\Database\Eloquent\Model;

class University extends Model
{
    use EnforceSingleFormatPerResource;

    protected $fillable = [
        'uni_name','uni_type',
        'responsible_name','responsible_f_name','responsible_position','responsible_phone',
        'male_teachers_quantity','female_teachers_quantity',
        'picture',
        'male_students_quantity','female_students_quantity',
        'province','zone','district','village','specific_place','street_number','home_number',
        'Province_addr',
        'coordinates',
        'resource_id',
        'note',
    ];

    public function resource()
    {
        return $this->belongsTo(\App\Models\Resource::class);
    }
}
