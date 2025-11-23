<?php

namespace App\Models\fifthyformatfour;

use App\Models\Resource;
use Illuminate\Database\Eloquent\Model;

class GovernmentPlace extends Model
{
    protected $fillable = [
        'government_place_name','daily_clientele',
        'responsible_name','responsible_f_name','responsible_possition','responsible_phone',
        'province','zone','district','village','specefic_place','coordinates',
        'tafseel','amnyatee_khala','guard_quantity',
        'Province_addr',
        'resource_id','note',
    ];

    public function resource()
    {
        return $this->belongsTo(Resource::class , 'resource_id' , 'id');
    }
}
