<?php

namespace App\Models\fifthyformatfour;

use App\Models\Traits\EnforceSingleFormatPerResource;
use Illuminate\Database\Eloquent\Model;

class CriminalCapturePlace extends Model
{
    use EnforceSingleFormatPerResource;

    protected $fillable = [
        'name',
        'father_name',
        'job',
        'jorm_type',
        'criminals_quantity',
        'office',
        'province',
        'zone',
        'district',
        'village',
        'specefic_place',
        'picture',
        'coordinates',
        'Province_addr',
        'resource_id',
        'event_id',
        'note',
    ];

    public function resource()
    {
        return $this->belongsTo(\App\Models\Resource::class);
    }

    public function event()
    {
        return $this->belongsTo(\App\Models\Event::class);
    }
}