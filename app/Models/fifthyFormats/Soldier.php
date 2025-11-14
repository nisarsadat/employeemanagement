<?php

namespace App\Models\fifthyFormats;

use App\Models\Resource;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Soldier extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'father_name',
        'main_province','main_district','main_village',
        'now_province','now_zone','now_district','now_village',
        'specific_place','street_number','home_number',
        'Province_addr',
        'old_job','now_job','guard_amount','phone_number',
        'notes','resource_id',
    ];

    public function resource()
    {
        return $this->belongsTo(Resource::class , 'resource_id' , 'id');
    }
}
