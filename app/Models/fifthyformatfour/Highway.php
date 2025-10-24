<?php

namespace App\Models\fifthyformatfour;

use Illuminate\Database\Eloquent\Model;

class Highway extends Model
{
    protected $fillable = [
        'high_way_name',
        'province',
        'zone',
        'district',
        'village',
        'specific_place',
        'coordinates',
        'resource_id',
        'note',
    ];

    public function resource()
    {
        return $this->belongsTo(\App\Models\Resource::class);
    }
}
