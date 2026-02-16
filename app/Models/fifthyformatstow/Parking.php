<?php

namespace App\Models\fifthyformatstow;

use App\Models\Resource;
use App\Models\Traits\EnforceSingleFormatPerResource;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Parking extends Model
{
    use HasFactory;
    use EnforceSingleFormatPerResource;

    protected $fillable = [
        'parking_name',
        'parking_type',
        'parking_type_pe',

        'responsible_name',
        'responsible_f_name',
        'responsible_position',
        'responsible_phone',

        'province',
        'picture',
        'zone',
        'district',
        'Province_addr',
        'village',
        'specific_place',
        'coordinates',

        'daily_cars_parking_quantity',
        'note',
        'resource_id',
    ];

    public function resource()
    {
        return $this->belongsTo(Resource::class , 'resource_id' , 'id');
    }
}
