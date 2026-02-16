<?php

namespace App\Models\fifthyformatfour;

use Illuminate\Database\Eloquent\Model;

class CriminalCapturePlace extends Model
{
    protected $fillable = [
        'name','father_name','job',
        'jorm_type','criminals_quantity','office',
        'province','zone','district','village','specefic_place',
        'picture',
        'coordinates',
        'Province_addr',
        'resource_id','note',
    ];

    public function resource()
    {
        return $this->belongsTo(\App\Models\Resource::class);
    }
}
