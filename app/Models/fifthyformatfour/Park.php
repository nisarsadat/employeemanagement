<?php

namespace App\Models\fifthyformatfour;

use Illuminate\Database\Eloquent\Model;

class Park extends Model
{
    protected $fillable = [
        'park_name','park_type','dayly_clientele', 
        'district',
        'responsible_name','responsible_f_name','responsible_phone','responsible_possition',
        'province','zone','village','specefic_place','coordinates',
        'Province_addr',
        'picture',
        'employee_quantity',
        'guard_quantity',
        'resource_id','notes',
    ];

    public function resource()
    {
        return $this->belongsTo(\App\Models\Resource::class);
    }
}
