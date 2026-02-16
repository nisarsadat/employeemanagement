<?php

namespace App\Models\fifthyFormats;

use App\Models\Resource;
use App\Models\Traits\EnforceSingleFormatPerResource;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Clubs extends Model
{
    use HasFactory;
    use EnforceSingleFormatPerResource;

    protected $fillable = [
        'name',
        'club_name','club_type','father_name','position','phone_number',
        'province','zone','district','specific_place','cordinate',
        'am_students','pm_students',
        'picture',
        'Province_addr',
        'notes',
        'resource_id',
    ];

    public function resource()
    {
        return $this->belongsTo(Resource::class , 'resource_id' , 'id');
    }
}
