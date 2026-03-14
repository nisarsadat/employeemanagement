<?php

namespace App\Models\fifthyformatsthree;

use App\Models\Resource;
use App\Models\Traits\EnforceSingleFormatPerResource;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmaratOffice extends Model
{
    use HasFactory;
    use EnforceSingleFormatPerResource;

    protected $fillable = [
        'type',
        're_name',
        'province',
        'zone',
        'district',
        'village',
        'specific_place',
        'street_number',
        'home_number',
        'coordenates',      // kept spelling as written
        'home_type',
        'Province_addr',
        'responsible_office',
        'job',
        'tahdid_mutawajeh', // kept as provided
        'guard_quantity',
        'note',
        'resource_id',
    ];

    public function resource()
    {
        return $this->belongsTo(Resource::class , 'resource_id' , 'id');
    }
}
