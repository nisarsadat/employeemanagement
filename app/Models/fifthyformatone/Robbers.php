<?php

namespace App\Models\fifthyformatone;

use App\Models\Resource;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Robbers extends Model
{
    use HasFactory;

    protected $fillable = [
        'information_id',

        'nickname',
        'name',
        'father_name',

        'main_province',
        'main_district',
        'main_village',

        'now_province',
        'picture',
        'now_zone',
        'now_district',
        'now_village',
        'now_specific_place',
        'now_street_number',
        'Province_addr',
        'now_home_number',

        'old_job',
        'now_job',
        'supplier',
        'phone_number',
        'freinds_quantity',
        'history_form',
        'coordinates',

        'note',
        'resource_id',
    ];

    public function resource()
    {
        return $this->belongsTo(Resource::class , 'resource_id' , 'id');
    }
    public function information()
    {
        return $this->belongsTo(\App\Models\Information::class, 'information_id');
    }
}
