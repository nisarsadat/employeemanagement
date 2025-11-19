<?php

namespace App\Models\fifthyformatone;

use App\Models\Resource;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LostMujremeen extends Model
{
    use HasFactory;

    protected $fillable = [
        'picture',
        'nickname',
        'name',
        'father_name',

        'main_province',
        'main_district',
        'main_village',

        'now_province',
        'Province_addr',
        'now_district',
        'now_village',
        'now_specific_place',
        'now_street_number',
        'now_home_number',

        'province',
        'zone',
        'district',

        'old_job',

        'jorm',
        'history_jorm',
        'lost_reason',
        'phone_number',

        'note',
        'resource_id',
        'coordinates',
    ];

    public function resource()
    {
        return $this->belongsTo(Resource::class  , 'resource_id' , 'id');
    }
}
