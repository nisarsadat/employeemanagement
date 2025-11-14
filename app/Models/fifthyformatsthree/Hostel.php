<?php

namespace App\Models\fifthyformatsthree;

use App\Models\Resource;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Hostel extends Model
{
    use HasFactory;

    protected $fillable = [
        'hostel_name',
        'hostel_type',
        'people_quantity',
        'people_type',

        'responsible_name',
        'Province_addr',
        'responsible_f_name',
        'responsible_position',
        'responsible_phone',

        'province',
        'zone',
        'district',
        'village',
        'specific_place',
        'coordenates',

        'note',
        'resource_id',
    ];

    public function resource()
    {
        return $this->belongsTo(Resource::class , 'resource_id' , 'id');
    }
}
