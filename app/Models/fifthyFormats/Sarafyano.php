<?php

namespace App\Models\fifthyFormats;

use App\Models\Resource;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sarafyano extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'sarafi_name','sarafi_type','father_name','position','phone_number',
        'province','zone','district','village','specific_place',
        'market_name','shop_number','cordinate',
        'provinces_hawala','countries_hawala','employee_quantity',
        'notes','resource_id',
    ];

    public function resource()
    {
        return $this->belongsTo(Resource::class , 'resource_id' , 'id');
    }
}
