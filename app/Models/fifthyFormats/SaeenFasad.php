<?php

namespace App\Models\fifthyFormats;

use App\Models\Resource;
use App\Models\Traits\EnforceSingleFormatPerResource;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SaeenFasad extends Model
{
    use HasFactory;
    use EnforceSingleFormatPerResource;
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
        'Province_addr',
        'coordinates',
        'responsiability',
        'activity_place',
        'friends_number',
        'phone_number',
        'band_distince',
        'band_place',
        'old_job',
        'now_job',
        'resource_code',
        'resource_phone_number',
        'note',
        'tafseel',
        'resource_id',
    ];

    public function resource()
    {
        return $this->belongsTo(Resource::class);
    }
    public function documents()
    {
        return $this->hasMany(SaeenFasadDocument::class);
    }
}
