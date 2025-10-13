<?php

namespace App\Models\fifthyformatone;

use App\Models\Resource;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MashkokShops extends Model
{
    use HasFactory;

    protected $fillable = [
        'province',
        'zone',
        'district',
        'village',
        'specific_place',
        'market_name',
        'coordinates',

        'name',
        'father_name',
        'main_province',
        'phone_numbers',

        'shop_type',
        'mashkok_type',

        'note',
        'resource_id',
    ];

    public function resource()
    {
        return $this->belongsTo(Resource::class , 'resource_id' , 'id');
    }
}
