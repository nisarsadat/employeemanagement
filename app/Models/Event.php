<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'resource_id',
        'office_employee_id',
        'event_type',
        'event_specific_type',
        'target_person',
        'criminal',
        'event_date',
        'event_hour',
        'event_zone',
        'event_hawza',
        'event_specific_place',
        'talafat_jani',
        'talafat_mali',
        'tafseel',
        'talks',
        'coordinates',
        'note',
    ];

    // 🔗 Relations (UNCHANGED)
    public function resource()
    {
        return $this->belongsTo(Resource::class);
    }

    public function officeEmployee()
    {
        return $this->belongsTo(OfficeEmployee::class, 'office_employee_id');
    }
    public function documents()
    {
        return $this->hasMany(EventDocument::class);
    }
}
