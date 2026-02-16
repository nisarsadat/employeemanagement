<?php

namespace App\Models\fifthyformatstow;

use App\Models\Resource;
use App\Models\Traits\EnforceSingleFormatPerResource;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Hosppital extends Model
{
    use HasFactory;
    use EnforceSingleFormatPerResource;

    protected $fillable = [
        'name',
        'specific_past',   // as provided
        'hospita_type',    // as provided

        'responsible_name',
        'responsible_position',
        'responsible_phone',
        'responsible_f_name',

        'male_quantity',
        'picture',
        'female_quantity',
        'guard_quantity',

        'Province_addr',
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
