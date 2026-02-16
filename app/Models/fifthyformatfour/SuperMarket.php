<?php

namespace App\Models\fifthyformatfour;

use Illuminate\Database\Eloquent\Model;

class SuperMarket extends Model
{
    protected $fillable = [
        'supermarket_name','market_activity_type','daily_clientele','shops_quantity',
        'responsible_name','responsible_f_name',
        'responsible_phone',
        'picture',
        'responsible_job',
        'province','zone','district','village','specefic_place','coordinates',
        'Province_addr',
        'employee_quantity','guard_quantity','resource_id','note',
    ];

    public function resource()
    {
        return $this->belongsTo(\App\Models\Resource::class);
    }
}
