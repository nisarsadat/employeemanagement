<?php

namespace App\Models\fifthyformatfour;

use App\Models\Resource;
use Illuminate\Database\Eloquent\Model;

class Wakilano extends Model
{
    protected $fillable = [
        'picture',
        'coordinates',
        'name',
        'f_name',
        'grand_father_name',
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
        'Guzar',
        'place_tafseel',
        'old_job',
        'now_job',
        'home_quantity',
        'phone_number',
        'history_form',
        'Province_addr',
        'resource_id',
        'note',
    ];

    public function resource()
    {
        return $this->belongsTo(Resource::class);
    }
}
