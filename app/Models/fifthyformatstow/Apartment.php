<?php

namespace App\Models\fifthyformatstow;

use App\Models\Resource;
use App\Models\Traits\EnforceSingleFormatPerResource;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Apartment extends Model
{
    use HasFactory;
    use EnforceSingleFormatPerResource;

    protected $fillable = [
        'block_name',
        'apartments_quantity',
        'shahrak_name',
        'picture',

        'responsible_name',
        'responsible_f_name',
        'responsible_position',
        'responsible_phone',

        'province',
        'zone',
        'Province_addr',
        'district',
        'village',
        'specific_place',
        'coordinates',

        'guard_quantity',
        'note',
        'resource_id',
    ];

    public function resource()
    {
        return $this->belongsTo(Resource::class , 'resource_id' , 'id');
    }
}
