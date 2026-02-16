<?php

namespace App\Models\fifthyformatfour;

use Illuminate\Database\Eloquent\Model;

class Checkpoint extends Model
{
    protected $fillable = [
        'picture',
        'check_point_place',
        'checkpoint_name','employee_quantity',
        'organ','create_reason',
        'officer_name','officer_f_name','officer_phone',
        'gun_type',
        'province','zone','district','village','specefic_place',
        'coordinates',
        'resource_id','note',
        'Province_addr'
    ];

    public function resource()
    {
        return $this->belongsTo(\App\Models\Resource::class);
    }
}
