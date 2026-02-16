<?php

namespace App\Models\fifthyformatstow;

use App\Models\Resource;
use App\Models\Traits\EnforceSingleFormatPerResource;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bank extends Model
{
    use HasFactory;
    use EnforceSingleFormatPerResource;

    protected $fillable = [
        'bank_name',
        'bank_type',
        'bank_owner',

        'responsible_name',
        'responsible_f_name',
        'responsible_position',
        'responsible_phone',

        'which_offices_sallary',
        'province',
        'zone',
        'picture',
        'district',
        'village',
        'specific_place',
        'Province_addr',
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
