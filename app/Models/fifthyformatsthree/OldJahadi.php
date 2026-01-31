<?php

namespace App\Models\fifthyformatsthree;

use App\Models\Resource;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OldJahadi extends Model
{
    use HasFactory;

    protected $fillable = [
        'picture',
        'nick_name',
        'name',
        'father_name',

        'main_province',
        'main_district',
        'main_village',

        'now_province',
        'now_zone',
        'now_district',
        'now_village',
        'now_specific_place',
        'now_street_number',
        'now_home_number',

        'old_job',
        'Province_addr',
        'now_job',
        'employee_quantity',
        'supplier',
        'phone_number',
        'tanzim',
        'note',
        'organ',
        'coordinates',
        'resource_id',
    ];

    public function resource()
    {
        return $this->belongsTo(Resource::class , 'resource_id' , 'id');
    }
}
