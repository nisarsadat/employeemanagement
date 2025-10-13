<?php

namespace App\Models\fifthyformatone;

use App\Models\Resource;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OldSoldiersHomes extends Model
{
    use HasFactory;

    protected $fillable = [
        'province',
        'zone',
        'district',
        'village',
        'specific_place',
        'street_number',
        'home_number',
        'coordinates',
        'name',
        'father_name',
        'main_province',
        'old_job',
        'now_activity',
        'note',
        'resource_id',
    ];

    public function resource()
    {
        return $this->belongsTo(Resource::class , 'resource_id' , 'id');
    } 
}
