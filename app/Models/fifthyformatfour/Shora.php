<?php

namespace App\Models\fifthyformatfour;

use Illuminate\Database\Eloquent\Model;

class Shora extends Model
{
    protected $fillable = [
        'shora_village','note',
        'shora_name','shora_goal','people_quantity','week_meeting_quantity','regester','shora_meeting_place',
        'responsible_name','responsible_f_name','responsible_job','responsible_phone',
        'supplier_name','supplier_job','supplier_phone',
        'emarat_tarfdary',
        'shora_province','shora_zone','shora_district',
        'Province_addr',
        'resource_id',
    ];

    public function resource()
    {
        return $this->belongsTo(\App\Models\Resource::class);
    }
}
