<?php

namespace App\Models\fifthyformatfour;

use App\Models\Traits\EnforceSingleFormatPerResource;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use EnforceSingleFormatPerResource;

    protected $fillable = [
        'com_name','com_type','product_type','com_level','main_owner',
        'responsible_name','responsible_f_name','responsible_position','responsible_phone',
        'com_province','com_zone','com_district','com_village','com_specific_place','com_coordinates',
        'male_employee_quantity_inner','female_employee_quantity_inner',
        'male_employee_quantity_out','female_employee_quantity_out','guard_quantity',
        'resource_id','note',
        'Province_addr',
        'picture',

    ];

    public function resource()
    {
        return $this->belongsTo(\App\Models\Resource::class);
    }
}
