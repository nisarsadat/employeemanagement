<?php

namespace App\Models\fifthyformatstow;

use App\Models\Resource;
use App\Models\Traits\EnforceSingleFormatPerResource;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RahnomaMamlat extends Model
{
    use HasFactory;
    use EnforceSingleFormatPerResource;

    protected $fillable = [
        'name',

        'responsible_name',
        'responsible_f_name',
        'responsible_position',
        'responsible_phone',

        'province',
        'zone',
        'Province_addr',
        'district',
        'picture',
        'village',
        'specific_place',
        'coordinates',

        'note',
        'resource_id',
    ];

    public function resource()
    {
        return $this->belongsTo(Resource::class , 'resource_id' , 'id');
    }
}
