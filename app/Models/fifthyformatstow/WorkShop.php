<?php

namespace App\Models\fifthyformatstow;

use App\Models\Resource;
use App\Models\Traits\EnforceSingleFormatPerResource;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkShop extends Model
{
    use HasFactory;
    use EnforceSingleFormatPerResource;

    protected $fillable = [
        'workshop_name',
        'workshop_type',

        'responsible_name',
        'responsible_f_name',
        'responsible_position',
        'responsible_phone',

        'province',
        'Province_addr',
        'zone',
        'picture',
        'district',
        'village',
        'specific_place',
        'coordinates',

        'workers_quantity',
        'note',
        'resource_id',
    ];

    public function resource()
    {
        return $this->belongsTo(Resource::class , 'resource_id' , 'id');
    }
}
