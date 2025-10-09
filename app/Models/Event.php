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
        'who_did',
        'event_date',
        'event_hour',
        'zone',
        'hawza',
        'specific_place',
        'jani_talafat',
        'mali_talafat',
        'event_office_employee_code',
        'event_zone_employee_code',
        'tafseel',
        'talks',
        'notes',
    ];

    // 🔗 Each event belongs to one Resource
    public function resource()
    {
        return $this->belongsTo(Resource::class);
    }

    // 🔗 The employee who reported the event (main office employee)
    public function officeEmployee()
    {
        return $this->belongsTo(OfficeEmployee::class, 'office_employee_id');
    }

    // 🔗 The office employee assigned to the event
    public function eventOfficeEmployee()
    {
        return $this->belongsTo(OfficeEmployee::class, 'event_office_employee_code');
    }

    // 🔗 The zone employee assigned to the event
    public function eventZoneEmployee()
    {
        return $this->belongsTo(ZoneEmployee::class, 'event_zone_employee_code');
    }
    public function zoneEmployee()
    {
        return $this->belongsTo(ZoneEmployee::class, 'zone_employee_id');
    }

    // 📁 Documents uploaded for this event
    public function documents()
    {
        return $this->hasMany(EventDocument::class);
    }
}
