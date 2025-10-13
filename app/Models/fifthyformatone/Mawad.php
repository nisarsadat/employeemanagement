<?php

namespace App\Models\fifthyformatone;

use App\Models\Resource;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mawad extends Model
{
    use HasFactory;

    protected $fillable = [
        'mawad_type',
        'nickname',
        'name',
        'father_name',

        'main_province',
        'main_district',
        'main_village',

        'now_province',
        'now_district',
        'now_village',

        'province',
        'zone',
        'district',
        'village',

        'specific_place',
        'coordinates',
        'mawad_sales_phone',

        'history_form',
        'supplier',
        'note',

        'resource_id',
    ];

    public function resource()
    {
        return $this->belongsTo(Resource::class , 'resource_id' , 'id');
    }
}
