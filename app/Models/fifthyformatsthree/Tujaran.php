<?php

namespace App\Models\fifthyformatsthree;

use App\Models\Resource;
use App\Models\Traits\EnforceSingleFormatPerResource;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tujaran extends Model
{
    use HasFactory;
    use EnforceSingleFormatPerResource;


    protected $fillable = [
        'name',
        'father_name',
        'picture',

        // Main address
        'main_province',
        'main_district',
        'main_village',

        // Current address
        'now_province',
        'now_zone',
        'now_district',
        'now_village',
        'now_specific_place',
        'now_street_number',
        'Province_addr',
        'now_home_number',

        // Work & org
        'old_job',
        'now_job',
        'organ',

        // Other
        'guard_guntity',   // kept spelling as provided in the note
        'phone_number',
        'tahdid_mutawaji',
        'people_type',

        'note',
        'coordinates',
        'resource_id',
    ];

    public function resource()
    {
        return $this->belongsTo(Resource::class , 'resource_id' , 'id');
    }
}
