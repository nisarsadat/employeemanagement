<?php

namespace App\Models\fifthyFormats;

use App\Models\Information;
use App\Models\Resource;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Jawasis extends Model
{
    use HasFactory;



    protected $fillable = [
        'information_id',

        'picture',
        'name',
        'nick_name',
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

        'coordinates',
        'country',

        'responsiability',
        'done_activities',
        'activity_place',

        'count_of_communication',
        'phone_number',

        'old_job',
        'now_job',

        'resource_id',
        'notes',
        'tafseel',
    ];

    public function resource()
    {
        return $this->belongsTo(Resource::class);
    }

    public function documents()
    {
        return $this->hasMany(JawasisDocument::class);
    }
            public function information()
    {
        return $this->belongsTo(Information::class, 'information_id');
    }
}
