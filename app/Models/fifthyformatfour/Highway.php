<?php

namespace App\Models\fifthyformatfour;

use App\Models\Traits\EnforceSingleFormatPerResource;
use Illuminate\Database\Eloquent\Model;

class Highway extends Model
{
    use EnforceSingleFormatPerResource;

    protected $fillable = [
        'high_way_name',
        'province',
        'zone',
        'district',
        'village',
        'specific_place',
        'picture',
        'coordinates',
        'Province_addr',
        'resource_id',
        'note',
    ];

    public function resource()
    {
        return $this->belongsTo(\App\Models\Resource::class);
    }
}
