<?php

namespace App\Models\fifthyformatone;

use App\Models\Resource;
use App\Models\Traits\EnforceSingleFormatPerResource;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DamageRasanPlaces extends Model
{
    use HasFactory;
    use EnforceSingleFormatPerResource;

    protected $fillable = [
        'province',
        'zone',
        'district',
        'village',
        'specific_place',
        'specific_dot',
        'picture',
        'coordinates',
        'tahdid_type',
        'percentage',
        'damage_place',
        'Province_addr',
        'note',
        'resource_id',
    ];

    public function resource()
    {
        return $this->belongsTo(Resource::class , 'resource_id' , 'id');
    }
}
