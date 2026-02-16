<?php

namespace App\Models\fifthyFormats;

use App\Models\Resource;
use App\Models\Traits\EnforceSingleFormatPerResource;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mulkano extends Model
{
    use HasFactory;
    use EnforceSingleFormatPerResource;

    protected $fillable = [
        'name','picture','father_name','grand_father_name',
        'main_province','main_district','main_village',
        'now_province','now_zone','now_district','now_village',
        'specific_place','street_number','home_number','guzar',
        'tafseel_sahi','street_amount','old_job','new_job',
        'agent_quantity','home_quantity','phone_number',
        'Province_addr',
        'coordinates',
        'hawza',
        'nahya',
        'street',
        'jorm_history','tanzim','notes','resource_id',
    ];

    public function resource()
    {
        return $this->belongsTo(Resource::class , 'resource_id' , 'id');
    }
}
