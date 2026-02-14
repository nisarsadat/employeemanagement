<?php

namespace App\Models\fifthyformatsthree;

use App\Models\Resource;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MashkokPeople extends Model
{
    use HasFactory;

    protected $fillable = [
        'picture',
        'name',
        'father_name',

        'main_province',
        'main_district',
        'main_village',

        'now_province',
        'now_zone',
        'Province_addr',
        'now_districts',     // kept as written in your note
        'now_village',
        'now_specific_place',
        'now_street_number',
        'now_home_number',
        'coordinates',

        'old_job',
        'now_job',
        'phone_number',
        'mashkokyat_type',
        'mushkokyat_points', // kept spelling as requested

        'note',
        'tafseel',
        'resource_id',
    ];

    public function resource()
    {
        return $this->belongsTo(Resource::class , 'resource_id' , 'id');
    }
}
