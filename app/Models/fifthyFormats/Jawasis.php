<?php

namespace App\Models\fifthyFormats;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Jawasis extends Model
{
    use HasFactory;

    // ✅ IMPORTANT: Laravel was guessing "jawases"
    // We force the correct table name:
    protected $table = 'jawasis';

    protected $fillable = [
        'picture',
        'name',
        'nick_name',
        'father_name',

        'main_province',
        'main_district',
        'main_village',

        'now_province',
        'now_zone',
        'now_district',
        'now_village',
        'now_specific_place',
        'now_street_number',
        'now_home_number',

        'coordinates',
        'country',

        'responsiability',
        'done_activities',
        'activity_place',

        'count_of_communication',
        'phone_number',

        'old_job',
        'now_job',

        'resource_id',
        'notes',
    ];

    public function resource()
    {
        return $this->belongsTo(\App\Models\Resource::class);
    }

    public function documents()
    {
        return $this->hasMany(JawasisDocument::class);
    }
}
