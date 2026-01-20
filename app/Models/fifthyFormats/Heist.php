<?php

namespace App\Models\fifthyFormats;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Heist extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'picture',
        'father_name',
        'main_province',
        'main_district',
        'main_village',
        'now_province',
        'now_zone',
        'now_district',
        'now_village',
        'specific_place',
        'Province_addr',
        'street_number',
        'home_number',
        'coordinates',
        'old_job',
        'now_job',
        'phone_number',
        'done_roberies',
        'gun_type',
        'note',
        'resource_id',
    ];

    public function resource()
    {
        return $this->belongsTo(\App\Models\Resource::class);
    }

    // ✅ Documents
    public function documents()
    {
        return $this->hasMany(HeistDocument::class);
    }
}
