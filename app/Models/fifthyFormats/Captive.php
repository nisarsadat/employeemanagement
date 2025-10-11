<?php

namespace App\Models\fifthyFormats;

use App\Models\Resource;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Captive extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'picture',
        'nick_name',
        'father_name',
        'main_province',
        'main_district',
        'main_village',
        'now_province',
        'now_zone',
        'now_district',
        'now_village',
        'specific_place',
        'street_number',
        'home_number',
        'specific_form',
        'band_distince',
        'now_activity',
        'phone_number',
        'note',
        'resource_id',
    ];

    public function resource()
    {
        return $this->belongsTo(Resource::class, 'resource_id', 'id');
    }

}
