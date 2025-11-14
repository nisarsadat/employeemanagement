<?php

namespace App\Models\fifthyformatsthree;

use App\Models\Resource;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Madrasa extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'madrasa_type',

        'responsible_name',
        'responsible_f_name',
        'responsible_position',
        'responsible_phone',
        'responsible_main_province',
        'tarafdari_type_responsible',

        'mazhab',
        'male_teacher_quantity',
        'female_teacher_quantity',
        'male_students_quantity',
        'Province_addr',
        'female_students_quantity',

        'province',
        'zone',
        'district',
        'village',
        'specific_place',
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
