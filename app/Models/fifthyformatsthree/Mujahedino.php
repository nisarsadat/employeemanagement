<?php

namespace App\Models\fifthyformatsthree;

use App\Models\Resource;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mujahedino extends Model
{
    use HasFactory;

    protected $fillable = [
        'nickname',
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

        'duty_type',
        'now_job',
        'organ',
        'phone_number',
        'note',
        'resource_id',
    ];

    public function resource()
    {
        return $this->belongsTo(Resource::class , 'resource_id' , 'id');
    }
}
