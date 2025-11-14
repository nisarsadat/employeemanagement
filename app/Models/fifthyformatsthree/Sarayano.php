<?php

namespace App\Models\fifthyformatsthree;

use App\Models\Resource;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sarayano extends Model
{
    use HasFactory;

    protected $fillable = [
        'saray_name',
        'saray_type',

        'responsible_name',
        'responsible_f_name',
        'responsible_position',
        'responsible_phone',

        'province',
        'zone',
        'district',
        'village',
        'Province_addr',
        'specific_place',
        'cordenates',

        'employee_quantity',
        'note',

        'resource_id',
    ];

    public function resource()
    {
        return $this->belongsTo(Resource::class , 'resource_id' , 'id');
    }
}
