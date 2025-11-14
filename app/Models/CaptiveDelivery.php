<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CaptiveDelivery extends Model
{
    protected $table = 'captive_deliveries';

    protected $fillable = [
        'picture',
        'name',
        'father_name',
        'nickname',
        'grand_father_name',
        'main_province',
        'main_district',
        'main_village',
        'now_province',
        'now_district',
        'now_village',
        'halat_madani',
        'age',
        'tazkira',
        'criminal_history',
        'Province_addr',
        'old_job',
        'new_job',
        'language',
        'scientific_level',
        'captive_place',
        'captive_marja',
        'captive_date',
        'captive_hour',
        'suspect_type',
        'phone_number',
        'captive_halat',
        'suspect_tools',
        'notes',
    ];

    public function documents(): HasMany
    {
        return $this->hasMany(CaptiveDeliveryDocument::class, 'captive_delivery_id');
    }
}
