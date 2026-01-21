<?php

namespace App\Models\fifthyformatone;

use App\Models\Resource;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MashkokHomes extends Model
{
    use HasFactory;

    protected $fillable = [
        'picture',
        'province',
        'zone',
        'district',
        'village',
        'specific_place',
        'street_number',
        'home_number',
        'coordinates',
        'home_type',
        'Province_addr',
        'shak_symbols',
        'note',
        'resource_id',
    ];

    public function resource()
    {
        return $this->belongsTo(Resource::class , 'resource_id' , 'id');
    }
   public function documents()
    {
        return $this->hasMany(MashkokHomesDocument::class, 'mashkok_home_id');
    }


}
