<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CaptiveDelivery extends Model
{
    use HasFactory;

    protected $table = 'captive_deliveries';

    protected $fillable = [
        'name',
        'father_name',
        'grand_father_name',
        'nickname',
        'tazkira',
        'age',
        'halat_madani',
        'criminal_history',
        'old_job',
        'new_job',
        'language',
        'picture',
        'scientific_level',
        'main_province',
        'main_district',
        'main_village',
        'now_province',
        'now_district',
        'now_village',
        'captive_place',
        'coordinates',
        'captive_marja',
        'captive_date',
        'captive_hour',
        'suspect_type',
        'phone_number',
        'captive_halat',
        'suspect_tools',
        'notes',
        'captive_reason',
        'taslimi_warkawonky_person',
        'taslimi_akhistonky_person',
        'taslimi_akhistonky_organ',
        'maznonyat_type',
        'Province_addr',
        'picture',
    ];

    public function documents()
    {
        // ✅ THIS is the important part
        return $this->hasMany(CaptiveDeliveryDocument::class, 'captive_delivery_id', 'id');
    }
}
