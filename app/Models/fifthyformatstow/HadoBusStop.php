<?php

namespace App\Models\fifthyformatstow;

use App\Models\Resource;
use App\Models\Traits\EnforceSingleFormatPerResource;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HadoBusStop extends Model
{
    use HasFactory;
    use EnforceSingleFormatPerResource;

    protected $fillable = [
        'name',
        'hada_type',

        'responsible_name',
        'responsible_f_name',
        'responsible_position',
        'Province_addr',
        'responsible_phone',

        'province',
        'picture',
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
