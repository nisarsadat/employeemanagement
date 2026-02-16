<?php

namespace App\Models\fifthyformatstow;

use App\Models\Resource;
use App\Models\Traits\EnforceSingleFormatPerResource;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class School extends Model
{
    use HasFactory;
    use EnforceSingleFormatPerResource;

    protected $fillable = [
        'school_name',
        'school_type',

        'principle_name',
        'principle_f_name',
        'principle_phone',

        'male_teachers_quantity',
        'female_teachers_quantity',
        'male_students_quantity',
        'female_students_quantity',
        'picture',

        'province',
        'Province_addr',
        'zone',
        'district',
        'specific_place',
        'village',
        'street_number',
        'coordinates',

        'note',
        'resource_id',
    ];

    public function resource()
    {
        return $this->belongsTo(Resource::class , 'resource_id' , 'id');
    }
}
