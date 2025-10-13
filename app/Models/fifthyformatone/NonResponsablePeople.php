<?php

namespace App\Models\fifthyformatone;

use App\Models\Resource;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NonResponsablePeople extends Model
{
    use HasFactory;

    protected $fillable = [
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
        'friends_quantity',
        'supplier',
        'phone_number',
        'history_jorm',
        'activity_place',
        'old_job',
        'now_job',
        'gun_type',
        'tanzim',
        'takhalof_type',
        'note',
        'resource_id',
    ];

    public function resource()
    {
        return $this->belongsTo(Resource::class , 'resource_id' , 'id');
    }
}
