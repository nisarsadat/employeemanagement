<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Meeting extends Model
{
    use HasFactory;

    protected $fillable = [
        'meeting_title',
        'organ',
        'province',
        'district',
        'specific_place',
        'coordinates',
        'start_date',
        'end_date',
        'total_time',
        'participant',
        'boss_participant',
        'office_employee_count',
        'leader_name',
        'done_actions',
        'mashkok_situations',
        'problems',
        'meeting_supplier',
        'meeting_supplier_phone',
        'meeting_amniyat_responsable',
        'meeting_amniyat_responsable_phone',
        'amniyat_mansobine_count',
        'note',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'office_employee_count' => 'integer',
        'amniyat_mansobine_count' => 'integer',
    ];

    public function documents()
    {
        return $this->hasMany(MeetingDocument::class);
    }
}