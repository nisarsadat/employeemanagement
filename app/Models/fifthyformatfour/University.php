<?php

namespace App\Models\fifthyformatfour;

use Illuminate\Database\Eloquent\Model;

class University extends Model
{
    protected $fillable = [
        'uni_name','uni_type',
        'responsible_name','responsible_f_name','responsible_position','responsible_phone',
        'male_teachers_quantity','female_teachers_quantity',
        'male_students_quantity','female_students_quantity',
        'province','zone','district','village','specific_place','street_number','home_number',
        'coordinates',
        'resource_id',
        'note',
    ];

    public function resource()
    {
        return $this->belongsTo(\App\Models\Resource::class);
    }
}
