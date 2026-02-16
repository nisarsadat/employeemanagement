<?php

namespace App\Models\fifthyformatfour;

use App\Models\Traits\EnforceSingleFormatPerResource;
use Illuminate\Database\Eloquent\Model;

class Pump extends Model
{
    use EnforceSingleFormatPerResource;

    protected $fillable = [
        'pump_name','pump_type','main_owner',
        'responsible_name','responsible_f_name','responsible_possition','responsible_phone',
        'province','zone','district','village','specefic_place',
        'picture',
        'coordinates','employee_quantity','guard_quantity',
        'Province_addr',
        'resource_id','note',
    ];

    public function resource()
    {
        return $this->belongsTo(\App\Models\Resource::class);
    }
}
