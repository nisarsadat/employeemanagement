<?php

namespace App\Models\fifthyFormats;

use App\Models\Resource;
use App\Models\Traits\EnforceSingleFormatPerResource;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mahajerat extends Model
{
    use HasFactory;

    use EnforceSingleFormatPerResource;

    protected $fillable = [
        'information_id',

        'name',
        'father_name','scientific_level',
        'main_province','main_district','main_village',
        'picture',
        'now_province','now_zone','now_district','now_vinage',
        'specific_place','street_number','home_number','cordinates',
        'country','male_amount','female_amount','phone_number',
        'Province_addr',
        'note','resource_id',
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
