<?php

namespace App\Models\fifthyformatone;

use App\Models\Resource;
use App\Models\Traits\EnforceSingleFormatPerResource;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DamagePlaces extends Model
{
    use HasFactory;
    use EnforceSingleFormatPerResource;

    protected $fillable = [
        'province',
        'zone',
        'picture',
        'district',
        'village',
        'specific_place',
        'specific_dot',
        'coordinates',
        'tahdid_type',
        'percentage',
        'tahdid_place',
        'Province_addr',
        'amnyat_khala',
        'cameesa_tafseel',
        'guard_quantity',
        'masool_name',
        'masool_f_name',
        'masool_position',
        'masool_phone',
        'note',
        'resource_id',
    ];

    public function resource()
    {
        return $this->belongsTo(Resource::class , 'resource_id' , 'id');
    }
}
