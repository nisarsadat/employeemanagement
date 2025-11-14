<?php

namespace App\Models\fifthyFormats;

use App\Models\Resource;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Clubs extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'club_name','club_type','father_name','position','phone_number',
        'province','zone','district','specific_place','cordinate',
        'am_students','pm_students',
        'Province_addr',
        'resource_id',
    ];

    public function resource()
    {
        return $this->belongsTo(Resource::class , 'resource_id' , 'id');
    }
}
