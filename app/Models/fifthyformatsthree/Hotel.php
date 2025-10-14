<?php

namespace App\Models\fifthyformatsthree;

use App\Models\Resource;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Hotel extends Model
{
    use HasFactory;

    protected $fillable = [
        'hotel_name',
        'type',

        'responsible_name',
        'responsible_f_name',
        'responsible_position',
        'responsible_phone',

        'province',
        'zone',
        'district',
        'village',
        'specific_place',
        'cordenate',

        'employee_quantity',
        'note',

        'resource_id',
    ];

    public function resource()
    {
        return $this->belongsTo(Resource::class , 'resource_id' , 'id');
    }
}
