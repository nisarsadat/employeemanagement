<?php

namespace App\Models\fifthyformatfour;

use Illuminate\Database\Eloquent\Model;

class EconemyProject extends Model
{
    protected $fillable = [
        'coordinates',
        'name','type',
        'responsiable_name','responsiable_f_name','responsible_possition','responsible_phone',
        'province','zone','district','village','specefic_place',
        'amnyatee_khala','employee_quantity','gaurd_quantity',
        'Province_addr',
        'resource_id','note',
    ];

    public function resource()
    {
        return $this->belongsTo(\App\Models\Resource::class);
    }
}
