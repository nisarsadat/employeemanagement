<?php

namespace App\Models\fifthyformatstow;

use App\Models\Resource;
use App\Models\Traits\EnforceSingleFormatPerResource;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CarSalesCompany extends Model
{
    use HasFactory;
    use EnforceSingleFormatPerResource;

    protected $fillable = [
        'car_sales_company_name',

        'responsible_name',
        'responsible_f_name',
        'responsible_position',
        'responsible_phone',
        'Province_addr',

        'province',
        'zone',
        'picture',
        'district',
        'village',
        'specific_place',
        'coordinates',

        'note',
        'resource_id',
    ];

    public function resource()
    {
        return $this->belongsTo(Resource::class , 'resource_id' , 'id');
    }
}
